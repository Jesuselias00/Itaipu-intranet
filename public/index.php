<?php
// public/index.php

// Detecta se a requisição é para a API (ex: /api/*)
$uri = $_SERVER['REQUEST_URI'];

// Ajuste para funcionar mesmo se o projeto estiver em um subdiretório
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$apiPrefix = $scriptName . '/api/';

if (strpos($uri, $apiPrefix) === 0) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/../app/api.php';
    exit;
}

// Se não for uma rota de API, pode servir um arquivo HTML ou redirecionar para o frontend
header('Content-Type: text/html');
readfile(__DIR__ . '/index.html');
