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

    /**
     * Verifica si ya existe una regla activa para una empresa y moneda específica.
     */
    public function verificarReglaExistente(int $idEmpresa, int $idMoneda) {
        try {
            $sql = "SELECT tipoRegla, estatus 
                    FROM conf_diferenciaMontos 
                    WHERE idEmpresa = :idEmpresa AND idMoneda = :idMoneda AND estatus = 1 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
            $stmt->bindParam(':idMoneda', $idMoneda, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $resultado];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    
    // Registra una nueva configuración de diferencia de montos usando Whitelisting.
    
    public function registrarDiferenciaMontos(array $campos) {
        $camposValidos = [
            'idEmpresa'     => ['tipo' => PDO::PARAM_INT],
            'idMoneda'      => ['tipo' => PDO::PARAM_INT],
            'tipoRegla'     => ['tipo' => PDO::PARAM_INT],
            'montoSup'      => ['tipo' => PDO::PARAM_STR],
            'montoInf'      => ['tipo' => PDO::PARAM_STR],
            'porcentajeSup' => ['tipo' => PDO::PARAM_STR],
            'porcentajeInf' => ['tipo' => PDO::PARAM_STR],
            'estatus'       => ['tipo' => PDO::PARAM_INT],
            'idUserReg'     => ['tipo' => PDO::PARAM_INT]
        ];

        try {
            if (empty($campos)) {
                throw new \Exception("Los datos a registrar no pueden estar vacíos.");
            }

            $columnas = [];
            $placeholders = [];
            $params = [];

            foreach ($campos as $nombre => $valor) {
                if (array_key_exists($nombre, $camposValidos)) {
                    $columnas[] = $nombre;
                    $placeholders[] = ":$nombre";
                    $params[$nombre] = [
                        'valor' => $valor,
                        'tipo'  => $camposValidos[$nombre]['tipo']
                    ];
                }
            }

            // Añadir fecha de registro automática
            $columnas[] = "fechaReg";
            $placeholders[] = "NOW()";

            $sql = "INSERT INTO conf_diferenciaMontos (" . implode(', ', $columnas) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $nombre => $data) {
                $stmt->bindValue(":$nombre", $data['valor'], $data['tipo']);
            }

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Configuración registrada correctamente.'];
            } else {
                return ['success' => false, 'message' => 'No se pudo insertar el registro.'];
            }

        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::registrarDiferenciaMontos(): " . $e->getMessage() . "\n", 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al registrar: ' . $e->getMessage()];
        }
    }
}

?>