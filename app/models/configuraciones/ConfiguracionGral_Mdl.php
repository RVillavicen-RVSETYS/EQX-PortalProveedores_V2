<?php

namespace App\Models\Configuraciones;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class ConfiguracionGral_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase ConfiguracionGral_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    // Método flexible tipo listar, similar a listarComplementosPago()
    public function listarConfiguracionGral($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0;
        $filtrosDisponibles = [
            'idEmpresa' => ['tipoDato' => 'INT', 'sqlFiltro' => 'idEmpresa = :idEmpresa'],
            'estatus'   => ['tipoDato' => 'INT', 'sqlFiltro' => 'estatus = :estatus'],
            // Agrega más filtros si es necesario
        ];

        $filtrosSQL = '';
        $params = [];

        try {
            if (!is_int($cantMaxRes)) {
                throw new \Exception('El valor de $cantMaxRes debe ser un entero.');
            }
            $limiteResult = ($cantMaxRes == 0) ? '' : 'LIMIT ' . $cantMaxRes;

            if (!in_array($orden, ['DESC', 'ASC'])) {
                throw new \Exception('El orden debe ser DESC o ASC.');
            } else {
                $orden = strtoupper($orden);
            }

            foreach ($filtros as $nombreFiltro => $valorFiltro) {
                if (isset($filtrosDisponibles[$nombreFiltro]) && $valorFiltro !== null) {
                    $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                    $params[':' . $nombreFiltro] = $valorFiltro;
                }
            }

            $filtrosSQL = ltrim($filtrosSQL, ' AND');
            $where = $filtrosSQL ? "WHERE $filtrosSQL" : "";

            $sql = "SELECT cg.*, emp.razonSocial AS Empresa, emp.rfc AS RFC FROM configuracionGral cg INNER JOIN empresas emp ON cg.idEmpresa = emp.id $where ORDER BY cg.id $orden $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Listar Configuración General.');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cantRes = $stmt->rowCount();

            if ($result) {
                return ['success' => true, 'cantRes' => $cantRes, 'data' => $result];
            } else {
                return ['success' => false, 'cantRes' => 0, 'data' => [], 'message' => 'No se encontró configuración general.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::listarConfiguracionGral(): " . $e->getMessage() . "\n", 3, "error.log");
            return ['success' => false, 'cantRes' => 0, 'data' => [], 'message' => 'Error al listar la configuración general.'];
        }
    }


    public function registrarConfiguracionGral($data)
    {
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

    public function actualizarConfiguracionGral($data)
    {
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

    public function verificarReglaExistente($idEmpresa, $tipoMoneda)
    {
        try {
            $sql = "SELECT id, tipoRegla, estatus 
                    FROM conf_diferenciaMontos 
                    WHERE idEmpresa = :idEmpresa 
                      AND tipoMoneda = :tipoMoneda 
                      AND estatus = '1' 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
            $stmt->bindParam(':tipoMoneda', $tipoMoneda, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $resultado];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function desactivarReglasPorEmpresaMoneda($idEmpresa, $tipoMoneda)
    {
        try {
            $sql = "UPDATE conf_diferenciaMontos 
                    SET estatus = '0' 
                    WHERE idEmpresa = :idEmpresa 
                      AND tipoMoneda = :tipoMoneda 
                      AND estatus = '1'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
            $stmt->bindParam(':tipoMoneda', $tipoMoneda, PDO::PARAM_STR);
            $stmt->execute();
            $filas = $stmt->rowCount();
            return ['success' => true, 'message' => "Se desactivaron $filas reglas anteriores."];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::desactivarReglasPorEmpresaMoneda(): " . $e->getMessage() . "\n", 3, LOG_FILE_BD);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function registrarDiferenciaMontos(array $campos)
    {
        $camposValidos = [
            'idEmpresa'     => ['tipo' => PDO::PARAM_INT],
            'tipoMoneda'    => ['tipo' => PDO::PARAM_STR], // CHAR(5)
            'tipoRegla'     => ['tipo' => PDO::PARAM_INT],
            'montoSup'      => ['tipo' => PDO::PARAM_STR],
            'montoInf'      => ['tipo' => PDO::PARAM_STR],
            'porcentajeSup' => ['tipo' => PDO::PARAM_STR],
            'porcentajeInf' => ['tipo' => PDO::PARAM_STR],
            'estatus'       => ['tipo' => PDO::PARAM_STR], // CHAR(1)
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

    public function actualizarDiferenciaMontos(array $campos, array $filtros)
    {
        $camposValidos = [
            'estatus' => [
                'tipoDato' => 'STRING', // CHAR(1)
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ]
        ];

        $filtrosValidos = [
            'id' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'id = :id'
            ],
            'idEmpresa' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'idEmpresa = :idEmpresa'
            ],
            'tipoMoneda' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'tipoMoneda = :tipoMoneda'
            ]
        ];

        try {
            if (empty($campos) || empty($filtros)) {
                throw new \Exception('Campos y filtros no pueden estar vacíos.');
            }

            $setParts = [];
            $whereParts = [];
            $params = [];

            // Procesar campos a actualizar
            foreach ($campos as $campo => $valor) {
                if (!isset($camposValidos[$campo])) {
                    continue;
                }
                if (
                    isset($camposValidos[$campo]['permitidos']) &&
                    !in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)
                ) {
                    throw new \Exception($camposValidos[$campo]['mensajeError']);
                }
                $setParts[] = $camposValidos[$campo]['sqlQuery'];
                $params[":$campo"] = $valor;
            }

            // Procesar filtros (WHERE)
            foreach ($filtros as $filtro => $valor) {
                if (!isset($filtrosValidos[$filtro])) {
                    continue;
                }
                $whereParts[] = $filtrosValidos[$filtro]['sqlQuery'];
                $params[":$filtro"] = $valor;
            }

            if (empty($setParts)) {
                throw new \Exception('No se especificaron campos válidos para actualizar.');
            }
            if (empty($whereParts)) {
                throw new \Exception('No se especificaron filtros válidos para el WHERE.');
            }

            $sql = "UPDATE conf_diferenciaMontos 
                    SET " . implode(', ', $setParts) . " 
                    WHERE " . implode(' AND ', $whereParts);

            $stmt = $this->db->prepare($sql);

            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                $tipoDato = $camposValidos[$clave]['tipoDato'] ?? $filtrosValidos[$clave]['tipoDato'] ?? 'STRING';
                $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Regla actualizada correctamente.'];
            } else {
                return ['success' => false, 'message' => 'No se realizaron cambios (registro no encontrado o datos iguales).'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ConfiguracionGral_Mdl::actualizarDiferenciaMontos(): " . $e->getMessage() . "\n", 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }
}
