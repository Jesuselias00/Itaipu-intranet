<?php
// Establecer encabezado de tipo de contenido a JSON
header('Content-Type: application/json');

// Crear un array con datos de prueba
$data = [
    'status' => 'success',
    'message' => 'API de prueba funcionando correctamente',
    'timestamp' => time(),
    'server_info' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'http_host' => $_SERVER['HTTP_HOST']
    ]
];

// Devolver como JSON
echo json_encode($data, JSON_PRETTY_PRINT);
