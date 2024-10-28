<?php
// Habilitar la visualización de errores (para desarrollo; asegúrate de deshabilitar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoload de clases
require_once '../app/init.php'; // Cargamos la configuración inicial y el autoload

// Iniciar la aplicación
$app = new Core\App(); // Aquí se instancia el enrutador principal, que maneja la navegación y las solicitudes
