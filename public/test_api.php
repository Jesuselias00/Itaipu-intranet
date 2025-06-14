<?php
// public/test_api.php

// Define o cabeçalho para indicar que a resposta será JSON
header('Content-Type: application/json');

// Inclui a classe de conexão com o banco de dados
require_once __DIR__ . '/../app/core/Database.php';

// Cria uma instância da classe Database
$database = new Database();
$response = []; // Array para armazenar a resposta da API

try {
    // Tenta obter a conexão PDO
    $pdo = $database->connect();
    
    // Se a conexão for bem-sucedida, você pode testar uma consulta simples
    $stmt = $pdo->query("SELECT id_departamento, nome_departamento FROM departamentos LIMIT 2");
    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['message'] = 'Conexão com o banco de dados bem-sucedida e dados de departamentos obtidos.';
    $response['data'] = $departamentos;

} catch (PDOException $e) {
    // Se houver um erro de conexão ou na consulta
    $response['status'] = 'error';
    $response['message'] = 'Erro de conexão ou consulta ao banco de dados: ' . $e->getMessage();
    // Em um ambiente de produção, evite mostrar e.getMessage() diretamente por segurança
} catch (Exception $e) {
    // Para outros tipos de erros
    $response['status'] = 'error';
    $response['message'] = 'Erro inesperado: ' . $e->getMessage();
}

// Retorna a resposta em formato JSON
echo json_encode($response);

// IMPORTANTE: Remova este arquivo 'test_api.php' da pasta public/ quando terminar de testar!
// Ele é apenas para verificação rápida e não deve ficar exposto em produção.
