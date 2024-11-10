<?php
// Habilitar el modo de depuración
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
}

// Cargar constantes y configuración de la base de datos
require_once '../../config/constantes.php';

$debug = 0; // Cambia a 1 para activar la depuración

// Autoload de Composer para las dependencias (asegúrate de que Composer ha generado el archivo autoload.php en la carpeta vendor)
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
}
if ($debug == 1) {
    echo "<h2>Ya estamos dentro de app\init_admin.</h2>";
}

// Función de autoload personalizada para cargar clases automáticamente
spl_autoload_register(function ($class){
    global $debug;
    if ($debug == 1) {
        echo "<br>Dentro de spl_autoload_register: ";
    }

    // Definimos los prefijos y sus respectivos directorios base
    $prefixes = [
        'Core\\' => '../../core/',
        'App\\' => '../../app/',
        'Config\\' => '../../config/'
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        // Verifica si la clase usa el prefijo de espacio de nombres
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue; // Si no coincide el prefijo, pasamos al siguiente
        }

        // Obtiene el nombre relativo de la clase
        $relative_class = substr($class, $len);
        if ($debug == 1) {
            echo "<br><b>Nombre relativo de la clase: </b>" . $relative_class;
        }

        // Construye la ruta del archivo basado en el nombre de la clase
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if ($debug == 1) {
            echo "<br><b>Ruta del archivo basado en el nombre de la clase: </b>" . $file;
        }

        // Incluye el archivo si existe
        if (file_exists($file)) {
            if ($debug == 1) {
                echo "<br>Si existió el Archivo: " . $file;
            }
            require $file;
            return; // Termina aquí si encuentra y carga el archivo
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/init.php -> Archivo no encontrado: " . $file. PHP_EOL, 3, '../'.LOG_FILE);
            if ($debug == 1) {
                echo "<br>No existe el Archivo: " . $file;
            }
        }
    }
});

// Iniciar sesión si no ha sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    if ($debug == 1) {
        echo "<br>Session no iniciada se iniciara.<br>";
    }
    session_start();
}
