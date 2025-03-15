<?php

namespace App\Models\Configuraciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class CierrePortal_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase CierrePortal_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function verificaCierreDePortal($noProveedor)
    {
        try {
            $sql = "SELECT
                        DATE_FORMAT( fechaInicio, '%d-%m-%Y' ) AS fechaInicio,
                        DATE_FORMAT( fechaFin, '%d-%m-%Y' ) AS fechaFin,
                        mensajeCierre,
                        mensajeCierreIng
                    FROM conf_cierreAnio 
                    WHERE estatus = '1' AND NOW() BETWEEN fechaInicio AND fechaFin
                    ORDER BY id DESC LIMIT 1";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Busca Bloqueo de Facturas.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $bloqueaPortal = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($bloqueaPortal);
                echo '<br><br>';
            }
            if ($bloqueaPortal) {
                $proveedorExcento = $this->proveedorExcentoBloqueo($noProveedor);
                if ($proveedorExcento['success']) {
                    return ['success' => false, 'message' => 'Proveedor Excento de Bloqueo.'];
                } else {
                    return ['success' => true, 'data' => $bloqueaPortal]; // Retornar datos si tiene permisos a algun Area
                }
                
            } else {
                if (self::$debug) {
                    echo "No se encontro ningun Bloqueo para este momento.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No hay Bloqueo.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/configuraciones/CierrePortal_Mdl.php ->Error al listar fechas de Bloqueo: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error al listar fechas de Bloqueo: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return null; // Retorna null en caso de error
        }
    }

    public function proveedorExcentoBloqueo($noProveedor)
    {
        try {
            $sql = "SELECT *
                    FROM conf_provFactSiempre 
                    WHERE estatus = '1'
                    ORDER BY id DESC LIMIT 1";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Busca Bloqueo de Facturas.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $proveedorExcento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($proveedorExcento);
                echo '<br><br>';
            }
            if ($proveedorExcento) {
                return ['success' => true, 'data' => $proveedorExcento]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No se encontro Excepción para el Proveedor.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No se encontro Excepción para el Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Configuraciones/CierrePortal_Mdl.php ->Error al buscar Proveedor Excento: " . $e->getMessage(), 3, LOG_FILE); // Manejo del error
            if (self::$debug) {
                echo "Error al buscar Proveedor Excento: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return null; // Retorna null en caso de error
        }
    }

}
