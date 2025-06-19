<?php
// Permitir solicitudes desde cualquier origen (para desenvolvimento)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Solo permitir solicitudes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// Incluir los archivos necesarios
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Usuario.php';

// Crear archivo de log para depuración
$log_file = __DIR__ . '/../app/core/login_debug.log';
file_put_contents($log_file, date('c') . " | LOGIN ATTEMPT | Iniciando proceso de login\n", FILE_APPEND);

// Leer datos del cuerpo de la solicitud
$raw_input = file_get_contents('php://input');
file_put_contents($log_file, date('c') . " | RAW INPUT | " . $raw_input . "\n", FILE_APPEND);

$data = json_decode($raw_input, true);
file_put_contents($log_file, date('c') . " | DECODED DATA | " . json_encode($data) . "\n", FILE_APPEND);

// Verificar que se proporcionen email y contraseña
if (!isset($data['email'], $data['senha'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Email e senha são obrigatórios'
    ]);
    file_put_contents($log_file, date('c') . " | ERROR | Datos incompletos\n", FILE_APPEND);
    exit;
}

// Crear instancia del modelo de usuario
$usuarioModel = new Usuario();

// Buscar usuario por email
$usuario = $usuarioModel->findByEmail($data['email']);
file_put_contents($log_file, date('c') . " | USUARIO | " . ($usuario ? "Usuario encontrado: " . $usuario['email'] : "Usuario no encontrado") . "\n", FILE_APPEND);

// Verificar credenciales
if (!$usuario) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Credenciais inválidas'
    ]);
    file_put_contents($log_file, date('c') . " | ERROR | Usuario no encontrado\n", FILE_APPEND);
    exit;
}

// Verificar contraseña
$password_valid = password_verify($data['senha'], $usuario['password_hash']);
file_put_contents($log_file, date('c') . " | PASSWORD | Validación: " . ($password_valid ? "CORRECTA" : "INCORRECTA") . "\n", FILE_APPEND);
file_put_contents($log_file, date('c') . " | PASSWORD | Hash almacenado: " . substr($usuario['password_hash'], 0, 20) . "...\n", FILE_APPEND);
file_put_contents($log_file, date('c') . " | PASSWORD | Password enviada: " . $data['senha'] . "\n", FILE_APPEND);

if (!$password_valid) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Credenciais inválidas'
    ]);
    file_put_contents($log_file, date('c') . " | ERROR | Contraseña incorrecta\n", FILE_APPEND);
    exit;
}

// Verificar si el usuario está activo
if (!$usuario['activo']) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Conta de usuário desativada'
    ]);
    exit;
}

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = $usuario['id_usuario'];
$_SESSION['user_email'] = $usuario['email'];
$_SESSION['user_role'] = $usuario['rol'];

// Devolver respuesta exitosa
echo json_encode([
    'status' => 'success',
    'message' => 'Login bem-sucedido',
    'usuario' => [
        'id' => $usuario['id_usuario'],
        'email' => $usuario['email'],
        'nome' => $usuario['nombre_completo'],
        'rol' => $usuario['rol']
    ]
]);
