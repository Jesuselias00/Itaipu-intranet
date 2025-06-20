<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $router = new \Bramus\Router\Router();
    echo 'Router cargado correctamente';
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
