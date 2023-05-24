<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Login
$app->post('/users/login', function (Request $request, Response $response) {
    global $dbConnector,$jwtService;

    // Obtener los datos enviados en la solicitud
    $requestBody = $request->getBody();
    $data = json_decode($requestBody, true);
    $email = $data['email'];
    $password = $data['password'];

    // Crear la consulta utilizando parámetros preparados
    $query = 'SELECT * FROM users WHERE email = ? AND pass = ?';
    $params = [$email, $password];
    $user = $dbConnector->query($query, $params);

    if (!empty($user)) {
        $username = $user[0]['username'];
        $id = $user[0]['id'];

        $payload = [
            'username' => $username,
            'email' => $email,
            'id' => $id,

        ];
        $token = $jwtService->generateToken($payload);
        // Credenciales válidas
        $responseData = [
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } else {
        // Credenciales inválidas
        $responseData = [
            'status' => 'error',
            'message' => 'Invalid email or password'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});

//  Registro
$app->post('/users/register', function (Request $request, Response $response) {
    global $dbConnector, $jwtService;

    // Obtener los datos enviados en la solicitud
    $requestBody = $request->getBody();
    $data = json_decode($requestBody, true);

    $fullname  = $data['fullname'];
    $username  = $data['username'];
    $email  = $data['email'];
    $pass  = $data['pass'];
    $height  = $data['height'];
    $weight  = $data['weight'];
    $birthday  = $data['birthday'];
    $activities = "";

    foreach ($data['activities'] as $activity) {
        $activities = $activities . $activity . "-";
    }

    // Insertar usuario en la base de datos
    $sql = "INSERT INTO users (fullname, username, email, pass,
    height, weight, birthday, activities) VALUES (:fullname, :username, :email, :pass,
        :height, :weight, :birthday, :activities)";
    $params = [
        'fullname' => $fullname,
        'username' => $username,
        'email' => $email,
        'pass' => $pass,
        'height' => $height,
        'weight' => $weight,
        'birthday' => $birthday,
        'activities' => $activities
    ];
    
    try {
        $dbConnector->query($sql, $params);
        $responseData = [
            'status' => 'success',
            'message' => 'User registered successfully'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $responseData = [
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Rutas usuario
$app->get('/user/routes', function (Request $request, Response $response, $args)  {
    global $dbConnector, $jwtService;
    $authorizationHeader = $request->getHeaderLine('Authorization');
    $token = explode(' ', $authorizationHeader)[1];
    $decodedToken = $jwtService->validateToken($token);
    $user_id = $decodedToken->id;

    $query = "SELECT * FROM routes WHERE userID = ?";
    $params = [$user_id];
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
?>
