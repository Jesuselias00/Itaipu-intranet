<?php
// app/api.php

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/controllers/FuncionarioController.php';

$router = new Router();

// Rota de Teste Simples
$router->get('test-router', function() {
    echo json_encode(['status' => 'success', 'message' => 'Roteador funcionando!']);
});

// Rota para Listar Funcionários
$router->get('funcionarios', 'FuncionarioController@index');

$router->dispatch();
