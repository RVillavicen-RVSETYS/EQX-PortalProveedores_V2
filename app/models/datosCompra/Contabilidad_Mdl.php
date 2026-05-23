<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible
use BD_ConnectHES;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}

require_once __DIR__ . '/../../../config/BD_Connect.php';
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';

class Contabilidad_Mdl
{
    private $db;
    private $dbHes;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Contabilidad_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
        $this->dbHes = new BD_ConnectHES();
    }

    public function listarReporteContable($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0;
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'com.idProveedor = :idProveedor'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(com.fechaReg BETWEEN :fechaInicial AND :fechaFinal)'],
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
                                $filtrosSQL .= ' AND (com.insolutoPendiente > 0 OR ISNULL(com.insolutoPendiente))';
                            } else {
                                $filtrosSQL .= ' AND com.insolutoPendiente = 0';
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
                        com.id AS Acuse,
                        cf.razonSocialEm AS RazonSocial,
                        cf.rfcEmisor AS RFC,
                        COALESCE(NULLIF(CONCAT_WS('-', NULLIF(cf.serie, ''), NULLIF(cf.folio, '')), ''), 'S/F') AS FolioFac,
                        cf.uuid AS UUIDFac,
                        cf.monto AS MontoEgreso,
                        cf.idCatMetodoPago AS MetodoPago,
                        COALESCE(GROUP_CONCAT(DISTINCT NULLIF(CONCAT_WS('-', NULLIF(nc.serie, ''), NULLIF(nc.folio, '')), '') SEPARATOR ', '), 'N/A') AS FolioNC,
                        COALESCE(GROUP_CONCAT(DISTINCT NULLIF(nc.uuid, '') SEPARATOR ', '), 'N/A') AS UUIDNC,
                        COALESCE(GROUP_CONCAT(DISTINCT NULLIF(CONCAT_WS('-', NULLIF(cp.serie, ''), NULLIF(cp.folio, '')), '') SEPARATOR ', '), 'N/A') AS FolioCP,
                        COALESCE(GROUP_CONCAT(DISTINCT NULLIF(cp.uuid, '') SEPARATOR ', '), 'N/A') AS UUIDCP,
                        COALESCE(DATE_FORMAT(cf.fechaFac, '%d/%m/%Y'), 'N/A') AS FechaFactura,
                        COALESCE(DATE_FORMAT(pc.fechaPago, '%d/%m/%Y'), 'N/A') AS FechaPago,
                        COALESCE(GROUP_CONCAT(DISTINCT NULLIF(CONCAT_WS(' - ', NULLIF(cb.nombreCorto, ''), NULLIF(pc.noCuenta, '')), '') SEPARATOR ', '), 'N/A') AS CuentaBanco,
                        COALESCE(GROUP_CONCAT(DISTINCT NULLIF(pc.tipoCambio, '') SEPARATOR ', '), 'N/A') AS TipoCambio
                    FROM
                        compras com
                        INNER JOIN cfdi_facturas cf ON com.id = cf.idCompra 
                        LEFT JOIN cfdi_complementoPagoDet cpd ON cf.uuid = cpd.uuidFact 
                        LEFT JOIN cfdi_complementoPago cp ON cpd.idComplementoPago = cp.id AND cp.estatus = '2'
                        LEFT JOIN cfdi_notasCreditos nc ON (com.id = nc.idCompra OR cf.uuid = nc.uuidRelacionado) AND nc.estatus NOT IN ('0', '3')
                        LEFT JOIN pagos_compras pc ON com.id = pc.idAcuse
                        LEFT JOIN cat_bancos cb ON pc.idClaveBanco = cb.id
                    WHERE
                        $filtrosSQL
                    GROUP BY cf.id
                    ORDER BY com.id $orden";

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
}
