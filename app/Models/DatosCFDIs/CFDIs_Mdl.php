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
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(cp.fechaReg BETWEEN :fechaInicial AND :fechaFinal)']
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
            $filtrosSQL1 = (empty($filtrosSQL1)) ? '' : 'WHERE '.ltrim($filtrosSQL1, ' AND');

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

            $sql = "SELECT * 
                    FROM cfdi_facturas fc
                    WHERE $filtrosSQL
                    ORDER BY fc.idcompra $orden
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

}