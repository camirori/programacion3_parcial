<?php

class File{
    private static $folder='uploads/';
    private static $backupFolder='backup/';

    public static function UploadFile($httpKey = 'archivo'){
        if(!self::isValidFile($httpKey))
            return false;
        self::CreateFolder(self::$folder);
        $destinoName = self::SetFileName($httpKey);
        $tmp_name= $_FILES[$httpKey]['tmp_name'];  //nombre temporal del fichero en el cual se almacena el fichero subido en el servidor
        return move_uploaded_file($tmp_name,$destinoName);      //si ok ret 1, sino 0
    }

    public static function SetFileName($httpKey = 'archivo', $isBackup = false){
        $folder = $isBackup? self::$backupFolder : self::$folder;
        $originalName= $_FILES[$httpKey]['name'];  //nombre original del fichero en la máquina del cliente
        $originalName=str_replace(' ','-', $originalName);
        $ext= '.'.pathinfo($originalName,PATHINFO_EXTENSION);
        $originalNameSinExt=basename($originalName,$ext);
        $destinoName=$folder.$originalNameSinExt;     //para que no pise una img de otro usuario, se podría usar un número aleatorio
        
        $sufijo='';                                     //si ya existe el file agrega un sufijo
        for ($i=1; file_exists($destinoName.$sufijo.$ext); $i++) { 
            $sufijo='('.$i.')';
        }
        return $destinoName.$sufijo.$ext;;
    }

    public static function SetFolder($newFolder){  //$folder debe incluir / al final
        self::$folder=$newFolder;
        self::CreateFolder(self::$folder);
    }
    private static function CreateFolder($newFolder){
        if(!file_exists($newFolder))
            return mkdir($newFolder);
        return true;
    }

    public static function DeleteFile($fileName){         //fileName es el nombre devuelto por SetFileName cuando se subió el archivo
        self::CreateFolder(self::$backupFolder); 
        $destinoBackup = self::$backupFolder.basename($fileName);
        copy($fileName, $destinoBackup);
        return unlink($fileName);  
    }

    private static function isValidFile($httpKey = 'archivo', $maxsize = 1000000*10, $fileType = array(), $nameMaxLength=250){
        //corroborar que el archivo subido no esté vacio
        //Limit File Size    1 000 000 bytes = 1mb 
        if($_FILES[$httpKey]['size']==0 || $_FILES[$httpKey]['size']>$maxsize)
            return false;

        //Limit File Type, param fileType sin '.'
        if($fileType){
            $ext= strtolower(pathinfo($_FILES[$httpKey]['name'],PATHINFO_EXTENSION));
            if(gettype($fileType)=='string'){
                if($ext!=$fileType)
                    return false;
            }
            if(gettype($fileType)=='array'){
                if(!in_array($ext, $fileType))
                    return false;
            }
        }

        //Limit name length
        if(strlen($_FILES[$httpKey]['name'])>$nameMaxLength)
            return false;

        return true;

        //Second : make sure the file name in English characters, numbers and (_-.) symbols, For more protection.
        // function check_file_uploaded_name ($filename)
        // {
        //     (bool) ((preg_match("`^[-0-9A-Z_\.]+$`i",$filename)) ? true : false);
        // }

        // Fifth: Check file size and make sure the limit of php.ini to upload files is what you want, You can start from http://www.php.net/manual/en/ini.core.php#ini.file-uploads
        // And last but not least : Check the file content if have a bad codes or something like this function http://php.net/manual/en/function.file-get-contents.php.
        // You can use .htaccess to stop working some scripts as in example php file in your upload path.
        // use :
        // AddHandler cgi-script .php .pl .jsp .asp .sh .cgi
        // Options -ExecCGI 
    }

    public static function CrearMarcaDeAgua($fileName, $fileNameMarca){
        $img=imagecreatefrompng($fileName); //o jpeg imagecreatefromjpeg
        $marca=imagecreatefrompng($fileNameMarca);
        $margenDerecho=10;
        $margenInf=10;
        $sx=imagesx($marca);
        $sy=imagesy($marca);
        imagecopymerge($img,$marca,imagesx($img)-$sx-$margenDerecho, imagesy($img)-$sy-$margenInf,0,0,$sx,$sy,50);
        imagepng($img,$fileName);
        imagedestroy($img);

    }

}


//opleadFile debería dar la opc de indicar una carpeta especifica?
// crear un getArchivo que muestre por ej una img