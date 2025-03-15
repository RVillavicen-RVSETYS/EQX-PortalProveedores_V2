<?php

namespace app\models\configuraciones;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class ConfiguracionGral_Mdl{
    private $db;
    private static $debug = 0;

    public function __construct(){
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase ConfiguracionGral_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    public function obtenerConfiguracionGral(){
        try {
            $sql = "SELECT *
                    FROM configuracionGral
                    ORDER BY id DESC
                    LIMIT 1";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Configuración General.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $configuracionGral = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($configuracionGral);
                echo '<br><br>';
            }
            if ($configuracionGral) {
                return ['success' => true, 'data' => $configuracionGral];
            } else {
                return ['success' => false, 'message' => 'No se encontró la configuración general.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::obtenerConfiguracionGral(): " . $e->getMessage() . "\n", 3, "error.log");
            if (self::$debug) {
                echo "Error al obtener la configuración general: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Error al obtener la configuración general, Notifica a tu Administrador.'];
        }
    }


}

?>