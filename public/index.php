<?php
// public/index.php

// Para depuración durante el desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Registra la ruta actual para depuración
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
file_put_contents(__DIR__ . '/../app/core/router_debug.log', 
    date('c') . " | INDEX.PHP | Método: $method | URI: $uri\n", FILE_APPEND);

// Detección más robusta para la API
$isApiRequest = false;

// 1. Verificar si la URL tiene /api/ en cualquier parte de la ruta
if (strpos($uri, '/api/') !== false) {
    $isApiRequest = true;
}

// 2. Verificar si la URL tiene /Itaipu-intranet/api/ (para instalaciones en subdirectorios)
if (strpos($uri, '/Itaipu-intranet/api/') !== false) {
    $isApiRequest = true;
}

// Registra si es una solicitud a la API o no
file_put_contents(__DIR__ . '/../app/core/router_debug.log', 
    date('c') . " | INDEX.PHP | Es solicitud API: " . ($isApiRequest ? 'Sí' : 'No') . "\n", FILE_APPEND);

if ($isApiRequest) {
    // Forzar encabezados para JSON en todas las respuestas API
    header('Content-Type: application/json');
    
    // Permitir CORS para pruebas (ajustar en producción)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Manejar preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Procesar métodos PUT y DELETE
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        parse_str(file_get_contents('php://input'), $_PUT);
        $_REQUEST = array_merge($_REQUEST, $_PUT);
    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents('php://input'), $_DELETE);
        $_REQUEST = array_merge($_REQUEST, $_DELETE);
    }
    
    // Capturar el buffer de salida para evitar que se muestre HTML antes del JSON
    ob_start();
    
    try {
        require_once __DIR__ . '/../app/core/Router.php';
        
        // Enrutar la solicitud usando el Router
        $method = $_SERVER['REQUEST_METHOD'];
        $request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
        $resource = $request[0] ?? null;
        $id = $request[1] ?? null;
        
        Router::route($method, $resource, $id);
    } catch (Exception $e) {
        // Limpiar cualquier salida anterior
        ob_clean();
        
        // Responder con un error en formato JSON
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
        
        // Registrar el error para depuración
        file_put_contents(__DIR__ . '/../app/core/router_debug.log', 
            date('c') . " | INDEX.PHP | ERROR: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    }
    
    // Finalizar la solicitud API
    exit;
} else {
    // Si no es API, servir contenido HTML o redirigir al frontend
    header('Content-Type: text/html');
    readfile(__DIR__ . '/index.html');
}
