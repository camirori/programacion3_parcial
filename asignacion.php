<?php

require_once 'profesor.php';

class Asignacion{
    public $id;
    public $idMateria;
    public $turno;
    public $legajoProfesor;
    public static $fileNameDataBase = 'materias-profesores.txt';

    public function __construct($idMateria, $turno, $legajoProfesor)
    {
        $this->idMateria=$idMateria;
        $this->turno=$turno;
        $this->legajoProfesor=$legajoProfesor;
    }

    public function insert(){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            foreach($lista as $item){
                if($this->legajoProfesor == $item->legajoProfesor && $this->turno == $item->turno && $this->idMateria == $item->idMateria)    
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
                    array_splice($lista, $i, 1); 
                    break;
                }        
            }
            if(Datos::serializar_guardar(self::$fileNameDataBase, $lista))
                return true;
        }
        return false;
    }

    public static function getVentasByUserType($requestedType='all'){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            if($requestedType=='all')
                return $lista;

            $sublista = array();
            foreach($lista as $venta){
                if((Usuario::getUserByID($venta->idComprador))->tipo==$requestedType)
                    array_push($sublista, $venta);
            }            
            return $sublista;            
        }
        return false;
    }

    public static function getMateriasByUserID($legajoProfesor=0){
        $lista= Datos::deserializar(self::$fileNameDataBase);
        if($lista){
            if($legajoProfesor==0)
                return $lista;
            $sublista = array();
            foreach($lista as $materia){
                if($materia->legajoProfesor==$legajoProfesor)
                    array_push($sublista, $materia);
            }
            return $sublista;            
        }
        return null;
    }




}