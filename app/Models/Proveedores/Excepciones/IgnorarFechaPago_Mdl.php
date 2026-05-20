<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

/**
 * Activa/desactiva proveedores.descontarPromocionesAplicables (ajuste montos vs políticas NC en carga de factura).
 */
class IgnorarFechaPago_Mdl
{
    private $db;
    private static $debug = 0;
    private $tabla = 'conf_provIgnoraFechaPago';
    private $logFile = 'ignorarFechaPago.log';

    public function __construct()
    {
        if (self::$debug) {
            echo '<h2>IgnorarFechaPago_Mdl</h2>';
        }
        $this->db = new BD_Connect();
    }

    /**
     * Proveedores que aún no tienen el flag activo (para el select izquierdo)
     */
    public function getProveedoresDisponibles()
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

            if ($dataResul && count($dataResul) > 0) {
                return ['success' => true, 'data' => $dataResul];
            }

            return ['success' => false, 'message' => 'No hay proveedores disponibles para agregar (todos tienen el ajuste activo o no hay registros).'];
        } catch (\PDOException $e) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] IgnorarFechaPago_Mdl::getProveedoresDisponibles: " . $e->getMessage(), 3, LOG_FILE_BD);

            return ['success' => false, 'message' => 'Error al obtener proveedores. Notifica a tu administrador.'];
        }
    }

    // Metodo para agregar el proveedor a la tabla de la regla
    public function registraIgnoraFechaPago($campos)
    {
        $camposValidos = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlQuery' => 'idProveedor = :idProveedor'],
            'motivo' => ['tipoDato' => 'STRING', 'sqlQuery' => 'motivo = :motivo'],
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
            $invalidCampos = [];

            // Usar SELECT para obtener nombre del proveedor
            $sql = "INSERT INTO {$this->tabla} (idProveedor, motivo, estatus, idUserReg, fechaReg) 
                    SELECT prov.id, :motivo, :estatus, :idUserReg, NOW()
                    FROM proveedores prov
                    WHERE prov.id = :idProveedor";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, [], 'Registrar Proveedor Exento Año Fiscal');
            }

            $stmt = $this->db->prepare($sql);
            $estatus = $campos['estatus'] ?? 1;
            $idUserReg = $campos['idUserReg'] ?? $_SESSION['EQXident'] ?? 0;
            $idProveedor = $campos['idProveedor'];
            $motivo = $campos['motivo'];


            $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
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
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoAnoFisc_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . ", IdUserReg: " . $idUserReg . PHP_EOL, 3, $logFile);

                return ['success' => true, 'message' => 'Proveedor agregado correctamente.', 'filasAfectadas' => $filasAfectadas];
            } else {
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.', 'filasAfectadas' => $filasAfectadas];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/ExentoAnoFisc_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'filasAfectadas' => 0];
        }
    }

    private function writeLog(string $accion, int $idProveedor, string $motivo, $idUserReg): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $year = date('Y');
        if (!defined('LOG_SYSTEM')) {
            return;
        }
        $logDir = LOG_SYSTEM . 'excepciones/' . $year;
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $path = $logDir . '/' . $this->logFile;
        $line = "[$timestamp] $accion idProveedor=$idProveedor motivo=" . str_replace(["\r", "\n"], ' ', $motivo) . ' idUser=' . $idUserReg . PHP_EOL;
        error_log($line, 3, $path);
    }
}
