<?php

class Datos{

    //Guardar------------------------------------------------------------------------
    public static function txt_guardar($fileName, $datos){
        $myfile = fopen($fileName, "a") or die("Unable to open file!");
        $rta = fwrite($myfile, $datos);		//.PHP_EOL para agregar salto de línea, en este caso está ya en $datos
        fclose($myfile);

        return $rta;        //fwrite devuelve cant de datos escritos
    }

    public static function json_guardar($fileName, $objArr){     //no agrega un item sino que guarda un nuevo array
        $myfile = fopen($fileName, "w") or die("Unable to open file!");      //crea nuevo archivo o reemplaza
        $rta = fwrite($myfile, json_encode($objArr));
        fclose($myfile);       

        return $rta;       
    }

    public static function json_agregar($fileName, $objeto){
        $arrayJson=self::leerJSON($fileName);
        if(!$arrayJson)             //false o null
            $arrayJson=array();
            
        if(array_push($arrayJson,$objeto)>0){
            $myfile = fopen($fileName, "w") or die("Unable to open file!");      //crea nuevo archivo o reemplaza
            $rta = fwrite($myfile, json_encode($arrayJson));
            fclose($myfile);            
        }
        return $rta;       
    }

    public static function serializar_guardar($fileName, $datos){     //datos array de objetos
        $myfile = fopen($fileName, "w") or die("Unable to open file!");
        $rta = fwrite($myfile, serialize($datos));	
        fclose($myfile);

        return $rta;        //fwrite devuelve cant de datos escritos
    }

    public static function serializar_agregar($fileName, $nuevoDato){     //$nuevoDato objeto individual
        $datos= self::deserializar($fileName);
        if(!$datos){
            $datos = array();
        }
        if(array_push($datos,$nuevoDato)>0){
            $myfile = fopen($fileName, "w") or die("Unable to open file!");
            $rta = fwrite($myfile, serialize($datos));	
            fclose($myfile);            
        }
        return $rta;        //fwrite devuelve cant de datos escritos
    }

    //Leer-----------------------------------------------------------------------------------
    public static function leerJSON($fileName){
        if(file_exists($fileName) && filesize($fileName)>0){
            $myfile = fopen($fileName, "r") or die("Unable to open file!");
            $arrayJson = json_decode(fread($myfile,filesize($fileName)));
            fclose($myfile);

            return $arrayJson;                   //hay que instanciar cada elem, devuelve stdClass                  
        }
        return null; 
    }


    public static function leerTodoRaw($fileName){
        if(file_exists($fileName) && filesize($fileName)>0){
            $myfile = fopen($fileName, "r") or die("Unable to open file!");
            $rta= fread($myfile,filesize($fileName));	                  
            fclose($myfile); 
            return $rta;                      
        }
        return null;
    }

    public static function leerTodoTxt($fileName){
        $myfile = fopen($fileName, "r") or die("Unable to open file!");
        $retorno=array();
        while(!feof($myfile)) {			// Output one line until end-of-file
            $linea=explode('@',fgets($myfile));  //explode devuelve un array donde cada elem es una sección separada por el @
            if($linea>1)                            //para que no guarde la última línea vacía
                array_push($retorno,$linea);         
        }
        fclose($myfile);
        return $retorno;
    }

    public static function deserializar($fileName){
        $datosDeserializados= unserialize(self::leerTodoRaw($fileName));    //no necesita include 'Clase.php' si ya está incluido en index
        return $datosDeserializados;    //devuelve un array de objetos
    }
}


