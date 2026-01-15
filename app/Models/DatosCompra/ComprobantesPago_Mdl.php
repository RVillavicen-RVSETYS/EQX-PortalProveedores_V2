<?php

namespace App\Models\DatosCompra;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class ComprobantesPago_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase ComprobantesPago_Mdl.</h2>";
        }

        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function obtenerComplementosPendientes($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'c.idProveedor = :idProveedor']
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
                    $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                    $params[':' . $nombreFiltro] = $valorFiltro;
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

            // Query para buscar facturas con método de pago PPD que tienen pagos pero no todos los complementos
            $sql = "SELECT c.id AS acuse, c.idProveedor, c.totalPagos, c.totalComplementos, 
                    cf.uuid, cf.serie, cf.folio, cf.fechaFac, cf.monto, cf.idCatTipoMoneda,
                    (c.totalPagos - c.totalComplementos) AS complementosPendientes
                    FROM compras c
                    INNER JOIN cfdi_facturas cf ON cf.idCompra = c.id
                    WHERE $filtrosSQL
                    AND cf.idCatMetodoPago = 'PPD'
                    AND c.totalPagos > 0
                    AND c.totalPagos > c.totalComplementos
                    ORDER BY c.id $orden
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Obtener Complementos Pendientes');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $complementosResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantResult = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($complementosResult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantResult, 'data' => $complementosResult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            $errorMessage = $e->getMessage();
            error_log("[$timestamp] app/Models/DatosCompra/ComprobantesPago_Mdl.php ->Error en obtenerComplementosPendientes: " . $errorMessage, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al obtener complementos pendientes: " . $errorMessage; // Mostrar error en modo depuración
            }
            // Retornar mensaje más específico si es posible
            $mensajeUsuario = 'Problemas al obtener los complementos de pago pendientes, Notifica a tu administrador.';
            if (strpos($errorMessage, 'No se encontró ningún parámetro válido') !== false) {
                $mensajeUsuario = 'No se proporcionaron parámetros válidos para buscar los complementos de pago pendientes.';
            } elseif (strpos($errorMessage, 'SQLSTATE') !== false || strpos($errorMessage, 'SQL') !== false) {
                $mensajeUsuario = 'Error de conexión con la base de datos al buscar complementos de pago pendientes.';
            }
            return ['success' => false, 'message' => $mensajeUsuario];
        }
    }
}
