<?php

namespace App\Models\DatosCFDIs;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class CFDIs_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase CFDIs_Mdl.</h2>";
        }

        $this->db = new BD_Connect();
    }

    public function obtenerComplementosDePago($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'cp.uuid IN (:uuids)'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(cp.fechaReg BETWEEN :fechaInicial AND :fechaFinal)'],
            'soloActivos' => ['tipoDato' => 'INT', 'sqlFiltro' => 'cp.estatus IN (0, 1, 2)']
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

                        case 'uuids':
                            // Validar que el valor sea una cadena de UUIDs separados por comas
                            $uuids = explode(',', $valorFiltro);
                            $uuids = array_map('trim', $uuids); // Limpiar espacios en blanco
                            // Construir la consulta IN manualmente para múltiples UUIDs
                            $placeholders = [];
                            foreach ($uuids as $index => $uuid) {
                                $placeholder = ':uuid_' . $index;
                                $placeholders[] = $placeholder;
                                $params[$placeholder] = $uuid;
                            }
                            if (!empty($placeholders)) {
                                $filtrosSQL .= ' AND cp.uuid IN (' . implode(', ', $placeholders) . ')';
                            }
                            break;

                        case 'soloActivos':
                            // Si soloActivos es true, filtrar solo estatus activos (0, 1, 2), excluyendo rechazados (3)
                            if ($valorFiltro == 1 || $valorFiltro === true) {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
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
                echo '<br>';
            }

            $sql = "SELECT * 
                    FROM cfdi_complementoPago cp
                    WHERE $filtrosSQL
                    ORDER BY cp.fechaReg $orden
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista de Facturas por UUID: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $comprasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantCompras = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($comprasresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantCompras, 'data' => $comprasresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Facturas por UUID: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Facturas por UUID: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar Facturas por UUID, Notifica a tu administrador.'];
        }
    }

    public function obtenerComplementosDePagoAgrupados($filtros = [], $agrupar = [], $valoresRetornar = [], INT $cantMaxRes = 0, $orden = 'DESC', $campoOrdenar = '')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
            echo '<br><br>Valores a Retornar: ';
            var_dump($valoresRetornar);
            echo '<br><br>Valores a Agrupar: ';
            var_dump($agrupar);
        }

        // Definición de filtros, agrupados y valores a retornar disponibles
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'cp.uuid IN (:uuids)'],
            'uuidFacts' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'dcp.uuidFact IN (:uuidFacts)'],
            'idComplemento' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'cp.idComplemento IN (:idComplemento)'],
            'saldoInsoluto' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'dat.minimoInsoluto'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(cp.fechaReg BETWEEN :fechaInicial AND :fechaFinal)']
        ];

        $agrupadosDisponibles = [
            'uuid' => ['sqlGroupBy' => 'cp.uuid'],
            'uuidFact' => ['sqlGroupBy' => 'dcp.uuidFact'],
            'tipoMoneda' => ['sqlGroupBy' => 'dcp.idCatTipoMoneda'],
            'formaPago' => ['sqlGroupBy' => 'dcp.formaPago'],
        ];

        $valoresRetornarDisponibles = [
            'cantidadComplementos' => ['sqlSelect' => 'COUNT(cp.id) AS cantComplementos'],
            'cantidadPagos' => ['sqlSelect' => 'COUNT(dcp.id) AS cantPagos'],
            'cnatidadInsolutos' => ['sqlSelect' => 'COUNT(dcp.saldoInsoluto) AS cantInsolutos'],
            'minimoInsoluto' => ['sqlSelect' => 'MIN(dcp.saldoInsoluto) AS minimoInsoluto'],
            'montoPagado' => ['sqlSelect' => 'SUM(dcp.montoPagado) AS montoPagado'],
            'mayorParcialidad' => ['sqlSelect' => 'MAX(dcp.noParcialidad) AS mayorParcialidad'],
        ];

        $filtrosSQL = '';
        $filtrosSQL1 = '';
        $agrupadosSQL = '';
        $valoresRetornarSQL = '';
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

            //Filtros de consulta
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
                            $filtrosSQL1 .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':uuids'] = implode(',', $uuids); // Convertir a cadena separada por comas
                            break;

                        case 'uuidFacts':
                            // Validar que el valor sea una cadena de uuidFacts separados por comas
                            $uuidFacts = explode(',', $valorFiltro);
                            $uuidFacts = array_map('trim', $uuidFacts); // Limpiar espacios en blanco
                            $filtrosSQL1 .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':uuidFacts'] = implode(',', $uuidFacts); // Convertir a cadena separada por comas
                            break;

                        case 'saldoInsoluto':
                            // Validar que el valor sea true o false
                            if (!in_array($valorFiltro, ['true', 'false'])) {
                                throw new \Exception('El valor de saldoInsoluto debe ser true o false.');
                            }
                            if ($valorFiltro == 'true') {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'] . ' > 0';
                            } else {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'] . ' = 0';
                            }

                            break;

                        default:
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $filtrosSQL1 .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':' . $nombreFiltro] = $valorFiltro;
                            break;
                    }
                }
            }
            if (empty($filtrosSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido.');
            }
            $filtrosSQL = ltrim($filtrosSQL, ' AND');
            $filtrosSQL1 = (empty($filtrosSQL1)) ? '' : 'WHERE ' . ltrim($filtrosSQL1, ' AND');

            // Filtros de agrupación
            foreach ($agrupar as $nombreAgrupado) {
                if (isset($agrupadosDisponibles[$nombreAgrupado])) {
                    $agrupadosSQL .= ', ' . $agrupadosDisponibles[$nombreAgrupado]['sqlGroupBy'];
                }
            }
            if (empty($agrupadosSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido para agrupar.');
            }
            $agrupadosSQL = ltrim($agrupadosSQL, ', ');

            // Valores a retornar
            foreach ($valoresRetornar as $nombreValor) {
                if (isset($valoresRetornarDisponibles[$nombreValor])) {
                    $valoresRetornarSQL .= ', ' . $valoresRetornarDisponibles[$nombreValor]['sqlSelect'];
                }
            }
            if (empty($valoresRetornarSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido para retornar.');
            }
            $valoresRetornarSQL = ltrim($valoresRetornarSQL, ', ');
            $valoresRetornarSQL = $agrupadosSQL . ', ' . $valoresRetornarSQL;

            if (self::$debug) {
                echo '<br><br>Parametros: ';
                var_dump($params);
                echo '<br>';
            }

            $sql = "SELECT * 
                    FROM (
                        SELECT $valoresRetornarSQL
                        FROM cfdi_complementoPago cp 
                        INNER JOIN cfdi_complementoPagoDet dcp ON cp.id = dcp.idComplementoPago
                        $filtrosSQL1
                        GROUP BY $agrupadosSQL
                    ) dat
                    WHERE $filtrosSQL
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Consulta de Complementos Agrupada: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $comprasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantCompras = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($comprasresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantCompras, 'data' => $comprasresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Facturas por UUID: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Facturas por UUID: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar Facturas por UUID, Notifica a tu administrador.'];
        }
    }

    public function obtenerFacturasPorUUID($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'fc.uuid IN (:uuids)'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(fc.fechaReg BETWEEN :fechaInicial AND :fechaFinal)']
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

            $sql = "SELECT fc.*, c.id AS acuse
                    FROM cfdi_facturas fc
                    INNER JOIN compras c ON fc.idCompra = c.id
                    WHERE $filtrosSQL
                    ORDER BY fc.idCompra $orden
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista de Facturas por UUID: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $comprasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantCompras = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($comprasresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantCompras, 'data' => $comprasresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Facturas por UUID: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Facturas por UUID: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar Facturas por UUID, Notifica a tu administrador.'];
        }
    }

    public function obtenerNotasCredito($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos para Notas de Crédito: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'nc.uuid IN (:uuids)'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(nc.fechaReg BETWEEN :fechaInicial AND :fechaFinal)'],
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'nc.idProveedor = :idProveedor'],
            'noProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'nc.noProveedor = :noProveedor']
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

                        case 'uuids':
                            $uuids = explode(',', $valorFiltro);
                            $uuids = array_map('trim', $uuids);
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':uuids'] = implode(',', $uuids);
                            break;

                        default:
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':' . $nombreFiltro] = $valorFiltro;
                            break;
                    }
                }
            }

            if (empty($filtrosSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido para buscar Notas de Crédito.');
            }
            $filtrosSQL = ltrim($filtrosSQL, ' AND');

            if (self::$debug) {
                echo '<br><br>Parametros: ';
                var_dump($params);
                echo '<br><br>';
            }

            $sql = "SELECT * 
                    FROM cfdi_notasCreditos nc
                    WHERE $filtrosSQL
                    ORDER BY nc.id $orden
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista de Notas de Crédito por UUID: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cantResult = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query NC:';
                var_dump($result);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantResult, 'data' => $result];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/DatosCFDIs/CFDIs_Mdl.php ->Error buscar Notas de Crédito por UUID: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Notas de Crédito por UUID: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas al listar Notas de Crédito por UUID, Notifica a tu administrador.'];
        }
    }

    public function obtenerTotalesPorCompra($idCompra)
    {
        self::$debug = 0; // 1 para depuración
        $response = ['success' => false, 'message' => 'No se encontraron totales.'];

        try {
            if (empty($idCompra)) {
                throw new \Exception('El ID de la compra es requerido.');
            }

            $sql = "
                SELECT
                    f.monto AS totalFactura,
                    
                    -- Totales de Notas de Crédito
                    COALESCE(nc.cantidad_nc, 0) AS cantidadNotasCredito,
                    COALESCE(nc.total_nc, 0) AS totalNotasCredito,

                    -- Totales de Complementos de Pago
                    COALESCE(cp.cantidad_pagos, 0) AS cantidadPagos,
                    COALESCE(cp.total_pagado, 0) AS totalPagado,

                    -- Saldo Calculado
                    (f.monto - COALESCE(nc.total_nc, 0) - COALESCE(cp.total_pagado, 0)) AS saldoCalculado

                FROM 
                    cfdi_facturas f

                -- Subconsulta para Notas de Crédito
                LEFT JOIN (
                    SELECT 
                        idCompra,
                        COUNT(id) AS cantidad_nc,
                        SUM(total) AS total_nc
                    FROM 
                        cfdi_notasCreditos
                    WHERE 
                        idCompra = :idCompra_nc AND estatus = 1
                    GROUP BY 
                        idCompra
                ) AS nc ON f.idCompra = nc.idCompra

                -- Subconsulta para Complementos de Pago
                LEFT JOIN (
                    SELECT 
                        c.id AS idCompra,
                        COUNT(pd.id) as cantidad_pagos,
                        SUM(pd.importePagado) as total_pagado
                    FROM 
                        cfdi_facturas c
                    INNER JOIN 
                        cfdi_complementoPagoDet pd ON c.uuid = pd.uuidFact
                    INNER JOIN
                        cfdi_complementoPago p ON pd.idComplementoPago = p.id
                    WHERE
                        c.idCompra = :idCompra_cp AND p.estatus = 2
                    GROUP BY
                        c.id
                ) AS cp ON f.idCompra = cp.idCompra

                WHERE 
                    f.idCompra = :idCompra_main;
            ";

            $params = [
                ':idCompra_nc' => $idCompra,
                ':idCompra_cp' => $idCompra,
                ':idCompra_main' => $idCompra
            ];

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Obtener Totales por Compra');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response = ['success' => true, 'data' => $result];
            }
        } catch (\Exception $e) {
            // ... (Manejo de errores)
            $response['message'] = 'Error al obtener los totales: ' . $e->getMessage();
        }

        return $response;
    }

    public function obtenerDatosGraficaDona($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'year' => ['tipoDato' => 'INT', 'sqlFiltro' => ''],
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

                        case 'year':
                            $filtrosSQL .= ' AND com.fechaReg BETWEEN :inicioFecha AND :finFecha';
                            $params[':inicioFecha'] = $valorFiltro . '-01-01';
                            $params[':finFecha'] = $valorFiltro . '-12-31';
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
                        estatuses.nombre_estatus,
                        IFNULL(t.total, 0) AS total
                    FROM (
                        SELECT 1 AS estatus, 'Pendientes' AS nombre_estatus
                        UNION
                        SELECT 2, 'Aceptadas'
                        UNION
                        SELECT 4, 'Canceladas'
                    ) AS estatuses
                    LEFT JOIN (
                        SELECT
                            com.estatus,
                            COUNT(*) AS total
                        FROM
                            compras com
                        WHERE
                            $filtrosSQL
                        GROUP BY
                            com.estatus
                    ) AS t ON t.estatus = estatuses.estatus;";
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Datos Para La Grafica: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $comprasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantCompras = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($comprasresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantCompras, 'data' => $comprasresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Datos Para La Grafica: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Datos Para La Grafica: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar Datos Para La Grafica, Notifica a tu administrador.'];
        }
    }

    public function obtenerDatosGraficaLine($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'year' => ['tipoDato' => 'INT', 'sqlFiltro' => ''],
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

                        case 'year':
                            $filtrosSQL .= ' AND cf.fechaFac BETWEEN :inicioFecha AND :finFecha';
                            $params[':inicioFecha'] = $valorFiltro . '-01-01';
                            $params[':finFecha'] = $valorFiltro . '-12-31';
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
                        YEAR(cf.fechaFac) AS Año,
                        MONTH(cf.fechaFac) AS Mes,
                        SUM(cf.monto) AS TotalFacturado,
                        SUM(CASE WHEN pc.fechaPago IS NOT NULL THEN pc.montoPagado ELSE 0 END) AS TotalPagado
                    FROM
                        cfdi_facturas cf
                        INNER JOIN compras com ON cf.idCompra = com.id
                        LEFT JOIN pagos_compras pc ON com.id = pc.idAcuse
                    WHERE
                        $filtrosSQL
                    GROUP BY
                        YEAR(cf.fechaFac),
                        MONTH(cf.fechaFac)
                    ORDER BY
                        Año, Mes;";
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Datos Para La Grafica: ');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $comprasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantCompras = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($comprasresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantCompras, 'data' => $comprasresult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Datos Para La Grafica: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Datos Para La Grafica: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar Datos Para La Grafica, Notifica a tu administrador.'];
        }
    }
}
