<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class PermitirPueSiempre_Mdl
{
    private $db;
    private static $debug = 0;
    private $tabla = 'conf_provPermitirPueSiempre';
    private $logFile = 'permitirPueSiempre.log';

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase PermitirPueSiempre_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    /**
     * Lista de permisos PUE siempre (No. proveedor, fecha expiración, estatus).
     */
    public function obtenerLista()
    {
        try {
            $sql = "SELECT
                        pp.id AS 'IdConf',
                        pp.idProveedor AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        pp.fechaExpiracion AS 'FechaExpiracion',
                        pp.fechaCancela AS 'FechaCancela',
                        pp.idUserCancela AS 'IdUserCancela',
                        pp.motivo AS 'Motivo',
                        pp.estatus AS 'Estatus'
                    FROM
                        {$this->tabla} pp
                        INNER JOIN proveedores prov ON pp.idProveedor = prov.id
                    ORDER BY pp.fechaExpiracion DESC, pp.idProveedor ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            }
            return ['success' => false, 'message' => 'No hay registros de Permiso PUE siempre.'];
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] " . __FILE__ . " -> Error al obtener lista: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al obtener la lista. Notifica a tu administrador.'];
        }
    }

    /**
     * Proveedores que NO tienen un permiso PUE activo (estatus=1 y fechaExpiracion >= hoy).
     * Así solo se pueden elegir para darles un nuevo permiso.
     */
    public function getProveedores()
    {
        try {
            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor'
                    FROM
                        proveedores prov
                    WHERE
                        prov.id NOT IN (
                            SELECT idProveedor
                            FROM {$this->tabla}
                            WHERE estatus = 1 AND fechaExpiracion >= CURDATE()
                        )
                    ORDER BY prov.id ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            }
            return ['success' => false, 'message' => 'No hay proveedores disponibles (todos tienen permiso activo o no hay proveedores).'];
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] " . __FILE__ . " -> Error al obtener proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al obtener proveedores. Notifica a tu administrador.'];
        }
    }

    /**
     * Indica si el proveedor ya tiene un permiso PUE activo (estatus=1 y no vencido).
     */
    public function tienePermisoActivo($idProveedor)
    {
        try {
            $sql = "SELECT 1 FROM {$this->tabla}
                    WHERE idProveedor = :idProveedor AND estatus = 1 AND fechaExpiracion >= CURDATE()
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            return (bool) $stmt->fetch();
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] " . __FILE__ . " -> Error en tienePermisoActivo: " . $e->getMessage(), 3, LOG_FILE_BD);
            return true; // por seguridad, asumir que sí tiene para no duplicar
        }
    }

    /**
     * Registrar nuevo permiso PUE. Falla si el proveedor ya tiene permiso activo.
     */
    public function registraPermitirPueSiempre($campos)
    {
        $camposValidos = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlQuery' => 'idProveedor = :idProveedor'],
            'fechaExpiracion' => ['tipoDato' => 'STRING', 'sqlQuery' => 'fechaExpiracion = :fechaExpiracion'],
            'motivo' => ['tipoDato' => 'STRING', 'sqlQuery' => 'motivo = :motivo'],
            'estatus' => ['tipoDato' => 'INT', 'sqlQuery' => 'estatus = :estatus', 'permitidos' => ['0', '1'], 'mensajeError' => 'Estatus inválido.'],
            'idUserReg' => ['tipoDato' => 'INT', 'sqlQuery' => 'idUserReg = :idUserReg']
        ];

        try {
            if (!is_array($campos) || empty($campos)) {
                throw new \Exception('Los campos deben ser un arreglo no vacío.');
            }

            $idProveedor = (int) ($campos['idProveedor'] ?? 0);
            if ($idProveedor <= 0) {
                throw new \Exception('Proveedor inválido.');
            }
            if ($this->tienePermisoActivo($idProveedor)) {
                return [
                    'success' => false,
                    'message' => 'Este proveedor ya tiene un permiso PUE activo. No se puede agregar otro hasta que expire o se desactive.'
                ];
            }

            $params = [];
            $columnParts = [];
            $valueParts = [];
            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    continue;
                }
                if (isset($camposValidos[$campo]['permitidos']) && !in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                    throw new \Exception($camposValidos[$campo]['mensajeError'] ?? "Valor inválido para $campo.");
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
                $tipoDato = $camposValidos[$clave]['tipoDato'] ?? 'STRING';
                $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas >= 1) {
                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/' . $this->logFile;
                $motivo = $campos['motivo'] ?? '';
                error_log("[$timestamp] " . __FILE__ . " -> Permiso PUE agregado. IdProveedor: " . $idProveedor . ", motivo: '$motivo', IdUserReg: " . ($campos['idUserReg'] ?? 0) . PHP_EOL, 3, $logFile);
                return ['success' => true, 'message' => 'Permiso PUE siempre agregado correctamente.'];
            }
            return ['success' => false, 'message' => 'No se pudo registrar el permiso.'];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] " . __FILE__ . " -> Error al registrar: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Actualizar estatus (activar/desactivar) por id.
     * Al desactivar (estatus=0) se guarda fechaCancelacion = hoy; al reactivar se limpia.
     */
    public function actualizarPermitirPueSiempre($campos, $filtros)
    {
        $camposValidos = [
            'estatus' => ['tipoDato' => 'INT', 'sqlQuery' => 'estatus = :estatus', 'permitidos' => ['0', '1'], 'mensajeError' => 'Estatus inválido.']
        ];
        $filtrosValidos = ['id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id']];

        try {
            if (!is_array($campos) || !is_array($filtros) || empty($campos) || empty($filtros)) {
                throw new \Exception('Campos y filtros deben ser arreglos no vacíos.');
            }

            $params = [];
            $setParts = [];
            $whereParts = [];
            foreach ($campos as $campo => $valor) {
                if (!isset($camposValidos[$campo])) {
                    continue;
                }
                if (isset($camposValidos[$campo]['permitidos']) && !in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                    throw new \Exception($camposValidos[$campo]['mensajeError']);
                }
                $setParts[] = $camposValidos[$campo]['sqlQuery'];
                $params[":$campo"] = $valor;
            }
            foreach ($filtros as $filtro => $valor) {
                if (!isset($filtrosValidos[$filtro])) {
                    continue;
                }
                $whereParts[] = $filtrosValidos[$filtro]['sqlQuery'];
                $params[":$filtro"] = $valor;
            }

            // Auditoría cancelación: al cancelar (estatus=0) guardar cuándo, quién y motivo; al reactivar limpiar
            $setParts[] = 'fechaCancela = IF(:estatusAudit1 = 0, NOW(), NULL)';
            $setParts[] = 'idUserCancela = IF(:estatusAudit2 = 0, :idUserCancela, NULL)';
            $setParts[] = 'motivoCancela = IF(:estatusAudit3 = 0, :motivoCancela, NULL)';
            $idUserCancela = $_SESSION['EQXident'] ?? null;
            $params[':estatusAudit1'] = $campos['estatus'];
            $params[':estatusAudit2'] = $campos['estatus'];
            $params[':estatusAudit3'] = $campos['estatus'];
            $params[':idUserCancela'] = $idUserCancela;
            $params[':motivoCancela'] = $campos['motivoCancela'] ?? null;

            $sql = "UPDATE {$this->tabla} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                $tipoDato = $camposValidos[$clave]['tipoDato'] ?? $filtrosValidos[$clave]['tipoDato'] ?? 'INT';
                if (strpos($clave, 'estatusAudit') === 0) {
                    $stmt->bindValue($param, $value, PDO::PARAM_INT);
                } elseif ($clave === 'idUserCancela') {
                    $stmt->bindValue($param, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                } elseif ($clave === 'motivoCancela') {
                    $stmt->bindValue($param, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                } else {
                    $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
            }
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas >= 1) {
                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/' . $this->logFile;
                $estatus = ($campos['estatus'] == 1) ? 'Activó' : 'Desactivó';
                error_log("[$timestamp] " . __FILE__ . " -> Se $estatus permiso PUE id: " . ($filtros['id'] ?? '') . ", IdUserReg: " . ($_SESSION['EQXident'] ?? 0) . PHP_EOL, 3, $logFile);
                return ['success' => true, 'message' => 'Estatus cambiado correctamente.'];
            }
            return ['success' => false, 'message' => 'No se actualizó ningún registro.'];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] " . __FILE__ . " -> Error al actualizar: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
