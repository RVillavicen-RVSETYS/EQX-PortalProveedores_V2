<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class UsoCfdiDistinto_Mdl
{
    private $db;
    private static $debug = 0;
    private $tabla = 'conf_provUsoCfdiDistinto';
    private $logFile = 'usoCfdiDistinto.log';

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase UsoCfdiDistinto_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    public function obtenerUsoCfdi()
    {
        try {
            $sql = "SELECT
                        ucd.id AS 'IdConf',
                        ucd.idProveedor AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        ucd.usoCFDI AS 'Codigo',
                        uc.descripcion AS 'UsoCfdi',
                        ucd.estatus AS 'Estatus'
                    FROM
                        conf_provUsoCfdiDistinto ucd
                        INNER JOIN proveedores prov ON ucd.idProveedor = prov.id
                        INNER JOIN sat_catUsoCFDI uc ON ucd.usoCFDI = uc.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores con Uso De CFDI Distinto.'];
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php -> Error Al Obtener Proveedores con Uso De CFDI Distinto: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores con Uso De CFDI Distinto. Notifica a tu administrador'];
        }
    }

    public function obtenerCatUsoCfdi()
    {
        try {
            $sql = "SELECT
                        uc.id AS 'IdUsoCfdi',
                        uc.descripcion AS 'UsoCfdi' 
                    FROM
                        sat_catUsoCFDI uc 
                    WHERE
                        uc.estatus = 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                return ['success' => false, 'message' => 'No Se Obtuvieron El Catalogo De Usos De CFDI.'];
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php -> Error Al Obtener El Catalogo De Usos De CFDI: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener El Catalogo De Usos De CFDI. Notifica a tu administrador'];
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
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php -> Error Al Obtener Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores. Notifica a tu administrador'];
        }
    }

    public function registraUsoCfdiDistinto($campos)
    {
        $camposValidos = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlQuery' => 'idProveedor = :idProveedor'],
            'usoCFDI' => ['tipoDato' => 'INT', 'sqlQuery' => 'usoCFDI = :usoCFDI'],
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ],
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
                if (isset($camposValidos[$campo]['permitidos'])) {
                    if (!in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                        throw new \Exception($camposValidos[$campo]['mensajeError']);
                    }
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
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php -> Se Agregó El Proveedor: " . $campos['idProveedor'] . ", IdUserReg: " . ($campos['idUserReg'] ?? 0) . PHP_EOL, 3, $logFile);

                return ['success' => true, 'message' => 'Proveedor agregado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }

    public function actualizarUsoCfdiDistinto($campos, $filtros)
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
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php -> Se " . $estatus . " El Proveedor: " . $idProveedor . ", IdUserReg: " . ($_SESSION['EQXident'] ?? 0) . PHP_EOL, 3, $logFile);

                return ['success' => true, 'message' => 'Estatus cambiado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'No se actualizó ningún registro.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/UsoCfdiDistinto_Mdl.php ->Error al actualizar: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }
}
