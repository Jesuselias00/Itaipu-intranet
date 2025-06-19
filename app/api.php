<?php
// app/api.php

// Asegurar que todos los errores sean reportados
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Permitir solicitudes desde cualquier origen (para desenvolvimento)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8"); // Forzar el tipo de contenido a JSON

// Si es una solicitud OPTIONS (preflight), terminar aquí.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Registrar la solicitud para depuración
$log_file_path = __DIR__ . '/core/router_debug.log';
@file_put_contents($log_file_path, 
    date('c') . " | NUEVA API SOLICITUD | " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . " | Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n", 
    FILE_APPEND);

// Intento de log inicial absoluto para depuración de creación de archivo
$log_file_path = __DIR__ . '/core/router_debug.log';
$initial_log_message = date('c') . " | API.php SCRIPT EJECUTADO INICIO\n";
@file_put_contents($log_file_path, $initial_log_message, FILE_APPEND);

// Para depuração durante o desenvolvimento (mostrar errores temporalmente)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevenir que se muestre HTML antes que JSON
if (ob_get_level() === 0) {
    ob_start();
}

// Forçar encabezado JSON solo si no hay errores fatales antes
// header('Content-Type: application/json'); // Comentado temporalmente para ver errores HTML si ocurren

// Registrar o início da execução da API (segundo intento, después de ob_start)
@file_put_contents($log_file_path, 
    date('c') . " | API.php iniciado (después de ob_start) | URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . " | Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n", 
    FILE_APPEND);

try {    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/core/Router.php';
    require_once __DIR__ . '/controllers/FuncionarioController.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/CrachaController.php';

    // Si llegamos aquí, los archivos base se cargaron.
    @file_put_contents($log_file_path, date('c') . " | API.php | Archivos core y controllers cargados.\n", FILE_APPEND);

    $router = new Router();

    // Rota de Teste Simples
    $router->get('test-router', function() {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Roteador funcionando!',
            'timestamp' => time()
        ]);
    });

    // Rotas para funcionários - usando o formato Controller@method
    $router->get('funcionarios', 'FuncionarioController@index'); // Listar todos
    $router->post('funcionarios', 'FuncionarioController@store'); // Criar novo
    $router->get('funcionarios/{id}', 'FuncionarioController@show'); // Obter um específico
    $router->put('funcionarios/{id}', 'FuncionarioController@update'); // Atualizar via PUT
    $router->post('funcionarios/{id}', 'FuncionarioController@update'); // Adicionado para aceitar POST como atualização
    $router->delete('funcionarios/{id}', 'FuncionarioController@destroy'); // Excluir
    
    // Rotas de Autenticação
    $router->post('auth/login', 'AuthController@login');
    $router->post('auth/logout', 'AuthController@logout');
    $router->post('auth/register', 'AuthController@register'); // Registrar novo usuário
    $router->get('auth/users', 'AuthController@getAllUsers'); // Listar usuários (para administradores)

    // Rotas para crachás
    $router->get('crachas', 'CrachaController@index'); // Listar todos
    $router->get('crachas/pendentes', 'CrachaController@pendentes'); // Listar pendentes
    $router->get('crachas/status/{status}', 'CrachaController@status'); // Filtar por status
    $router->get('crachas/motivos', 'CrachaController@motivos'); // Listar motivos
    $router->get('crachas/funcionario/{id}', 'CrachaController@porFuncionario'); // Listar por funcionário
    $router->post('crachas', 'CrachaController@store'); // Criar novo
    $router->get('crachas/{id}', 'CrachaController@show'); // Obter um específico
    $router->put('crachas/{id}', 'CrachaController@update'); // Atualizar
    $router->put('crachas/{id}/status', 'CrachaController@updateStatus'); // Atualizar status
    $router->delete('crachas/{id}', 'CrachaController@destroy'); // Excluir

    // Log para mostrar que o arquivo api.php foi carregado
    @file_put_contents($log_file_path, 
        date('c') . " | API.php | Rotas registradas | Despachando...\n", FILE_APPEND);    // Registrar las rutas disponibles para depuración
    $availableRoutes = implode(", ", $router->getRegisteredRoutes());
    @file_put_contents($log_file_path, 
        date('c') . " | API.php | Rutas disponibles: " . $availableRoutes . "\n", 
        FILE_APPEND);

    // Registrar la ruta solicitada para depuración
    @file_put_contents($log_file_path, 
        date('c') . " | API.php | Ruta solicitada: " . ($router->getCurrentRoute() ?? 'N/A') . "\n", 
        FILE_APPEND);

    // Despachar a requisição
    $router->dispatch();

    @file_put_contents($log_file_path, date('c') . " | API.php | Despacho completado.\n", FILE_APPEND);

} catch (Throwable $e) { // Cambiado a Throwable para capturar Errores y Excepciones
    // Limpiar el buffer de salida si es necesario
    if (ob_get_level() > 0 && ob_get_length() > 0) {
        ob_clean();
    }
    
    // Asegurarse de que el encabezado Content-Type sea JSON para la respuesta de error
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $error_message = date('c') . " | API.php | ERROR CAPTURADO: " . $e->getMessage() . " | Archivo: " . $e->getFile() . " | Línea: " . $e->getLine() . " | Trace: " . $e->getTraceAsString() . "\n";
    @file_put_contents($log_file_path, $error_message, FILE_APPEND);
    
    // Enviar respuesta de error JSON
    if (!headers_sent()) {
         http_response_code(500);
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} finally {
    // Finalizar qualquer buffer aberto
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    @file_put_contents($log_file_path, date('c') . " | API.php SCRIPT EJECUTADO FIN\n", FILE_APPEND);
}
