<?php
// app/api.php

// Habilitar la visualización de errores para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Headers para permitir CORS (Cross-Origin Resource Sharing) ---
header("Access-Control-Allow-Origin: *"); // En producción, deberías restringirlo a tu dominio
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Si es una solicitud OPTIONS (preflight de CORS), terminar aquí con éxito.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// --- El tipo de contenido de la respuesta siempre será JSON ---
header("Content-Type: application/json; charset=UTF-8");

// --- Log de depuración ---
$log_file_path = __DIR__ . '/core/router_debug.log';
$log_message = sprintf(
    "[%s] %s %s",
    date('c'),
    $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    $_SERVER['REQUEST_URI'] ?? 'N/A'
);
@file_put_contents($log_file_path, $log_message . "\n", FILE_APPEND);

// Depuración: log de variables de entorno
file_put_contents(__DIR__ . '/core/router_debug.log', print_r($_SERVER, true), FILE_APPEND);

// Forzar la lectura del parámetro _url como la ruta solicitada
if (isset($_GET['_url'])) {
    $_SERVER['REQUEST_URI'] = $_GET['_url'];
}

// --- Bloque principal de la aplicación ---
try {
    // 1. Cargar el autoloader de Composer. Esto carga TODAS las librerías, incluyendo Bramus Router.
    require_once __DIR__ . '/../vendor/autoload.php';

    // 2. Cargar los CONTRALADORES (la lógica de tu aplicación)
    require_once __DIR__ . '/controllers/FuncionarioController.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/CrachaController.php';

    // 3. Crear una instancia del Router de Bramus
    $router = new \Bramus\Router\Router();

    // 4. Definir todas tus rutas de la API
    // El router se encargará de llamar al método correcto en el controller correcto.

    // Rota de Teste
    $router->get('/test-router', function () {
        echo json_encode(['status' => 'success', 'message' => 'Bramus Router está funcionando!']);
    });

    // --- Rutas para Funcionarios ---
    $router->get('/funcionarios', 'FuncionarioController@handleRequest');
    $router->get('/funcionarios/(\d+)', 'FuncionarioController@handleRequest');
    $router->post('/funcionarios', 'FuncionarioController@handleRequest');
    $router->put('/funcionarios/(\d+)', 'FuncionarioController@handleRequest');
    $router->delete('/funcionarios/(\d+)', 'FuncionarioController@handleRequest');

    // --- Rutas para Autenticação ---
    $router->post('/auth/login', 'AuthController@login');
    $router->post('/auth/register', 'AuthController@register');
    $router->post('/auth/logout', 'AuthController@logout');
    $router->get('/auth/users', 'AuthController@getAllUsers');

    // --- Rutas para Crachás ---
    $router->get('/crachas', 'CrachaController@index');
    $router->get('/crachas/pendentes', 'CrachaController@pendentes');
    // Agrega aquí el resto de rutas de crachas...

    // Middleware para rutas no encontradas (404)
    $router->set404(function () {
        header('HTTP/1.1 404 Not Found');
        echo json_encode([
            'status' => 'error',
            'message' => 'Endpoint no encontrado'
        ]);
    });

    // 5. Ejecutar el router
    $router->run();

} catch (Throwable $e) {
    // --- Manejo de Errores Graves ---
    // Si algo falla (ej: conexión a DB, error de sintaxis), este bloque lo captura.
    
    // Limpiar cualquier salida anterior para asegurar una respuesta JSON limpia
    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    $error_log = sprintf(
        "[%s] ERRO FATAL: %s em %s na linha %d\nTrace: %s\n",
        date('c'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    @file_put_contents($log_file_path, $error_log, FILE_APPEND);
    
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno do servidor.',
        'detail' => $e->getMessage() // Útil para depuración
    ]);
}
