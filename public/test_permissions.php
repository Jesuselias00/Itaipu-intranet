<?php
echo "<h1>Test de Permisos del Servidor</h1>";
echo "<p>Este script verifica si PHP puede escribir archivos en el directorio 'app/core'.</p>";

$log_dir = __DIR__ . '/../app/core/';
$log_file = $log_dir . 'permission_test.log';
$log_message = "Test de escritura ejecutado a las " . date('Y-m-d H:i:s') . "\n";

echo "<p>Intentando escribir en la siguiente ruta: <code>" . htmlspecialchars($log_file) . "</code></p>";

// Verificar si el directorio es escribible
if (is_writable($log_dir)) {
    echo "<p style='color:green;'>El directorio <code>" . htmlspecialchars($log_dir) . "</code> SÍ tiene permisos de escritura.</p>";
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> El directorio <code>" . htmlspecialchars($log_dir) . "</code> NO tiene permisos de escritura.</p>";
    echo "<p>Por favor, verifica los permisos de la carpeta 'app/core' en tu sistema de archivos.</p>";
}

// Intentar escribir en el archivo
if (file_put_contents($log_file, $log_message, FILE_APPEND)) {
    echo "<p style='color:green;'>¡Éxito! Se ha escrito correctamente en el archivo de log.</p>";
    echo "<p>Puedes verificar el contenido del archivo <code>permission_test.log</code>.</p>";
} else {
    echo "<p style='color:red;'><strong>FALLO:</strong> No se pudo escribir en el archivo de log.</p>";
    echo "<p>Esto confirma que hay un problema de permisos que impide a PHP crear o modificar archivos.</p>";
}

echo "<h2>Próximos Pasos:</h2>";
echo "<ol>";
echo "<li>Si ves mensajes de error, necesitas ajustar los permisos de la carpeta 'htdocs/Itaipu-intranet'.</li>";
echo "<li>En Windows, haz clic derecho en la carpeta -> Propiedades -> Seguridad y asegúrate de que el usuario con el que corre Apache (normalmente 'SYSTEM' o 'Users') tenga permisos de 'Modificar' o 'Control total'.</li>";
echo "<li>Una vez corregidos los permisos, el `router_debug.log` debería empezar a llenarse y podremos solucionar el error 400.</li>";
echo "</ol>";

?>
