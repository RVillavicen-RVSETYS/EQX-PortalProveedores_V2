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

    public function verificaAnticipo($Anticipo, $noProveedor)
    {
        try {
            $sql = "SELECT COUNT(id_OC) AS cantAnticipos
              FROM vw_ext_PortalProveedores_HESporPagar
              WHERE idProveedor = :noProveedor AND OrdenCompra = :anticipo";

            if (self::$debug) {
                $params = [
                    ':anticipo' => $Anticipo,
                    ':noProveedor' => $noProveedor
                ];
                $this->dbHES->imprimirConsulta($sql, $params, 'Busca Anticipo.');
            }
            $stmt = $this->dbHES->prepare($sql);
            $stmt->bindParam(':anticipo', $Anticipo, PDO::PARAM_STR);
            $stmt->bindParam(':noProveedor', $noProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $cantAnticipos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($cantAnticipos);
                echo '<br><br>';
            }

            if ($cantAnticipos["cantAnticipos"] > 0) {
                return ['success' => true, 'data' => $cantAnticipos]; 
            } else {
                if (self::$debug) {
                    echo "No hay ningun Anticipo.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No existe ese Codigo de Anticipo.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/datosCompra/Anticipos_Mdl.php ->Error contar HES de la OC: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error al buscar Anticipo: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas con tu Anticipo, Notifica a tu administrador.'];
        }
    }
}