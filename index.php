<?php
require 'vendor/autoload.php';
use Slim\Factory\AppFactory;

require 'Config/DatabaseConector.php';
require 'services/JwtService.php';

// Habilitar CORS en PHP
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$dbConfig = require './Config/config.php'; // Archivo de configuración con la información de la base de datos
try {

    // Configuración de la base de datos y otros componentes
    $dbConnector = DatabaseConnector::getInstance($dbConfig['host'], $dbConfig['dbname'], $dbConfig['user'], $dbConfig['pass']);

    // Configuración de la base de datos y otros componentes
    echo "Conexion establecida con la base de datos\n";

    // Configuración del servicio JWT
    $jwtSecretKey = 'prueba'; 
    $jwtExpirationTime = 3600; // Tiempo de expiración en segundos (1 hora)
    $jwtService = JwtService::getInstance($jwtSecretKey, $jwtExpirationTime);

    $app = AppFactory::create();

    // Rutas generales
    require 'routes/generalRoutes.php';

    // Rutas de usuarios
    require 'routes/userRoutes.php';

    // Rutas de rutas
    require 'routes/trips.php';

    $app->run();

} catch (Exception $e) {
    // Error al conectar a la base de datos
    echo "Error al conectar a la base de datos: " . $e->getMessage();
    exit; // Terminar la ejecución del script
}
?>
