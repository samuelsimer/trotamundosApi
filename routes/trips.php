<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Crear ruta
$app->post('/trips', function (Request $request, Response $response){
    global $dbConnector,$jwtService;
    $authorizationHeader = $request->getHeaderLine('Authorization');
    $token = explode(' ', $authorizationHeader)[1];
    $decodedToken = $jwtService->validateToken($token);
    
    // Si no hay token devolvuelve error 401
    if($decodedToken == false){
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401); 
    }

    $user_id = $decodedToken->id;
    $requestBody = $request->getBody();
    $data = json_decode($requestBody, true);
    $name = $data['name'];
    $description = $data['description'];
    $height = $data['height'];
    $difficulty = $data['difficulty'];
    $distance = $data['distance'];

    $images = "";

    foreach ($data['images'] as $image) {
        $images = $images . $image . ";";
    }

    $lat = $data['lat'];
    $lon = $data['lon'];
    $userID = $user_id;

    // Insertar la ruta en la base de datos
    $query = "INSERT INTO routes (name, description, height, difficulty, distance, images, lat, lon, userID) VALUES (:name, :description, :height, :difficulty, :distance, :images, :lat, :lon, :userID)";
    $params = [
        'name' => $name,
        'description' => $description,
        'height' => $height, 
        'difficulty' => $difficulty, 
        'distance' => $distance, 
        'images' => $images, 
        'lat' => $lat, 
        'lon' => $lon, 
        'userID' => $userID
    ];
    try {
        $result = $dbConnector->query($query, $params);
        $responseData = [
            'status' => 'success',
            'message' => 'Route created successfully'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (Exception $e) {
        $responseData = [
            'status' => 'error',
            'message' => 'Failed to create route'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Listado rutas
$app->get('/trips', function (Request $request, Response $response, $args)  {
    global $dbConnector, $jwtService;

    $query = "SELECT * FROM routes";
    $params = [];
    $result = $dbConnector->query($query, $params);

    // Verificar si se obtuvieron rutas
    if (empty($result)) {
        $responseData = [
            'status' => 'error',
            'message' => 'No routes found for the user'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);    
    } else {
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);   
    }
});

// Eliminar ruta
$app->delete('/trips/{routeId}', function (Request $request, Response $response, $args) {
    global $dbConnector, $jwtService;

    $routeId = $args['routeId'];
    $authorizationHeader = $request->getHeaderLine('Authorization');
    $token = explode(' ', $authorizationHeader)[1];
    $decodedToken = $jwtService->validateToken($token);

    if($decodedToken == false){
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401); 
    }

    $userId = $decodedToken->id;

    // Verificar si la ruta existe y pertenece al usuario
    $query = "SELECT * FROM routes WHERE id = ? AND userID = ?";
    $params = [$routeId, $userId];
    $result = $dbConnector->query($query, $params);
    echo $userId;
    if (empty($result)) {
        // La ruta no existe o no pertenece al usuario
        $responseData = [
            'status' => 'error',
            'message' => 'No hay ninguna ruta que pertenezca al usuario con esos parametros'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);   
    }

    // Eliminar la ruta
    $query = "DELETE FROM routes WHERE id = ? AND userID = ?";
    $params = [$routeId, $userId];

    try {
        $dbConnector->query($query, $params);
        $responseData = [
            'status' => 'success',
            'message' => 'La ruta fue eliminada correctamente'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);   
    } catch (Exception $e) {
        $responseData = [
            'status' => 'error',
            'message' => 'Fallo al eliminar la ruta'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);   
        }
});


// Actualizar ruta
$app->post('/trips/{routeId}', function (Request $request, Response $response, $args) {
    global $dbConnector,$jwtService;
    $authorizationHeader = $request->getHeaderLine('Authorization');
    $token = explode(' ', $authorizationHeader)[1];
    $decodedToken = $jwtService->validateToken($token);
    $userID = $decodedToken->id;

    if($decodedToken == false){
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401); 
    }
    $requestBody = $request->getBody();
    $data = json_decode($requestBody, true);

    $name = $data['name'];
    $description = $data['description'];
    $height = $data['height'];
    $difficulty = $data['difficulty'];
    $distance = $data['distance'];
    $images = "";

    foreach ($data['images'] as $image) {
        $images = $images . $image . ";";
    }
    $lat = $data['lat'];
    $lon = $data['lon'];
    $routeId = $args['routeId'];

    


    // Verificar si la ruta existe y pertenece al usuario
    $query = "SELECT * FROM routes WHERE id = ? AND userID = ?";

    $params = [$routeId, $userID];
    $result = $dbConnector->query($query, $params);

    if (empty($result)) {
        // La ruta no existe o no pertenece al usuario
        $responseData = [
            'status' => 'error',
            'message' => 'La ruta no existe o no pertenece al usuario'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404); 
    }

    // Obtener los datos enviados en la solicitud

    // Actualizar la ruta en la base de datos
    $query = "UPDATE routes SET name = ?, description = ?, height = ?, difficulty = ?, distance = ?, images = ?, lat = ?, lon = ? WHERE id = ? AND userID = ?";
    $params = [$name, $description, $height, $difficulty, $distance, $images, $lat, $lon, $routeId, $userID];

    try {
        $dbConnector->query($query, $params);
        $responseData = [
            'status' => 'success',
            'message' => 'La ruta se actualizo correctamente'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200); 
        } catch (Exception $e) {
        $responseData = [
            'status' => 'error',
            'message' => 'Error al actualizar la ruta'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500); 
        }
});

// Rutas filtradas
$app->get('/trips/filtered', function (Request $request, Response $response) {
    global $dbConnector;
    
    // Obtener los parámetros de la consulta
    $queryParams = $request->getQueryParams();
    $name = $queryParams['name'] ?? null;
    $difficulty = $queryParams['difficulty'] ?? null;
    $maxLength = $queryParams['max_distance'] ?? null;
    
    // Crear la consulta base
    $query = "SELECT * FROM routes WHERE 1";
    $params = [];
    
    // Agregar las condiciones según los filtros ingresados
    if ($name !== null) {
        $query .= " AND name LIKE %?%";
        $params[] = $name;
    }
    
    if ($difficulty !== null) {
        $query .= " AND difficulty = ?";
        $params[] = $difficulty;
    }
    
    if ($maxLength !== null) {
        $query .= " AND distance <= ?";
        $params[] = $maxLength;
    }
    
    // Ejecutar la consulta
    $result = $dbConnector->query($query, $params);
    
    // Obtener los resultados
    if (empty($result)) {
        // La ruta no existe o no pertenece al usuario
        $responseData = [
            'status' => 'error',
            'message' => 'No existen resultados con esos filtros'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404); 
    }else{
        $responseData = [
            'status' => 'success',
            'data' => $result
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

});


?>

