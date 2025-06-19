<?php
// test_login.php - Un script para probar el inicio de sesión directamente

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Usuario.php';

// Función para probar el inicio de sesión
function testLogin($email, $password) {
    echo "Probando inicio de sesión para: $email\n";
    
    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->findByEmail($email);
    
    if (!$usuario) {
        echo "ERROR: Usuario no encontrado\n";
        return false;
    }
    
    echo "Usuario encontrado: {$usuario['nombre_completo']}\n";
    
    $password_valid = password_verify($password, $usuario['password_hash']);
    echo "Validación de contraseña: " . ($password_valid ? "CORRECTA" : "INCORRECTA") . "\n";
    echo "Hash almacenado: " . substr($usuario['password_hash'], 0, 20) . "...\n";
    
    return $password_valid;
}

// Probar con las credenciales proporcionadas
echo "=== Test 1: admin123@itaipu.com / password123 ===\n";
testLogin('admin123@itaipu.com', 'password123');

echo "\n=== Test 2: admin@itaipu.com / admin123 ===\n";
testLogin('admin@itaipu.com', 'admin123');

echo "\n=== Test 3: admin2@itaipu.com / admin123 ===\n";
testLogin('admin2@itaipu.com', 'admin123');

// Crear un nuevo usuario con password explícito para prueba
echo "\n=== Creando un nuevo usuario de prueba ===\n";
$db = new Database();
$conn = $db->connect();

$email = 'test.user@itaipu.com';
$plain_password = 'test123';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Email: $email\n";
echo "Contraseña: $plain_password\n";
echo "Hash generado: " . substr($hashed_password, 0, 20) . "...\n";

// Eliminar usuario de prueba si ya existe
$stmt = $conn->prepare("DELETE FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

// Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (email, password_hash, nombre_completo, rol, activo) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$email, $hashed_password, 'Usuario de Prueba', 'usuario', 1]);

echo "Usuario de prueba creado con ID: " . $conn->lastInsertId() . "\n";

// Probar inicio de sesión con el usuario recién creado
echo "\n=== Test 4: test.user@itaipu.com / test123 ===\n";
testLogin('test.user@itaipu.com', 'test123');
