<?php
define('APP_ROOT', dirname(__DIR__));

// Cargar constantes y configuración de la base de datos
require_once APP_ROOT . '/config/constantes.php';
require_once APP_ROOT . '/config/BD_Connect.php';

// Autoload de clases dentro de la carpeta `core`
spl_autoload_register(function ($class) {
    $classPath = APP_ROOT . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($classPath)) {
        require_once $classPath;
    }
});

// Iniciar sesión si no ha sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
