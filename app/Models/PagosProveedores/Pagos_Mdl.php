<?php

namespace App\Models\PagosProveedores;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class Pagos_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 1 para activar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Pagos_Mdl.</h2>";
        }

        $this->db = new BD_Connect();
    }

    public function listarPagosRealizados($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'idProveedor1' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.idProveedor = :idProveedor1'],
            'idProveedor2' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.idProveedor = :idProveedor2'],
            'entreFechasPago1' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(pc.fechaPago BETWEEN :fechaInicial1 AND :fechaFinal1)'],
            'entreFechasPago2' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(pc.fechaPago BETWEEN :fechaInicial2 AND :fechaFinal2)'],
        ];

        $filtrosSQL = '';
        $filtrosSQL2 = '';
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
                    if ($nombreFiltro == 'entreFechasPago1') {
                        list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                        if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                            throw new \Exception('Las fechas proporcionadas no son válidas.');
                        }
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':fechaInicial1'] = $fechaInicial;
                        $params[':fechaFinal1'] = $fechaFinal;
                    } elseif ($nombreFiltro == 'entreFechasPago2') {
                        list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                        if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                            throw new \Exception('Las fechas proporcionadas no son válidas.');
                        }
                        $filtrosSQL2 .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':fechaInicial2'] = $fechaInicial;
                        $params[':fechaFinal2'] = $fechaFinal;
                    } elseif ($nombreFiltro == 'idProveedor1') {
                        if (!empty($valorFiltro)) {
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        }
                    } elseif ($nombreFiltro == 'idProveedor2') {
                        if (!empty($valorFiltro)) {
                            $filtrosSQL2 .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        }
                    } else {
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':' . $nombreFiltro] = $valorFiltro;
                    }
                }
            }

            if (empty($filtrosSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido.');
            }
            if (empty($filtrosSQL2)) {
                throw new \Exception('No se encontró ningún parámetro válido.');
            }
            $filtrosSQL = ltrim($filtrosSQL, ' AND');
            $filtrosSQL2 = ltrim($filtrosSQL2, ' AND');

            if (self::$debug) {
                echo '<br><br>Parametros: ';
                var_dump($params);
                echo '<br><br>';
            }

            $sql = "SELECT
                        Pagos.IdCompra AS 'Acuse',
                        CONCAT( cf.serie, cf.folio ) AS 'Serie',
                        cf.razonSocialEm AS 'Emisor',
                        Pagos.OrdenCompra AS 'OC',
                        GROUP_CONCAT( Pagos.Recepcion ) AS 'HES',
                        GROUP_CONCAT( Pagos.FormaPago ) AS 'FormaPago',
                        SUM( Pagos.MontoPagado ) AS 'MontoPagado' 
                    FROM
                        (
                        SELECT
                            dc.idCompra AS 'IdCompra',
                            pc.OC AS 'OrdenCompra',
                            pc.HES AS 'Recepcion',
                            pc.montoPagado AS 'MontoPagado',
                            fp.nombre AS 'FormaPago'
                        FROM
                            pagos_compras pc
                            LEFT JOIN detcompras dc ON pc.OC = dc.ordenCompra
                            INNER JOIN sat_catFormaPago fp ON pc.formaPago = fp.id
                            LEFT JOIN compras com ON dc.idCompra = com.id
                        WHERE
                            $filtrosSQL
                        GROUP BY
                            pc.HES 
                        ) Pagos
                        INNER JOIN cfdi_facturas cf ON Pagos.IdCompra = cf.idCompra 
                    WHERE
                        Pagos.IdCompra > 0
                    GROUP BY
                        Pagos.OrdenCompra UNION
                    SELECT
                        Pagos.IdCompra AS 'Acuse',
                        '' AS 'Serie',
                        '' AS 'Emisor',
                        Pagos.OrdenCompra AS 'OC',
                        Pagos.Recepcion AS 'HES',
                        GROUP_CONCAT( Pagos.FormaPago ) AS 'FormaPago',
                        SUM( Pagos.MontoPagado ) AS 'MontoPagado' 
                    FROM
                        (
                        SELECT
                            dc.idCompra AS 'IdCompra',
                            pc.OC AS 'OrdenCompra',
                            pc.HES AS 'Recepcion',
                            pc.montoPagado AS 'MontoPagado',
                            fp.nombre AS 'FormaPago' 
                        FROM
                            pagos_compras pc
                            LEFT JOIN detcompras dc ON pc.OC = dc.ordenCompra
                            INNER JOIN sat_catFormaPago fp ON pc.formaPago = fp.id
                            LEFT JOIN compras com ON dc.idCompra = com.id
                        WHERE
                            $filtrosSQL2
                            AND ISNULL( dc.idCompra ) 
                        GROUP BY
                            pc.HES 
                        ) Pagos
                        LEFT JOIN cfdi_facturas cf ON Pagos.IdCompra = cf.idCompra 
                    GROUP BY
                        Pagos.OrdenCompra,
                        Pagos.Recepcion
                    ORDER BY Acuse $orden
                    $limiteResult";
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista De Pagos Realizados: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $pagosresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantPagos = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($pagosresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantPagos, 'data' => $pagosresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/PagosProveedores/Pagos_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Pagos Realizados: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar los Pagos Realizados, Notifica a tu administrador.'];
        }
    }

    public function dataPagosDesdeFacturas($filtros = [], INT $cantMaxRes = 0)
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'fc.uuid IN (:uuids)'],
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'cp.idProveedor = :idProveedor'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(cp.fechaReg BETWEEN :fechaInicial AND :fechaFinal)']
        ];

        $filtrosSQL = '';
        $params = [];

        try {
            if (!is_int($cantMaxRes)) {
                throw new \Exception('El valor de $cantMaxRes debe ser un entero.');
            }
            $limiteResult = ($cantMaxRes == 0) ? '' : 'LIMIT ' . $cantMaxRes;

            foreach ($filtros as $nombreFiltro => $valorFiltro) {
                if (isset($filtrosDisponibles[$nombreFiltro]) && $valorFiltro !== null) {
                    switch ($nombreFiltro) {
                        case 'entreFechas':
                            list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                            if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                                throw new \Exception('Las fechas proporcionadas no son válidas.');
                            }
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':fechaInicial'] = $fechaInicial;
                            $params[':fechaFinal'] = $fechaFinal;
                            break;

                        case 'uuids':
                            // Validar que el valor sea una cadena de UUIDs separados por comas
                            $uuids = explode(',', $valorFiltro);
                            $uuids = array_map('trim', $uuids); // Limpiar espacios en blanco
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':uuids'] = implode(',', $uuids); // Convertir a cadena separada por comas
                            break;

                        default:
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':' . $nombreFiltro] = $valorFiltro;
                            break;
                    }
                }
            }

            if (empty($filtrosSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido.');
            }
            $filtrosSQL = ltrim($filtrosSQL, ' AND');

            if (self::$debug) {
                echo '<br><br>Parametros: ';
                var_dump($params);
                echo '<br><br>';
            }

            $sql = "SELECT * 
                    FROM pagos_compras pgc
                    INNER JOIN (
                        SELECT dcp.idCompra, dcp.noRecepcion, fc.uuid, SUM(dcp.monto) AS subtotalHES
                        FROM cfdi_facturas fc
                        INNER JOIN compras cp ON fc.idCompra = cp.id
                        INNER JOIN detcompras dcp ON cp.id = dcp.idCompra
                        WHERE $filtrosSQL
                        GROUP BY dcp.idCompra, dcp.noRecepcion
                    ) dt ON pgc.HES = dt.noRecepcion
                    ORDER BY dt.uuid, dt.idCompra, dt.noRecepcion DESC
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista de Pagos por UUID: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $pagosresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantPagos = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($pagosresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantPagos, 'data' => $pagosresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Facturas por UUID: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Facturas por UUID: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar Facturas por UUID, Notifica a tu administrador.'];
        }
    }
    
}
