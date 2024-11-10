<?php
// Cargar el archivo de inicialización de la aplicación (ajustar la ruta según sea necesario)
require_once '../../app/init_admin.php'; // Ajustar a `core/app.php` si corresponde

// Espacio de nombres y uso del controlador correspondiente
use App\Controllers\LoginAdmin;

// Verifica si el usuario ya está autenticado como administrador
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /Administrador/Inicio');
    exit();
}

try {
    // Crear instancia del controlador de login para administradores
    $controller = new LoginAdmin();

    // Llamar al método del controlador que muestra la vista de inicio de sesión
    $controller->index();

} catch (Exception $e) {
    // Manejo de errores: se registra en el log y se muestra mensaje genérico
    $timestamp = date("Y-m-d H:i:s");
    error_log("[$timestamp] Error en AdminFilos: " . $e->getMessage(), 3, LOG_FILE);
    echo "Error al cargar la página de inicio de sesión. Contacte al administrador del sistema.";
}
