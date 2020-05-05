<?php

include_once './datos.php';

class Materia{
    public $nombre; 
    public $cuatrimestre;
    public $id;
    public static $fileNameDataBase = 'materias.txt';


    public function __construct($nombre, $cuatrimestre) 
    {
        $this->nombre=$nombre;
        $this->cuatrimestre=$cuatrimestre;
    }

    public function insert(){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
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
                    array_splice($lista, $i, 1); 
                    break;
                }        
            }
            if(Datos::serializar_guardar(self::$fileNameDataBase, $lista))
                return true;
        }
        return false;
    }

    public static function select($id=0){                 //si no se pasa id devuelve todos
        $lista = Datos::deserializar(self::$fileNameDataBase);      
        if($lista){
            if($id==0)
                return $lista;
            else{                                           //buscar solo ese legajo
                foreach ($lista as $elemento) {
                    if($elemento->id==$id)       
                        return $elemento;
                }
                return false;                     //no existe el usuario
            }
        }
        return null;               //no hay usuarios registrados
    }


}