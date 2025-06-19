<?php
// Crear un hash para la contraseña 'admin'
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Contraseña: $password\n";
echo "Hash: $hash\n";
?>
