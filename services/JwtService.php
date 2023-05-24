<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private static $instance;
    private $secretKey;

    private function __construct($secretKey){
        $this->secretKey = $secretKey;
    }

    public static function getInstance($secretKey){
        if (self::$instance === null) {
            self::$instance = new JwtService($secretKey);
        }

        return self::$instance;
    }

    public function generateToken($payload){
        $token = JWT::encode($payload, $this->secretKey, 'HS256');
        return $token;
    }

    public function validateToken($token){
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }
}