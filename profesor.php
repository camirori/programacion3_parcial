<?php

include_once './datos.php';

class Profesor{
    public $nombre; 
    public $legajo;
    public $foto;
    public $id;
    public static $fileNameDataBase = 'profesores.txt';


    public function __construct($nombre, $legajo, $foto) 
    {
        $this->nombre=$nombre;
        $this->legajo=$legajo;
        $this->foto=$foto;
    }

    public function insert(){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            foreach($lista as $item){
                if($this->legajo == $item->legajo)    
                    return false;
            }
            $ultimoID=(($lista[count($lista)-1])->id);
            $this->id=++$ultimoID;    
        }else{
            $lista = array();
            $this->id=1;
        }
        array_push($lista,$this);
        return Datos::serializar_guardar(self::$fileNameDataBase,$lista);
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
                    File::DeleteFile($elemento->foto);
                    array_splice($lista, $i, 1); 
                    break;
                }        
            }
            if(Datos::serializar_guardar(self::$fileNameDataBase, $lista))
                return true;
        }
        return false;
    }

    public static function select($id=0){                
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            if($id==0)
                return $lista;
            foreach($lista as $user){
                if($id == $user->id)
                    return $user;           
            }     
        }
        return null;
    }


}