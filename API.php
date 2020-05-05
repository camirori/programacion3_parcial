<?php

require_once 'response.php';
require_once 'usuario.php';
require_once 'profesor.php';
require_once 'materia.php';
require_once 'asignacion.php';
require_once 'file.php';
require_once 'API.php';


class API{                  

    //usuario-JWT-------------------------------------------------------------------------------------------------------------------------------
    public static function post_signin(){                   
        $response = new Response();
        $email=$_POST['email']??'';
        $clave=$_POST['clave']??'';

        if($email=='' || $clave==''){
            $response->status='fail';
            $response->data='Faltan datos'; 
        }
        else{
            $usr = new Usuario($email, $clave);
            $result = $usr->signin();
            if($result){
                $response->status='success';   
                $response->data='Usuario creado';                        
            }else{
                $response->data='El usuario ya existe'; 
                $response->status='error';                          
            }
        }
        return $response;
    }

    public static function post_login(){
        $response = new Response();
        $email=$_POST['email']??'';
        $clave=$_POST['clave']??'';
        if($email=='' || $clave==''){
            $response->status='fail';
            $response->data='Faltan datos'; 
        }else{
            $result = Usuario::login($email, $clave);     //jwt  
            if($result){
                $response->status='success';   
                $response->data=$result;                 
            }else{
                $response->data="Credenciales incorrectas"; 
                $response->status='fail'; 
            }
        }
        return $response;
    }

    public static function isLoggedIn($authorizedUserType=''){
        $response = new Response();
        $all_headers=getallheaders();
        $jwt = $all_headers['Authorization']?? '';

        if(empty($jwt)){
            $response->status='fail';
            $response->data='Unauthorized: Debe iniciar sesion para acceder a este recurso';
        }else{
            try {
                if($jwtDecoded = Usuario::checkLoggedIn($jwt)){
                    if(self::checkAccess($authorizedUserType,$jwtDecoded)){
                        $response->data=$jwtDecoded;
                        $response->status='success'; 
                    }else{
                        $response->data ='Recurso restringido para el usuario';
                        $response->status='error';      
                    }
                }else{
                    $response->data ='Error de sesion: Usuario no encontrado';
                    $response->status='error';                             
                }
            } catch (Exception $ex) {
                $response->data ='Error de sesion: '.$ex->getMessage();
                $response->status='error';
            }            
        }
        return $response;
    }

    public static function isLoggedInWithCallback($callback, $authorizedUserType='all', $params = array() ){
        $response = new Response();
        $all_headers=getallheaders();
        $jwt = $all_headers['Authorization']?? '';

        if(empty($jwt)){
            $response->status='fail';
            $response->data='Unauthorized: Debe iniciar sesion para acceder a este recurso';
        }else{
            try {
                if($jwtDecoded = Usuario::checkLoggedIn($jwt)){
                    if(self::checkAccess($authorizedUserType,$jwtDecoded)){
                        if($params=='jwt'){
                            $response= call_user_func($callback,$jwtDecoded);                            
                        }elseif(empty($params)){
                            $response= call_user_func($callback);                            
                        }else{
                            $response= call_user_func_array($callback, $params);
                        }
                    }else{
                        $response->data ='Recurso restringido para el usuario';
                        $response->status='error';      
                    }
                }else{
                    $response->data ='Error de sesion: Usuario no encontrado';
                    $response->status='error';                             
                }
            } catch (Exception $ex) {
                $response->data ='Error de sesion: '.$ex->getMessage();
                $response->status='error';
            }            
        }
        return $response;
    }

    public static function checkAccess($authorizedUserType,$payload){
        if($authorizedUserType=='' || $authorizedUserType=='all')
            return true;
        elseif($payload->data->tipo==$authorizedUserType)
            return true;
        else
            return false;
    }




    //producto, persona, item, etc--------------------------------------------------------------------------------------
    
    //3. (POST) materia: Recibe nombre, cuatrimestre y lo guarda en el archivo materias.xxx. Agregar un id único para cada materia.
    public static function post_addMateria(){      
        $response = new Response();
        
        $nombre=$_POST['nombre']??"";
        $cuatrimestre=$_POST['cuatrimestre']??"";

        if($nombre!='' || $cuatrimestre!=''){    
            $objProd = new Materia($nombre, $cuatrimestre);
            if($objProd->insert()>0){
                $response->data = "Materia registrada";
                $response->status = 'success';
            }else{
                $response->data = 'Error de registro';
                $response->status = 'fail';
            }
        }else{
            $response->data = 'Faltan datos';
            $response->status = 'fail';
        }

        return $response;
    }
    
    //6. (GET) materia: Muestra un listado con todas las materias.
    
    public static function get_materias(){       
        $response = new Response();

        $response->data = Materia::select()?? 'No hay registros en la base de datos';
        $response->status = 'success';

        return $response;
    }
    
    /*4. (POST) profesor: Recibe nombre, legajo (validar que sea único) y foto y lo guarda en el archivo profesores.xxx, a la imagen la guarda en la carpeta imágenes.*/
    
    public static function post_addProfesor(){      
        $response = new Response();
        
        $nombre=$_POST['nombre']??"";
        $legajo=$_POST['legajo']??"";
        $foto=is_uploaded_file($_FILES['imagen']['tmp_name']);

        if($nombre!='' || $legajo!=''|| $foto){    
            $objProd = new Profesor($nombre, $legajo, File::SetFileName('imagen'));
            if($objProd->insert()>0 && File::UploadFile('imagen')){
                $response->data = "Profesor registrado";
                $response->status = 'success';
            }else{
                $response->data = 'Error de registro';
                $response->status = 'fail';
            }
        }else{
            $response->data = 'Faltan datos';
            $response->status = 'fail';
        }

        return $response;
    }
    
    //7. (GET) profesor: Muestra un listado con todas las profesores.
    
    public static function get_profesores(){       
        $response = new Response();

        $response->data = Profesor::select()?? 'No hay registros en la base de datos';
        $response->status = 'success';

        return $response;
    }
    
    
    /*5. (POST) asignacion: Recibe legajo del profesor, id de la materia y turno (manana o noche) y lo guarda en el
        archivo materias-profesores. No se debe poder asignar el mismo legajo en el mismo turno y materia..*/

    public static function post_addAsignacion(){
        $response = new Response();
        
        $idMateria=$_POST['id']??"";
        $turno=$_POST['turno']??"";
        $legajoProfesor=$_POST['legajo']??"";


        if($idMateria!='' || $turno!=''|| $legajoProfesor!=''){    
            $obj = new Asignacion($idMateria, $turno, $legajoProfesor);
            if($obj->insert()){
                $response->data = 'Materia asignada';
                $response->status = 'success';               
            }else{
                $response->data = 'Error de registro';
                $response->status = 'fail';
            }
        }else{
            $response->data = 'Faltan datos';
            $response->status = 'fail';
        }

        return $response;

    }

    //8. (GET) asignacion: Muestra un listado con todas las materias asignadas a cada profesor.

    public static function get_materiasPorProfesor(){         
        $response = new Response();

        $lista = array();
        $profesores = Profesor::select();
        if($profesores){
            $i=0;
            foreach($profesores as $profesor){
                $lista[$i]['profesor']=$profesor;
                $lista[$i]['materias']=Asignacion::getMateriasByUserID($profesor->legajo)?? 'No tiene materias asignadas';
                $i++;
            }
            $response->data = $lista;
            $response->status = 'success';            
        }else{
            $response->data = 'No hay registros en la base de datos';
            $response->status = 'fail';  
        }


        return $response;
    }

}