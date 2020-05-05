<?php

require_once('vendor/autoload.php');    //composer require firebase/php-jwt
use \Firebase\JWT\JWT;

class Auth{
    private static $key= 'pro3-parcial';
    
    public static function crearJWT($payload){
        return JWT::encode($payload, Auth::$key);
    }

    public static function generarPayload($user_data =''){
        $payload = array(
            "iss" => "localhost",
            "sub" => "",
            "aud" => "users",
            "iat" => time(),
            "nbf" => time() + 1,
            "exp" => time() + 600, //10 min
            "data" => $user_data
        );
        return $payload;
    }

    public static function autentificar($jwt){
        try {
            return JWT::decode($jwt, Auth::$key, array('HS256'));
        } catch (Exception $ex) {
            throw $ex;
        }
    }



}