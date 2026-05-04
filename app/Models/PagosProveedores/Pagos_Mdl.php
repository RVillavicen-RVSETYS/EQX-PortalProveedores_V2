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

    public function listarComplementosPago($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.idProveedor = :idProveedor'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(com.fechaReg BETWEEN :fechaInicial AND :fechaFinal)'],
            'complementosPendientes' => ['tipoDato' => 'STRING', 'sqlFiltro' => ''],
            'estatus' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.estatus = :estatus'],
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
                        case 'complementosPendientes':
                            if (!in_array($valorFiltro, ['true', 'false'])) {
                                throw new \Exception('El valor de insolutoPendiente debe ser true o false.');
                            }
                            if ($valorFiltro == 'true') {
                                $filtrosSQL .= ' AND com.totalPagos > com.totalComplementos';
                            } else {
                                $filtrosSQL .= ' AND com.totalPagos <= com.totalComplementos';
                            }
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

            $sql = "SELECT
                        prov.id AS NoProveedor,
                        prov.razonSocial AS Proveedor,
                        prov.correo AS Correo,
                        com.id AS Acuse,
                        cf.uuid AS UUID,
                        cf.serie AS Serie,
                        cf.folio AS Folio,
                        (COALESCE(com.insolutoPendiente, cf.monto) - IFNULL(com.totalNotasCredito, 0)) AS Insoluto,
                        com.totalComplementos AS Complemento,
                        com.totalPagos AS Pagos
                    FROM
                        compras com
                        INNER JOIN proveedores prov ON com.idProveedor = prov.id
                        INNER JOIN cfdi_facturas cf ON com.id = cf.idCompra 
                    WHERE
                        $filtrosSQL";
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

    public function listarPagosInsolutos($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0;
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.idProveedor = :idProveedor'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(com.fechaReg BETWEEN :fechaInicial AND :fechaFinal)'],
            'insoluto' => ['tipoDato' => 'STRING', 'sqlFiltro' => ''],
            'estatus' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.estatus = :estatus'],
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
                        case 'insoluto':
                            if (!in_array($valorFiltro, ['true', 'false', true, false], true)) {
                                throw new \Exception('El valor de insolutoPendiente debe ser true o false.');
                            }
                            if ($valorFiltro === 'true' || $valorFiltro === true) {
                                $filtrosSQL .= ' AND ((com.insolutoPendiente - IFNULL(com.totalNotasCredito, 0)) > 0.01 OR ISNULL(com.insolutoPendiente))';
                            } else {
                                $filtrosSQL .= ' AND (com.insolutoPendiente - IFNULL(com.totalNotasCredito, 0)) <= 0.01';
                            }
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

            $sql = "SELECT
                        prov.id AS NoProveedor,
                        prov.razonSocial AS Proveedor,
                        prov.correo AS Correo,
                        com.id AS Acuse,
                        cf.uuid AS UUID,
                        cf.serie AS Serie,
                        cf.folio AS Folio,
                        (COALESCE(com.insolutoPendiente, cf.monto) - IFNULL(com.totalNotasCredito, 0)) AS Insoluto 
                    FROM
                        compras com
                        INNER JOIN proveedores prov ON com.idProveedor = prov.id
                        INNER JOIN cfdi_facturas cf ON com.id = cf.idCompra 
                    WHERE
                        $filtrosSQL";
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

    public function listarPagosRealizados($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 1;
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
                            $params[':idProveedor1'] = (int)$valorFiltro;
                        }
                    } elseif ($nombreFiltro == 'idProveedor2') {
                        if (!empty($valorFiltro)) {
                            $filtrosSQL2 .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':idProveedor2'] = (int)$valorFiltro;
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
                        MAX( Pagos.IdCompra ) AS 'Acuse',
                        GROUP_CONCAT( DISTINCT CONCAT( cf.serie, cf.folio ) SEPARATOR ', ' ) AS 'Serie',
                        MAX( cf.razonSocialEm ) AS 'Emisor',
                        MAX( Pagos.OrdenCompra ) AS 'OC',
                        MAX( Pagos.Recepcion ) AS 'HES',
                        MAX( Pagos.FormaPago ) AS 'FormaPago',
                        MAX( Pagos.MontoPagado ) AS 'MontoPagado',
                        MAX( Pagos.TipoMoneda ) AS 'TipoMoneda',
                        MAX( cf.idCatMetodoPago ) AS 'MetodoPago',
                        MAX( cf.idCatFormaPago ) AS 'FormaPagoCfdi'
                    FROM
                        (
                        SELECT
                            pc.id AS 'IdPago',
                            MAX( dc.idCompra ) AS 'IdCompra',
                            pc.OC AS 'OrdenCompra',
                            pc.HES AS 'Recepcion',
                            pc.montoPagado AS 'MontoPagado',
                            pc.monedaTipoCambio AS 'TipoMoneda',
                            fp.nombre AS 'FormaPago'
                        FROM
                            pagos_compras pc
                            LEFT JOIN detcompras dc ON pc.OC = dc.ordenCompra
                            INNER JOIN sat_catFormaPago fp ON pc.formaPago = fp.id
                            LEFT JOIN compras com ON dc.idCompra = com.id
                        WHERE
                            $filtrosSQL
                        GROUP BY
                            pc.id 
                        ) Pagos
                        INNER JOIN cfdi_facturas cf ON Pagos.IdCompra = cf.idCompra 
                    WHERE
                        Pagos.IdCompra > 0
                    GROUP BY
                        Pagos.IdPago UNION
                    SELECT
                        MAX( Pagos.IdCompra ) AS 'Acuse',
                        '' AS 'Serie',
                        '' AS 'Emisor',
                        MAX( Pagos.OrdenCompra ) AS 'OC',
                        MAX( Pagos.Recepcion ) AS 'HES',
                        MAX( Pagos.FormaPago ) AS 'FormaPago',
                        MAX( Pagos.MontoPagado ) AS 'MontoPagado',
                        MAX( Pagos.TipoMoneda ) AS 'TipoMoneda',
                        MAX( cf.idCatMetodoPago ) AS 'MetodoPago',
                        MAX( cf.idCatFormaPago ) AS 'FormaPagoCfdi'
                    FROM
                        (
                        SELECT
                            pc.id AS 'IdPago',
                            MAX( dc.idCompra ) AS 'IdCompra',
                            pc.OC AS 'OrdenCompra',
                            pc.HES AS 'Recepcion',
                            pc.montoPagado AS 'MontoPagado',
                            pc.monedaTipoCambio AS 'TipoMoneda',
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
                            pc.id 
                        ) Pagos
                        LEFT JOIN cfdi_facturas cf ON Pagos.IdCompra = cf.idCompra 
                    GROUP BY
                        Pagos.IdPago
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
        self::$debug = 0; // Activado para pruebas (Complemento de Pago)
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => ''], // Se manejará dinámicamente
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
                            // Manejar múltiples UUIDs con placeholders dinámicos
                            $uuids = explode(',', $valorFiltro);
                            $uuids = array_map('trim', $uuids); // Limpiar espacios en blanco
                            $uuids = array_filter($uuids); // Eliminar valores vacíos

                            if (empty($uuids)) {
                                throw new \Exception('No se proporcionaron UUIDs válidos.');
                            }

                            $placeholders = [];
                            foreach ($uuids as $index => $uuid) {
                                $placeholder = ':uuid_' . $index;
                                $placeholders[] = $placeholder;
                                $params[$placeholder] = $uuid;
                            }

                            if (!empty($placeholders)) {
                                $filtrosSQL .= ' AND fc.uuid IN (' . implode(', ', $placeholders) . ')';
                            }
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
                echo '<br><br>Filtros SQL: ' . $filtrosSQL . '<br><br>';
            }

            $sql = "SELECT DISTINCT
                        pgc.id,
                        pgc.idPagoDet,
                        pgc.idAcuse,
                        pgc.OC,
                        pgc.HES,
                        pgc.montoPagado,
                        pgc.saldoInsoluto,
                        pgc.moneda,
                        pgc.tipoCambio,
                        pgc.montoTipoCambio,
                        pgc.monedaTipoCambio,
                        pgc.formaPago,
                        pgc.formaPagoSAT,
                        pgc.fechaPago,
                        pgc.fechaReg,
                        dt.uuid,
                        dt.idCompra
                    FROM pagos_compras pgc
                    INNER JOIN (
                        SELECT DISTINCT dcp.idCompra, dcp.noRecepcion, fc.uuid
                        FROM cfdi_facturas fc
                        INNER JOIN compras cp ON fc.idCompra = cp.id
                        INNER JOIN detcompras dcp ON cp.id = dcp.idCompra
                        WHERE $filtrosSQL
                    ) dt ON (
                        pgc.HES = dt.noRecepcion 
                        OR FIND_IN_SET(dt.noRecepcion, REPLACE(pgc.HES, ' ', '')) > 0
                    )
                    ORDER BY dt.uuid, pgc.id DESC
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
                echo '<br><br>=== RESULTADO DE dataPagosDesdeFacturas ===<br>';
                echo 'Cantidad de registros encontrados: ' . $cantPagos . '<br>';
                echo 'Resultado de Query:<br>';
                var_dump($pagosresult);
                echo '<br><br>';

                // Si no hay resultados, intentar diagnosticar el problema
                if ($cantPagos == 0 && isset($filtros['uuids'])) {
                    echo '<br>⚠️ ADVERTENCIA: No se encontraron pagos para los UUIDs proporcionados.<br>';
                    echo 'UUIDs buscados: ' . $filtros['uuids'] . '<br>';
                    echo '<br>Verificando si los UUIDs existen en cfdi_facturas...<br>';

                    // Consulta de diagnóstico
                    $uuids = explode(',', $filtros['uuids']);
                    $uuids = array_map('trim', $uuids);
                    $placeholders = [];
                    $diagParams = [];
                    foreach ($uuids as $index => $uuid) {
                        $placeholder = ':diag_uuid_' . $index;
                        $placeholders[] = $placeholder;
                        $diagParams[$placeholder] = $uuid;
                    }

                    $sqlDiag = "SELECT fc.uuid, fc.idCompra, cp.id AS idCompra2, COUNT(dcp.id) AS numDetCompras
                                FROM cfdi_facturas fc
                                LEFT JOIN compras cp ON fc.idCompra = cp.id
                                LEFT JOIN detcompras dcp ON cp.id = dcp.idCompra
                                WHERE fc.uuid IN (" . implode(', ', $placeholders) . ")
                                GROUP BY fc.uuid, fc.idCompra";

                    $stmtDiag = $this->db->prepare($sqlDiag);
                    foreach ($diagParams as $param => $value) {
                        $stmtDiag->bindValue($param, $value, PDO::PARAM_STR);
                    }
                    $stmtDiag->execute();
                    $diagResult = $stmtDiag->fetchAll(PDO::FETCH_ASSOC);

                    echo 'UUIDs encontrados en cfdi_facturas:<br>';
                    var_dump($diagResult);
                    echo '<br><br>';
                }
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
