<?php
// public/index.php

// Detecta se a requisição é para a API (ex: /api/*)
$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/api/') === 0) {
    // Define o cabeçalho para indicar que a resposta será JSON
    header('Content-Type: application/json');
    // Inclui a lógica principal da sua aplicação/API
    require_once __DIR__ . '/../app/api.php';
    // Toda a lógica da API será tratada em app/api.php
    exit;
}

// Se não for uma rota de API, pode servir um arquivo HTML ou redirecionar para o frontend
// Exemplo simples: servir um index.html (ajuste conforme seu frontend)
header('Content-Type: text/html');
readfile(__DIR__ . '/index.html');
