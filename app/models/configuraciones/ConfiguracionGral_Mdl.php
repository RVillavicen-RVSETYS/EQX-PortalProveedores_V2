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

    public function obtenerConfiguracionPorEmpresa($idEmpresa){
        try {
            $sql = "SELECT *
                    FROM configuracionGral
                    WHERE idEmpresa = :idEmpresa";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, ['idEmpresa' => $idEmpresa], 'Obtener Configuración por Empresa.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
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
                return ['success' => false, 'message' => 'No se encontró configuración para esta empresa.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::obtenerConfiguracionPorEmpresa(): " . $e->getMessage() . "\n", 3, "error.log");
            if (self::$debug) {
                echo "Error al obtener la configuración: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Error al obtener la configuración.'];
        }
    }

   public function registrarConfiguracionGral($data){
        try {
            $sql = "INSERT INTO configuracionGral (idEmpresa, maxComplementosPendientes, diasPago)
                    VALUES (:idEmpresa, :maxComplementosPendientes, :diasPago)";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $data, 'Registrar Configuración General.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $data['idEmpresa'], PDO::PARAM_INT);
            $stmt->bindParam(':maxComplementosPendientes', $data['maxComplementosPendientes'], PDO::PARAM_INT);
            $stmt->bindParam(':diasPago', $data['diasPago'], PDO::PARAM_STR);
            $stmt->execute();

            if (self::$debug) {
                echo '<br>Configuración general registrada con éxito.';
                echo '<br><br>';
            }
            return ['success' => true, 'message' => 'Configuración general registrada con éxito.'];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::registrarConfiguracionGral(): " . $e->getMessage() . "\n", 3, "error.log");
            if (self::$debug) {
                echo "Error al registrar la configuración general: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Error al registrar la configuración general, Notifica a tu Administrador.'];
        }
    }

    public function actualizarConfiguracionGral($data){
        try {
            $sql = "UPDATE configuracionGral 
                    SET maxComplementosPendientes = :maxComplementosPendientes, 
                        diasPago = :diasPago
                    WHERE idEmpresa = :idEmpresa";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $data, 'Actualizar Configuración General.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $data['idEmpresa'], PDO::PARAM_INT);
            $stmt->bindParam(':maxComplementosPendientes', $data['maxComplementosPendientes'], PDO::PARAM_INT);
            $stmt->bindParam(':diasPago', $data['diasPago'], PDO::PARAM_STR);
            $stmt->execute();

            if (self::$debug) {
                echo '<br>Configuración general actualizada con éxito.';
                echo '<br><br>';
            }
            return ['success' => true, 'message' => 'Configuración general actualizada con éxito.'];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::actualizarConfiguracionGral(): " . $e->getMessage() . "\n", 3, "error.log");
            if (self::$debug) {
                echo "Error al actualizar la configuración general: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Error al actualizar la configuración general, Notifica a tu Administrador.'];
        }
    }


}
