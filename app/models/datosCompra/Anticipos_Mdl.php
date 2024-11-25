<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_ConnectHES; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';

class Anticipos_Mdl
{
    private $dbHES;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Anticipos_Mdl.</h2>";
        }
        
        $this->dbHES = new BD_ConnectHES(); // Instancia de la conexión a la base de datos
    }

    public function verificaAnticipoDeOrdenCompra($ordenCompra)
    {
        try {
            $cantAnticipos = 0;

            if ($cantAnticipos > 0) {
                return ['success' => true, 'data' => $cantAnticipos]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No hay ninguna Anticipo para esta OC.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No hay Anticipo para esta OC.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/datosCompra/Anticipos_Mdl.php ->Error buscar Anticipos para la OC: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error al contar Anticipos: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al buscar Anticipos, Notifica a tu administrador.'];
        }
    }
}