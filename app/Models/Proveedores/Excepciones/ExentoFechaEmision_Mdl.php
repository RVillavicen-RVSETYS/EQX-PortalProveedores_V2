<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class ExentoFechaEmision_Mdl
{
    private $db;
    private static $debug = 0;
    private $tabla = 'conf_provExentoFechaEmision';
    private $logFile = 'exentoTiempoEmision.log';

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase ExentoFechaEmision_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    public function obtenerFechaEmision()
    {
        try {
            $sql = "SELECT
                        efe.id AS 'IdExento',
                        efe.idProveedor AS 'IdProveedor',
                        efe.nombre AS 'Proveedor',
                        efe.estatus AS 'Estatus' 
                    FROM
                        conf_provExentoFechaEmision efe
                    ORDER BY efe.idProveedor ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores Exentos Fecha De Emision.'];
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoFechaEmision_Mdl.php -> Error Al Obtener Proveedores Exentos Fecha De Emision: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores Exentos Fecha De Emision. Notifica a tu administrador'];
        }
    }

    public function getProveedores()
    {
        try {
            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor' 
                    FROM
                        proveedores prov
                        LEFT JOIN {$this->tabla} confProv ON prov.id = confProv.idProveedor 
                    WHERE
                        confProv.idProveedor IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores.'];
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoFechaEmision_Mdl.php -> Error Al Obtener Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores. Notifica a tu administrador'];
        }
    }

    public function registraExentoFechaEmision($campos)
    {
        try {
            if (!is_array($campos) || count($campos) == 0) {
                throw new \Exception('Los campos deben ser un arreglo no vacío.');
            }

            $sql = "INSERT INTO {$this->tabla} (idProveedor, nombre, estatus, idUserReg, fechaReg) 
                    SELECT prov.id, prov.nombre, :estatus, :idUserReg, NOW() 
                    FROM proveedores prov 
                    WHERE prov.id = :idProveedor";

            $stmt = $this->db->prepare($sql);
            $estatus = $campos['estatus'] ?? 1;
            $idUserReg = $campos['idUserReg'] ?? $_SESSION['EQXident'] ?? 0;
            $idProveedor = $campos['idProveedor'];

            $stmt->bindValue(':estatus', $estatus, PDO::PARAM_INT);
            $stmt->bindValue(':idUserReg', $idUserReg, PDO::PARAM_INT);
            $stmt->bindValue(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas == 1) {
                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/' . $this->logFile;
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoFechaEmision_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . ", IdUserReg: " . $idUserReg . PHP_EOL, 3, $logFile);

                return ['success' => true, 'message' => 'Proveedor agregado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoFechaEmision_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }

    public function actualizarExentoFechaEmision($campos, $filtros)
    {
        $camposValidos = [
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ]
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id']
        ];

        try {
            if (!is_array($campos) || !is_array($filtros) || count($campos) == 0 || count($filtros) == 0) {
                throw new \Exception('Los campos y filtros deben ser arreglos no vacíos.');
            }

            $params = [];
            $setParts = [];
            $whereParts = [];

            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    throw new \Exception("Campo no válido: $campo");
                }
                if (isset($camposValidos[$campo]['permitidos'])) {
                    if (!in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                        throw new \Exception($camposValidos[$campo]['mensajeError']);
                    }
                }
                $setParts[] = $camposValidos[$campo]['sqlQuery'];
                $params[":$campo"] = $valor;
            }

            foreach ($filtros as $filtro => $valor) {
                if (!array_key_exists($filtro, $filtrosValidos)) {
                    throw new \Exception("Filtro no válido: $filtro");
                }
                $whereParts[] = $filtrosValidos[$filtro]['sqlQuery'];
                $params[":$filtro"] = $valor;
            }

            $sql = "UPDATE {$this->tabla} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);

            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                $tipoDato = $camposValidos[$clave]['tipoDato'] ?? $filtrosValidos[$clave]['tipoDato'];
                $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas >= 1) {
                // Obtener idProveedor para el log
                $sqlSelect = "SELECT idProveedor FROM {$this->tabla} WHERE id = :id LIMIT 1";
                $stmtSelect = $this->db->prepare($sqlSelect);
                $stmtSelect->bindValue(':id', $filtros['id'], PDO::PARAM_INT);
                $stmtSelect->execute();
                $resultSelect = $stmtSelect->fetch(PDO::FETCH_ASSOC);
                $idProveedor = $resultSelect['idProveedor'] ?? 0;

                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/' . $this->logFile;
                $estatus = ($campos['estatus'] == 1) ? 'Activó' : 'Desactivó';
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoFechaEmision_Mdl.php -> Se " . $estatus . " El Proveedor: " . $idProveedor . ", IdUserReg: " . ($_SESSION['EQXident'] ?? 0) . PHP_EOL, 3, $logFile);

                return ['success' => true, 'message' => 'Estatus cambiado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'No se actualizó ningún registro.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoFechaEmision_Mdl.php ->Error al actualizar: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }
}
