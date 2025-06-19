<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Usuario.php';

// Probar inicio de sesión con admin.new@itaipu.com
$email = 'admin.new@itaipu.com';
$password = 'admin';

$usuarioModel = new Usuario();
$usuario = $usuarioModel->findByEmail($email);

if ($usuario) {
    echo "Usuario encontrado: {$usuario['nombre_completo']}\n";
    echo "Hash almacenado: {$usuario['password_hash']}\n";
    
    $valid = password_verify($password, $usuario['password_hash']);
    echo "Validación: " . ($valid ? "CORRECTA" : "INCORRECTA") . "\n";
} else {
    echo "Usuario no encontrado.\n";
}
?>
