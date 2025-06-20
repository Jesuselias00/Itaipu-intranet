<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verificarToken() {
    $todosOsHeaders = getallheaders();
    $authHeader = $todosOsHeaders['Authorization'] ?? null;

    if (!$authHeader) {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'erro', 'mensagem' => 'Token de autorização não encontrado.']);
        exit();
    }

    list($tipo, $token) = explode(' ', $authHeader);

    if ($tipo !== 'Bearer' || !$token) {
        http_response_code(401);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Formato do token inválido.']);
        exit();
    }

    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Token inválido ou expirado: ' . $e->getMessage()]);
        exit();
    }
}
