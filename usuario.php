<?php
include_once 'datos.php';
include_once 'authJWT.php';

class Usuario{
    public $id;
    public $email;
    public $clave;
    public static $fileNameDataBase = 'users.txt';

    public function __construct($email, $clave)
    {   
        $this->email=$email;
        $this->clave=$clave;
    }
    
    public function signin(){                                   //registrar
        $usuarios= Datos::deserializar(self::$fileNameDataBase);
        if($usuarios){
            foreach($usuarios as $user){
                if($this->email == $user->email)        
                    return false;            
            }
            $ultimoID=(($usuarios[count($usuarios)-1])->id);
            $this->id=++$ultimoID;      
        }else{
            $usuarios = array();
            $this->id=1;
        }
        array_push($usuarios,$this);
        return Datos::serializar_guardar(self::$fileNameDataBase,$usuarios);
    }


    public static function login($email, $clave){
        $usuarios= Datos::deserializar(self::$fileNameDataBase);
        if($usuarios){
            foreach($usuarios as $user){
                if($email == $user->email && $clave==$user->clave)
                    return Auth::crearJWT(Auth::generarPayload(array("id"=>$user->id)));
            }     
        }
        return false;
    }

    public static function checkLoggedIn($jwt){   
        try {
            return Auth::autentificar($jwt);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function update(){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            foreach ($lista as $i=>$elemento) {
                if($elemento->id==$this->id){
                    $lista[$i]=clone $this;                              //si modifico $elemento no se guarda el cambio porque el foreach pasa por valor, no por referencia
                    break;
                }        
            }
            if(Datos::serializar_guardar(self::$fileNameDataBase, $lista))
                return true;
        }
        return false;
    }

    public static function delete($id){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            foreach ($lista as $i=>$elemento) {
                if($elemento->id==$id){
                    Datos::serializar_agregar('backup-'.self::$fileNameDataBase,$elemento);                   
                    array_splice($lista, $i, 1); 
                    break;
                }        
            }
            if(Datos::serializar_guardar(self::$fileNameDataBase, $lista))
                return true;
        }
        return false;
    }

    public static function getUserByID($id=0){
        $usuarios= Datos::deserializar(self::$fileNameDataBase);
        if($usuarios){
            if($id==0)
                return $usuarios;
            foreach($usuarios as $user){
                if($id == $user->id)
                    return $user;           
            }     
        }
        return false;
    }

    public static function getUsersByType($requestedType = 'all'){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            if($requestedType == 'all')
                return $lista;
            
            $sublista = array();
            foreach($lista as $user){
                if($user->tipo==$requestedType)
                    array_push($sublista, $user);
            }  
            return $sublista;          
        }
        return false;
    }

    public static function nameToID($nombre){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            foreach($lista as $elemento){
                if($nombre == $elemento->nombre)
                    return $elemento->id;           
            }     
        }
        return false;
    }

}