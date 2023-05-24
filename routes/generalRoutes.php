<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Ruta de inicio
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("¡Bienvenido a la aplicación!");
    return $response;
});

// Ruta de información
$app->get('/info', function (Request $request, Response $response) {
    $info = [
        'version' => '1.0',
        'author' => 'Samuel Remis',
        'description' => 'Api para la gestión de usuarios y rutas en la aplicación web trotamundos'
    ];
    $response->getBody()->write(json_encode($info));
    return $response->withHeader('Content-Type', 'application/json');
});

?>