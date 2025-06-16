<?php
// app/api.php

// Prevenir que se muestre HTML antes que JSON
if (ob_get_level() === 0) {
    ob_start();
}

// Forçar encabezado JSON
header('Content-Type: application/json');

// Para depuração durante o desenvolvimento (eliminar em produção)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Registrar o início da execução da API
file_put_contents(__DIR__ . '/core/router_debug.log', 
    date('c') . " | API.php iniciado | URI: " . $_SERVER['REQUEST_URI'] . " | Método: " . $_SERVER['REQUEST_METHOD'] . "\n", 
    FILE_APPEND);

try {
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/core/Router.php';
    require_once __DIR__ . '/controllers/FuncionarioController.php';

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
    $router->put('funcionarios/{id}', 'FuncionarioController@update'); // Atualizar
    $router->delete('funcionarios/{id}', 'FuncionarioController@destroy'); // Excluir

    // Log para mostrar que o arquivo api.php foi carregado
    file_put_contents(__DIR__ . '/core/router_debug.log', 
        date('c') . " | API.php | Rotas registradas | Despachando...\n", FILE_APPEND);

    // Despachar a requisição
    $router->dispatch();

} catch (Exception $e) {
    // Limpiar el buffer de salida
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Asegurarse de que el encabezado Content-Type sea JSON
    header('Content-Type: application/json');
    
    // Registrar el error
    file_put_contents(__DIR__ . '/core/router_debug.log', 
        date('c') . " | API.php | ERROR: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // Enviar respuesta de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}

// Finalizar qualquer buffer aberto
if (ob_get_level() > 0) {
    ob_end_flush();
}
