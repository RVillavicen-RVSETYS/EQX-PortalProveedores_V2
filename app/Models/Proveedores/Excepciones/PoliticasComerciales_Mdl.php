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
class PoliticasComerciales_Mdl
{
    private $db;
    private static $debug = 0;
    private $logFile = 'politicasComerciales.log';

    public function __construct()
    {
        if (self::$debug) {
            echo '<h2>PoliticasComerciales_Mdl</h2>';
        }
        $this->db = new BD_Connect();
    }

    /**
     * Proveedores con descontarPromocionesAplicables = 1
     */
    public function obtenerProveedoresConPoliticaActiva()
    {
        try {
            $sql = "SELECT
                        prov.id AS IdProveedor,
                        prov.nombre AS Proveedor,
                        prov.razonSocial AS RazonSocial
                    FROM proveedores prov
                    WHERE COALESCE(prov.descontarPromocionesAplicables, 0) = 1
                    ORDER BY prov.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul && count($dataResul) > 0) {
                return ['success' => true, 'data' => $dataResul];
            }

            return [
                'success' => false,
                'message' => 'No hay proveedores con ajuste de políticas comerciales activo.',
            ];
        } catch (\PDOException $e) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] PoliticasComerciales_Mdl::obtenerProveedoresConPoliticaActiva: " . $e->getMessage(), 3, LOG_FILE_BD);

            return ['success' => false, 'message' => 'Error al obtener la lista. Notifica a tu administrador.'];
        }
    }

    /**
     * Proveedores que aún no tienen el flag activo (para el select izquierdo)
     */
    public function getProveedoresDisponibles()
    {
        try {
            $sql = "SELECT
                        prov.id AS IdProveedor,
                        prov.nombre AS Proveedor
                    FROM proveedores prov
                    WHERE COALESCE(prov.descontarPromocionesAplicables, 0) = 0
                    ORDER BY prov.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataResul && count($dataResul) > 0) {
                return ['success' => true, 'data' => $dataResul];
            }

            return ['success' => false, 'message' => 'No hay proveedores disponibles para agregar (todos tienen el ajuste activo o no hay registros).'];
        } catch (\PDOException $e) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] PoliticasComerciales_Mdl::getProveedoresDisponibles: " . $e->getMessage(), 3, LOG_FILE_BD);

            return ['success' => false, 'message' => 'Error al obtener proveedores. Notifica a tu administrador.'];
        }
    }

    public function activarDescontarPromociones(int $idProveedor, string $motivo, $idUserReg): array
    {
        $motivo = trim($motivo);
        if ($idProveedor <= 0) {
            return ['success' => false, 'message' => 'Selecciona un proveedor válido.'];
        }
        if ($motivo === '') {
            return ['success' => false, 'message' => 'El motivo es obligatorio.'];
        }

        try {
            $sql = "UPDATE proveedores
                    SET descontarPromocionesAplicables = 1,
                        userUpdate = :userUpdate,
                        fechaUpdate = NOW()
                    WHERE id = :idProveedor
                      AND COALESCE(descontarPromocionesAplicables, 0) = 0";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindValue(':userUpdate', (string) $idUserReg, PDO::PARAM_STR);
            $stmt->execute();
            $filas = $stmt->rowCount();

            if ($filas === 1) {
                $this->writeLog('ACTIVAR', $idProveedor, $motivo, $idUserReg);

                return ['success' => true, 'message' => 'Proveedor agregado correctamente.'];
            }

            $sqlYa = 'SELECT COALESCE(descontarPromocionesAplicables, 0) AS v FROM proveedores WHERE id = :id LIMIT 1';
            $st = $this->db->prepare($sqlYa);
            $st->bindValue(':id', $idProveedor, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && (int) $row['v'] === 1) {
                return ['success' => false, 'message' => 'Este proveedor ya tiene activo el ajuste de políticas comerciales.'];
            }

            return ['success' => false, 'message' => 'No se pudo actualizar el proveedor. Verifica que exista.'];
        } catch (\PDOException $e) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] PoliticasComerciales_Mdl::activarDescontarPromociones: " . $e->getMessage(), 3, LOG_FILE_BD);

            return ['success' => false, 'message' => 'Error al guardar. Notifica a tu administrador.'];
        }
    }

    public function desactivarDescontarPromociones(int $idProveedor, $idUserReg): array
    {
        if ($idProveedor <= 0) {
            return ['success' => false, 'message' => 'Identificador de proveedor no válido.'];
        }

        try {
            $sql = "UPDATE proveedores
                    SET descontarPromocionesAplicables = 0,
                        userUpdate = :userUpdate,
                        fechaUpdate = NOW()
                    WHERE id = :idProveedor
                      AND COALESCE(descontarPromocionesAplicables, 0) = 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindValue(':userUpdate', (string) $idUserReg, PDO::PARAM_STR);
            $stmt->execute();
            $filas = $stmt->rowCount();

            if ($filas === 1) {
                $this->writeLog('DESACTIVAR', $idProveedor, '(eliminado de la lista)', $idUserReg);

                return ['success' => true, 'message' => 'Proveedor eliminado de la lista correctamente.'];
            }

            return ['success' => false, 'message' => 'No se encontró el proveedor con el ajuste activo o ya estaba inactivo.'];
        } catch (\PDOException $e) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] PoliticasComerciales_Mdl::desactivarDescontarPromociones: " . $e->getMessage(), 3, LOG_FILE_BD);

            return ['success' => false, 'message' => 'Error al eliminar. Notifica a tu administrador.'];
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
