<?php

require_once './response.php';
require_once 'usuario.php';
require_once 'profesor.php';
require_once 'materia.php';
require_once 'asignacion.php';
require_once 'file.php';
require_once 'API.php';



$request_method=$_SERVER['REQUEST_METHOD'];
$path_info=$_SERVER['PATH_INFO']??'';	
$response = new Response();

//settear nombres de archivos y carpetas
File::SetFolder('imagenes/');  


switch ($path_info) {
    case '/usuario':                                            //1. (POST) usuario. Registrar a un cliente con email y clave y guardarlo en el archivo users.xxx.
        switch ($request_method) {
            case 'POST':
                $response= API::post_signin();
            break;
            case 'GET':
                $response->data ="405 method not allowed";
                $response->status='fail';
            break;
            default:
                $response->data ="405 method not allowed";
                $response->status='fail';   
        }
    break;
    case '/login':                                                  //2. (POST) login: Recibe email y clave y si son correctos devuelve un JWT, de lo contrario informar lo sucedido.
        switch ($request_method) {
            case 'POST':
                $response= API::post_login();
            break;
            case 'GET':
                $response->data ="405 method not allowed";
                $response->status='fail';
            break;
            default:
                $response->data ="405 method not allowed";
                $response->status='fail';   
        }
    break;
    case '/materia':
        switch ($request_method) {
            case 'POST':                 //3. (POST) materia: Recibe nombre, cuatrimestre y lo guarda en el archivo materias.xxx. Agregar un id único para cada materia.
                $response= API::isLoggedInWithCallback('self::post_addMateria','all');    
                                                                         
            break;   
            case 'GET':                                             //6. (GET) materia: Muestra un listado con todas las materias.
                $response= API::isLoggedInWithCallback('self::get_materias','all');
            break;
            default:
                $response->data ="405 method not allowed";
                $response->status='fail';           
        }
    break;
    case '/profesor':                                          /*4. (POST) profesor: Recibe nombre, legajo (validar que sea único) y foto y lo guarda en el archivo
                                                                    profesores.xxx, a la imagen la guarda en la carpeta imágenes.*/
                                                                
        switch ($request_method) {
            case 'POST':
                $response= API::isLoggedInWithCallback('self::post_addProfesor','all');
            break;
            case 'GET':                                     //7. (GET) profesor: Muestra un listado con todas las profesores.
                $response= API::isLoggedInWithCallback('self::get_profesores','all');
            break;
            default:
                $response->data ="405 method not allowed";
                $response->status='fail';   
        }
    break;
    case '/asignacion':                                          /*5. (POST) asignacion: Recibe legajo del profesor, id de la materia y turno (manana o noche) y lo guarda en el
                                                                    archivo materias-profesores. No se debe poder asignar el mismo legajo en el mismo turno y materia..*/
    
        switch ($request_method) {
            case 'POST':
            $response= API::isLoggedInWithCallback('self::post_addAsignacion','all');
            break;
            case 'GET':                                             //8. (GET) asignacion: Muestra un listado con todas las materias asignadas a cada profesor.
            $response= API::isLoggedInWithCallback('self::get_materiasPorProfesor','all');
            break;
            default:
            $response->data ="405 method not allowed";
            $response->status='fail';   
        }
    break;

    default:
        $response->data ="404 Not Found";
        $response->status='fail';
}

echo json_encode($response);