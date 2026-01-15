<?php

namespace App\Models\Compras;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class ComprasAgrupadas_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Compras_Mdl.</h2>";
        }

        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function ComprasAgrupadas($filtros = [], $agrupar = [], $valoresRetornar = [], $orden = [], $cantMaxRes = 0)
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
            echo '<br>Valores a Retornar: ';
            var_dump($valoresRetornar);
            echo '<br>Valores a Agrupar: ';
            var_dump($agrupar);
        }

        // Definición de filtros, agrupados y valores a retornar disponibles
        $filtrosDisponibles = [
            'acuses' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'cp.id IN (:acuses)'],
            'idProveedor' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'cp.idComplemento = :idProveedor'],
            'debeComplemento' => ['tipoDato' => 'STRING', 'sqlFiltro' => ''], //Se usa true o false
            'pendientePorPagar' => ['tipoDato' => 'STRING', 'sqlFiltro' => ''], //Se usa true o false
            'pendientePorProcesar' => ['tipoDato' => 'STRING', 'sqlFiltro' => ''], //Se usa true o false
            'estatus' => ['tipoDato' => 'INT', 'sqlFiltro' => 'cp.estatus = :estatus'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(cp.fechaReg BETWEEN :fechaInicial AND :fechaFinal)']
        ];

        $agrupadosDisponibles = [
            'idProveedor' => ['sqlGroupBy' => 'cp.idProveedor'],
            'estatus' => ['sqlGroupBy' => 'cp.estatus'],
        ];

        $valoresRetornarDisponibles = [
            'cantCompras' => ['sqlSelect' => 'COUNT(cp.id) AS cantCompras'],
            'sumaTotales' => ['sqlSelect' => 'SUM(cp.total) AS totalCompras']
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

            if (empty($orden['tipo'])) {
                $ordenamiento = '';
            } else {
                if (!in_array($orden['tipo'], ['DESC', 'ASC'])) {
                    throw new \Exception('El orden debe ser DESC o ASC.');
                } else {
                    if (empty($orden['campo'])) {
                        $ordenamiento = '';
                    } else {
                        $ordenamiento = 'ORDER BY ' . $orden['campo'] . ' ' . strtoupper($orden['tipo']);
                    }
                    
                }
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

                        case 'acuses':
                            // Validar que el valor sea una cadena de acuses separados por comas
                            $acuses = explode(',', $valorFiltro);
                            $acuses = array_map('trim', $acuses); // Limpiar espacios en blanco
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':acuses'] = implode(',', $acuses); // Convertir a cadena separada por comas
                            break;

                        case 'debeComplemento':
                            // Validar que el valor sea true o false
                            if (!in_array($valorFiltro, ['true', 'false'])) {
                                throw new \Exception('El valor de debeComplemento debe ser true o false.');
                            }
                            if ($valorFiltro == 'true') {
                                $filtrosSQL .= ' AND cp.totalPagos > cp.totalComplementos';
                            } else {
                                $filtrosSQL .= ' AND cp.totalPagos <= cp.totalComplementos';
                            }
                            break;

                        case 'pendientePorPagar':
                            // Validar que el valor sea true o false
                            if (!in_array($valorFiltro, ['true', 'false'])) {
                                throw new \Exception('El valor de debeComplemento debe ser true o false.');
                            }
                            if ($valorFiltro == 'true') {
                                $filtrosSQL .= ' AND cp.total > cp.totalPagos';
                            } else {
                                $filtrosSQL .= ' AND cp.total <= cp.totalPagos';
                            }
                            break;
                        
                        case 'pendientePorProcesar':
                            // Validar que el valor sea true o false
                            if (!in_array($valorFiltro, ['true', 'false'])) {
                                throw new \Exception('El valor de debeComplemento debe ser true o false.');
                            }
                            if ($valorFiltro == 'true') {
                                $filtrosSQL .= ' AND (cp.estatus = 1 AND cp.totalPagos = 0)';
                            } else {
                                $filtrosSQL .= ' AND cp.estatus = 2';
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
                echo '<br>Parametros: ';
                var_dump($params);
                echo '<br>';
            }

            $sql = "SELECT * 
                    FROM (
                        SELECT $valoresRetornarSQL
                        FROM compras cp 
                        WHERE $filtrosSQL
                        GROUP BY $agrupadosSQL
                    ) dat
                    $filtrosSQL1
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
}
