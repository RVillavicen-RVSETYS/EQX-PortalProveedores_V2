<?php

namespace App\Models\Configuraciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class RecepcionCFDIs_Mdl
{
    private $db;
    private static $debug = 0; 

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase RecepcionCFDIs_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function configuracionBaseRecepcionCFDI(int $idEmpresa, string $tipoCFDI, string $versionCFDI)
    {
        try {
            // Validaciones iniciales
            if (empty($idEmpresa) || empty($tipoCFDI) || empty($versionCFDI)) {
                return [
                    'success' => false,
                    'message' => 'Todos los parámetros son obligatorios (idEmpresa, tipoCFDI, versionCFDI).'
                ];
            }

            // Construcción de la consulta
            $sql = "
                SELECT * 
                FROM configuracionCFDIs 
                WHERE idEmpresa = :idEmpresa 
                  AND tipoCFDI = :tipoCFDI 
                  AND versionCFDI = :versionCFDI
            ";

            $params = [
                ':idEmpresa' => $idEmpresa,
                ':tipoCFDI' => $tipoCFDI,
                ':versionCFDI' => $versionCFDI
            ];

            // Debug antes de ejecutar la consulta
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Consulta para configuración base recepción CFDI');
            }

            // Preparar y ejecutar la consulta
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
            $stmt->bindParam(':tipoCFDI', $tipoCFDI, PDO::PARAM_STR);
            $stmt->bindParam(':versionCFDI', $versionCFDI, PDO::PARAM_STR);
            $stmt->execute();

            // Obtener resultados
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug del resultado
            if (self::$debug) {
                echo '<br><strong>Resultado de la consulta:</strong><br>';
                var_dump($result);
                echo '<br><br>';
            }

            // Verificar si hay datos
            if (!$result) {
                return [
                    'success' => false,
                    'message' => "No se encontraron configuraciones para la Empresa: $idEmpresa y el tipo de CFDI: $tipoCFDI."
                ];
            }

            return ['success' => true, 'message' => 'Configuración obtenida correctamente.', 'data' => $result];

        } catch (\Exception $e) {
            // Registrar error
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/configuracion/Configuracion_Mdl.php -> Error en configuracionBaseRecepcionCFDI: " . $e->getMessage(), 3, LOG_FILE_BD);

            // Debug del error
            if (self::$debug) {
                echo '<br><strong>Error encontrado:</strong><br>';
                echo $e->getMessage();
                echo '<br>';
            }

            return [
                'success' => false,
                'message' => 'Problemas al obtener la configuración CFDI. Notifica a tu administrador.'
            ];
        }
    }


    public function diferenciaMontoXMoneda(int $idProveedor, int $empresa, string $moneda)
    {
        try {
            // Validar si el proveedor está bloqueado
            $sqlBloqueo = "SELECT id 
                       FROM conf_provBloqDiferencias 
                       WHERE idProveedor = :idProveedor";
            if (self::$debug) {
                $params = [':idProveedor' => $idProveedor];
                $this->db->imprimirConsulta($sqlBloqueo, $params, 'Validar proveedor bloqueado para diferencias');
            }
            $stmt = $this->db->prepare($sqlBloqueo);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $proveedorBloqueado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($proveedorBloqueado) {
                if (self::$debug) {
                    echo "Proveedor bloqueado: $idProveedor. Configuración restrictiva aplicada.<br>";
                }
                // Proveedor bloqueado, configuración restrictiva
                return [
                    'success' => true,
                    'data' => [
                        'tipoRegla' => 1, // Solo Aplica por Monto
                        'montoSuperior' => 0.00,
                        'montoInferior' => 0.00,
                        'porcentajeSuperior' => 0.00,
                        'porcentajeInferior' => 0.00,
                        'bloqParaDiferencias' => true
                    ]
                ];
            }

            // Obtener las reglas de diferencias por empresa y moneda
            $sqlReglas = "SELECT tipoRegla, montoSup, montoInf, porcentajeSup, porcentajeInf
                      FROM conf_diferenciaMontos
                      WHERE idEmpresa = :empresa 
                        AND tipoMoneda = :moneda 
                        AND estatus = 1";
            if (self::$debug) {
                $params = [':empresa' => $empresa, ':moneda' => $moneda];
                $this->db->imprimirConsulta($sqlReglas, $params, 'Consulta de reglas de diferencias por empresa y moneda');
            }
            $stmt = $this->db->prepare($sqlReglas);
            $stmt->bindParam(':empresa', $empresa, PDO::PARAM_INT);
            $stmt->bindParam(':moneda', $moneda, PDO::PARAM_STR);
            $stmt->execute();
            $reglas = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reglas) {
                if (self::$debug) {
                    echo "No hay reglas configuradas para la empresa $empresa y la moneda $moneda. Configuración restrictiva aplicada.<br>";
                }
                // No hay reglas configuradas, configuración restrictiva
                return [
                    'success' => true,
                    'data' => [
                        'tipoRegla' => 1, // Solo Aplica por Monto
                        'montoSuperior' => 0.00,
                        'montoInferior' => 0.00,
                        'porcentajeSuperior' => 0.00,
                        'porcentajeInferior' => 0.00,
                        'bloqParaDiferencias' => false
                    ]
                ];
            }

            // Preparar el resultado basado en el tipo de regla
            if (self::$debug) {
                echo "Reglas encontradas: ";
                var_dump($reglas);
                echo "<br>";
            }
            $resultado = [
                'success' => true,
                'data' => [
                    'tipoRegla' => (int) $reglas['tipoRegla'],
                    'montoSuperior' => (float) ($reglas['montoSup'] ?? 0),
                    'montoInferior' => (float) ($reglas['montoInf'] ?? 0),
                    'porcentajeSuperior' => (float) ($reglas['porcentajeSup'] ?? 0),
                    'porcentajeInferior' => (float) ($reglas['porcentajeInf'] ?? 0),
                    'bloqParaDiferencias' => false // No está bloqueado
                ]
            ];

            return $resultado;
        } catch (\Exception $e) {
            // Manejo de errores
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/datosCompra/Configuraciones_Mdl.php ->Error en diferenciaMontoXMoneda: " . $e->getMessage(), 3, LOG_FILE);
            if (self::$debug) {
                echo "Error en diferenciaMontoXMoneda: " . $e->getMessage();
            }

            return [
                'success' => false,
                'message' => 'Ocurrió un error al consultar las diferencias de monto. Notifica al administrador.'
            ];
        }
    }
}
