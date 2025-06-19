<?php
// Script de prueba para conexión y consulta de la tabla funcionarios
$config = require __DIR__ . '/../app/config/database.php';

try {
    $dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset={$config['DB_CHARSET']}";
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<b>Conexión exitosa a la base de datos.</b><br>";

    $stmt = $pdo->query('SELECT * FROM funcionarios LIMIT 10');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo "<pre>" . print_r($rows, true) . "</pre>";
    } else {
        echo "No hay datos en la tabla funcionarios.";
    }
} catch (Exception $e) {
    echo "<b>Error de conexión o consulta:</b> " . $e->getMessage();
}
