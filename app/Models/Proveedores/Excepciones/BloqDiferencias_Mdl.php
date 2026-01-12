<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class BloqDiferencias_Mdl
{
    private $db;
    private static $debug = 0;
    private $tabla = 'conf_provBloqDiferencias';
    private $logFile = 'bloqueoDeDiferencias.log';

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase BloqDiferencias_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    public function obtenerBloqueoDiferencias()
    {
        try {
            $sql = "SELECT
                        bf.id AS 'IdBloq',
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        prov.razonSocial AS 'RazonSocial',
                        bf.motivo AS 'Motivo'
                    FROM
                        conf_provBloqDiferencias bf
                        INNER JOIN proveedores prov ON bf.idProveedor = prov.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores con Bloqueo De Diferencias.'];
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqDiferencias_Mdl.php -> Error Al Obtener Proveedores con Bloqueo De Diferencias: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores con Bloqueo De Diferencias. Notifica a tu administrador'];
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
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqDiferencias_Mdl.php -> Error Al Obtener Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores. Notifica a tu administrador'];
        }
    }

    public function registraBloqDiferencias($campos)
    {
        $camposValidos = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlQuery' => 'idProveedor = :idProveedor'],
            'motivo' => ['tipoDato' => 'STRING', 'sqlQuery' => 'motivo = :motivo'],
            'idUserReg' => ['tipoDato' => 'INT', 'sqlQuery' => 'idUserReg = :idUserReg']
        ];

        try {
            if (!is_array($campos) || count($campos) == 0) {
                throw new \Exception('Los campos deben ser un arreglo no vacío.');
            }

            $params = [];
            $columnParts = [];
            $valueParts = [];

            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    throw new \Exception("Campo no válido: $campo");
                }
                $columnParts[] = $campo;
                $valueParts[] = ":{$campo}";
                $params[":{$campo}"] = $valor;
            }

            $columnParts[] = 'fechaReg';
            $valueParts[] = 'NOW()';

            $sql = "INSERT INTO {$this->tabla} (" . implode(', ', $columnParts) . ") VALUES (" . implode(', ', $valueParts) . ")";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                $tipoDato = $camposValidos[$clave]['tipoDato'];
                $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

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
                $motivo = $campos['motivo'] ?? '';
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqDiferencias_Mdl.php -> Se Agregó El Proveedor: " . $campos['idProveedor'] . " Con Motivo: '$motivo', IdUserReg: " . ($campos['idUserReg'] ?? 0) . PHP_EOL, 3, $logFile);

                return ['success' => true, 'message' => 'Proveedor agregado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqDiferencias_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }

    public function actualizarBloqDiferencias($campos, $filtros)
    {
        // Esta tabla no tiene estatus, solo se puede actualizar motivo si es necesario
        // Por ahora solo mantenemos la estructura por si se necesita en el futuro
        $camposValidos = [];
        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id']
        ];

        try {
            if (!is_array($campos) || !is_array($filtros) || count($filtros) == 0) {
                throw new \Exception('Los filtros deben ser un arreglo no vacío.');
            }

            // Si no hay campos para actualizar, retornar error
            if (count($campos) == 0) {
                throw new \Exception('No hay campos para actualizar en esta tabla.');
            }

            return ['success' => false, 'message' => 'Esta tabla no permite actualizaciones mediante este método.'];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqDiferencias_Mdl.php ->Error al actualizar: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }

    public function eliminarReg($identificador)
    {
        try {
            $sql = "DELETE FROM {$this->tabla} WHERE id = :identificador";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas == 1) {
                return ['success' => true, 'message' => 'Proveedor eliminado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'Error Al Eliminar Proveedor.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqDiferencias_Mdl.php ->Error Al Eliminar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }
}
