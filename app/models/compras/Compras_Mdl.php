<?php

namespace App\Models\Compras;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class Compras_Mdl
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

    public function listaComprasFacturadas($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'c.idProveedor = :idProveedor'],
            'estatusFactura' => ['tipoDato' => 'INT', 'sqlFiltro' => 'c.estatus = :estatusFactura'],
            'entreFechasRecepcion' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(c.fechaReg BETWEEN :fechaInicial AND :fechaFinal)'],
            'entreFechasPago' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(c.fechaProbablePago BETWEEN :fechaInicial AND :fechaFinal)'],
            'tipoMoneda' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'c.idCatTipoMoneda = :tipoMoneda'],
            'pendientePago' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'c.estatus !=  4 AND c.fechaVence IS NULL'],
            'pagada' => ['tipoDato' => 'INT', 'sqlFiltro' => ''],
            'nacional' => ['tipoDato' => 'INT', 'sqlFiltro' => '']
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
                    if ($nombreFiltro == 'entreFechasRecepcion') {
                        list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                        if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                            throw new \Exception('Las fechas proporcionadas no son válidas.');
                        }
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':fechaInicial'] = $fechaInicial;
                        $params[':fechaFinal'] = $fechaFinal;
                    } elseif ($nombreFiltro == 'entreFechasPago') {
                        list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                        if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                            throw new \Exception('Las fechas proporcionadas no son válidas.');
                        }
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':fechaInicial'] = $fechaInicial;
                        $params[':fechaFinal'] = $fechaFinal;
                    } elseif ($nombreFiltro == 'nacional') {
                        if ($valorFiltro == 1) {
                            $filtrosSQL .= " AND pv.pais = 'MX'";
                        } else {
                            $filtrosSQL .= " AND pv.pais <> 'MX'";
                        }
                    } elseif ($nombreFiltro == 'pendientePago') {
                        if ($valorFiltro == 1) {
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        }
                    } elseif ($nombreFiltro == 'pagada') {
                        if ($valorFiltro == 1) {
                            $filtrosSQL .= ' AND c.totalPagos > 0';
                        } else {
                            $filtrosSQL .= ' AND c.totalPagos = 0';
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
            $filtrosSQL = ltrim($filtrosSQL, ' AND');

            if (self::$debug) {
                echo '<br><br>Parametros: ';
                var_dump($params);
                echo '<br><br>';
            }

            $sql = "SELECT c.id AS acuse, c.claseDocto, dc.ordenCompra, c.estatus, c.totalPagos, c.totalComplementos,
                    GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                    c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML, pv.pais, pv.id AS 'IdProveedor', pv.razonSocial AS 'RazonSocial', pv.rfc AS 'RFC',
                    cf.serie AS 'SerieFact', cf.folio AS 'FolioFact', cf.fechaReg AS 'FechaReg', c.total AS 'Total', c.fechaProbablePago AS 'FechaPago', 
                    c.fechaVence AS 'FechaVence', cf.idCatTipoMoneda AS 'TipoMonedaFac', c.notaCredito AS 'NotaCredito', cpd.CantComplementos
                    FROM compras c
                    INNER JOIN proveedores pv ON c.idProveedor = pv.id
                    INNER JOIN detcompras dc ON c.id = dc.idCompra
                    LEFT JOIN cfdi_facturas cf ON cf.idCompra = c.id
                    LEFT JOIN (SELECT cpd.uuidFact, COUNT(cpd.id) AS 'CantComplementos' FROM cfdi_complementoPagoDet cpd GROUP BY uuidFact) cpd ON cf.uuid = cpd.uuidFact
                    WHERE $filtrosSQL
                    GROUP BY c.id, c.claseDocto, dc.ordenCompra, c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML 
                    ORDER BY c.id $orden
                    $limiteResult";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista ultimas Compras');
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
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Compras Facturadas: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar las Facturas Cargadas, Notifica a tu administrador.'];
        }
    }

    public function dataCompraPorFacturas($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'uuids' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'fc.uuid IN (:uuids)'],
            'metodoPago' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'fc.idCatMetodoPago =:metodoPago'],
            'formaPago' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'fc.idCatFormaPago =:formaPago'],
            'estatusPagado' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'cp.idPago'], // Se usa con 0 si no está pagado o con 1 si está pagado
            'idProveedor' => ['tipoDato' => 'INT', 'sqlFiltro' => 'c.idProveedor = :idProveedor'],
            'entreFechas' => ['tipoDato' => 'STRING', 'sqlFiltro' => '(c.fechaReg BETWEEN :fechaInicial AND :fechaFinal)']
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
                    if ($nombreFiltro == 'entreFechas') {
                        list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                        if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                            throw new \Exception('Las fechas proporcionadas no son válidas.');
                        }
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':fechaInicial'] = $fechaInicial;
                        $params[':fechaFinal'] = $fechaFinal;
                    } elseif ($nombreFiltro == 'uuids') {
                        // Validar que el valor sea una cadena de UUIDs separados por comas
                        $uuids = explode(',', $valorFiltro);
                        $uuids = array_map('trim', $uuids); // Limpiar espacios en blanco
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':uuids'] = implode(',', $uuids); // Convertir a cadena separada por comas
                    } elseif ($nombreFiltro == 'estatusPagado') {
                        if ($valorFiltro === 0) {
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'] . ' IS NULL';
                        } elseif ($valorFiltro === 1) {
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'] . ' IS NOT NULL';
                        } else {
                            throw new \Exception('El valor de estatusPagado debe ser 0 o 1.');
                        }
                    }else {
                        $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                        $params[':' . $nombreFiltro] = $valorFiltro;
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

            $sql = "SELECT cp.*, fc.*, MAX(cpd.idComplementoPago) AS idUltimoComplemento, MAX(cpd.noParcialidad) AS ultimaParcialidad, MIN(cpd.saldoInsoluto) AS minInsoluto
                    FROM compras cp
                    INNER JOIN cfdi_facturas fc ON cp.id = fc.idCompra
                    LEFT JOIN cfdi_complementoPagoDet cpd ON fc.uuid = cpd.uuidFact
                    WHERE $filtrosSQL
                    GROUP BY fc.uuid
                    ORDER BY fc.fechaFac $orden
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

    public function dataCompraPorAcuse(INT $idUser, INT $acuse)
    {
        self::$debug = 0;
        if (empty($idUser) || empty($acuse)) {
            return ['success' => false, 'message' => 'Se requiere No. de Acuse.'];
        } else {
            try {
                if ($_SESSION['EQXAdmin']) {
                    $validaUsuario = '';
                } else {
                    $validaUsuario = "AND c.idProveedor = $idUser";
                }

                $sql = "SELECT c.id AS acuse, c.claseDocto, c.estatus AS CpaEstatus, c.fechaVal, c.comentRegresa, c.subTotal, c.idCatTipoMoneda AS CpaTipoMoneda,
                            c.idProveedor, c.notaCredito, c.totalPagos,	c.totalComplementos, c.fechaReg, c.referencia, c.fechaVence AS 'FechaVence', c.fechaProbablePago AS 'FechaProbablePago',
                            dcp.ordenCompra, dcp.noRecepcion,
                            cf.urlPDF AS FacUrlPDF, cf.urlXML AS FacUrlXML, cf.subtotal AS FacSubtotal, cf.monto AS FacMonto, cf.idCatTipoMoneda AS FacTipoMoneda, 
                            cf.idCatMetodoPago AS FacMetodoPago, cf.idCatFormaPago AS FacFormaPago, cf.usoCfdi AS FacUsoCfdi,cuc.descripcion AS nameUsoCfdi, 
                            cf.uuid AS FacUUID, cf.fechaFac, cf.serie AS FacSerie, cf.folio AS FacFolio, cf.razonSocialEm, cf.version AS FacVersion,
                            dfi.*,
                            nc.uuid AS NCredUUID, nc.serie AS NCredSerie, nc.folio AS NCredFolio, nc.urlPDF AS NCredUrlPDF, nc.urlXML AS NCredUrlXML, 
                            cpg.uuid AS CPagUUID, cpg.serie AS CPagSerie, cpg.folio AS CPagFolio, cpg.urlPDF AS CPagUrlPDF, cpg.urlXML AS CPagUrlXML,
                            cpd.*												
                        FROM compras c
                        INNER JOIN (
                            SELECT  dc.idCompra, dc.ordenCompra, GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion, SUM(dc.monto) AS subTotal
                            FROM detcompras dc
                            GROUP BY dc.idCompra)dcp ON c.id = dcp.idCompra
                        INNER JOIN cfdi_facturas cf ON c.id = cf.idCompra
                        INNER JOIN (
                            SELECT fi.idFactura,
                                GROUP_CONCAT(DISTINCT CASE WHEN fi.tipo = 'Traslado' THEN CONCAT(fi.impuesto, ' = ', fi.Importe) END ORDER BY fi.impuesto SEPARATOR ', ') AS impuestosTrasMontos,
                                GROUP_CONCAT(DISTINCT CASE WHEN fi.tipo = 'Retencion' THEN CONCAT(fi.impuesto, ' = ', fi.Importe) END ORDER BY fi.impuesto SEPARATOR ', ') AS impuestosRetMontos,
                                GROUP_CONCAT(DISTINCT CASE WHEN fi.tipo = 'Traslado' THEN fi.impuesto END ORDER BY fi.impuesto SEPARATOR ', ') AS impuestosTrasladados,
                                GROUP_CONCAT(DISTINCT CASE WHEN fi.tipo = 'Retencion' THEN fi.impuesto END ORDER BY fi.impuesto SEPARATOR ', ') AS impuestosRetenidos
                            FROM cfdi_facturasImpuestos fi
                            GROUP BY fi.idFactura
                        )dfi ON cf.id = dfi.idFactura
                        LEFT JOIN cfdi_notasCreditos nc ON c.id = nc.idCompra
                        LEFT JOIN cfdi_complementoPagoDet cpd ON cf.uuid = cpd.uuidFact
                        LEFT JOIN cfdi_complementoPago cpg ON cpd.idComplementoPago = cpg.id
                        LEFT JOIN sat_catUsoCFDI cuc ON cf.usoCfdi = cuc.id
                        WHERE c.id = :acuse $validaUsuario
                        LIMIT 1";

                if (self::$debug) {
                    $params = [':acuse' => $acuse];
                    $this->db->imprimirConsulta($sql, $params, 'Datos de compra y Facturas por Acuse');
                }
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':acuse', $acuse, PDO::PARAM_INT);
                $stmt->execute();
                $comprasresult = $stmt->fetch(PDO::FETCH_ASSOC);

                if (self::$debug) {
                    echo '<br>Resultado de Query:';
                    var_dump($comprasresult);
                    echo '<br><br>';
                }

                return ['success' => true, 'data' => $comprasresult];
            } catch (\Exception $e) {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
                if (self::$debug) {
                    echo "Error al listar Compras: " . $e->getMessage(); // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Problemas al buscar este Documento, Notifica a tu administrador.'];
            }
        }
    }

    public function dataUrlPorAcuses($arrayAcuses)
    {
        
        if (empty($arrayAcuses)) {
            return ['success' => false, 'message' => 'Se requiere acuses de facturas.'];
        } else {

            $listaAcuses = implode(', ', array_map(function ($item) {
                return "'" . addslashes($item) . "'";
            }, $arrayAcuses));

            try {
                $sql = "SELECT
                            c.id AS 'Acuse',
                            prov.rfc AS 'RFC',
                            cf.urlPDF AS 'FacUrlPDF',
                            cf.urlXML AS 'FacUrlXML',
                            CONCAT( cf.serie, cf.folio ) AS 'FacSerie',
                            nc.urlPDF AS 'NCredUrlPDF',
                            nc.urlXML AS 'NCredUrlXML',
                            CONCAT( nc.serie, nc.folio ) AS 'NCredSerie',
                            cpg.urlPDF AS 'CPagUrlPDF',
                            cpg.urlXML AS 'CPagUrlXML',
                            CONCAT( cpg.serie, cpg.folio ) AS 'CPagSerie'
                        FROM
                            compras c
                            INNER JOIN proveedores prov ON c.idProveedor = prov.id
                            INNER JOIN cfdi_facturas cf ON c.id = cf.idCompra
                            LEFT JOIN cfdi_notasCreditos nc ON c.id = nc.idCompra
                            LEFT JOIN cfdi_complementoPagoDet cpd ON cf.uuid = cpd.uuidFact
                            LEFT JOIN cfdi_complementoPago cpg ON cpd.idComplementoPago = cpg.id 
                        WHERE
                            c.id IN ($listaAcuses)";
                if (self::$debug) {
                    $params = [];
                    $this->db->imprimirConsulta($sql, $params, 'Urls De Facturas por Acuses');
                }
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $comprasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (self::$debug) {
                    echo '<br>Resultado de Query:';
                    var_dump($comprasresult);
                    echo '<br><br>';
                }

                return ['success' => true, 'data' => $comprasresult];
            } catch (\Exception $e) {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Url de Facturas: " . $e->getMessage(), 3, LOG_FILE_BD);
                if (self::$debug) {
                    echo "Error al listar Compras: " . $e->getMessage(); // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Problemas al buscar Url, Notifica a tu administrador.'];
            }
        }
    }

    public function cantComprasPorProveedor(INT $idProveedor)
    {
        if (empty($idProveedor)) {
            return ['success' => false, 'message' => 'Se requiere No. de Proveedor.'];
        } else {
            try {
                $sql = "SELECT COUNT(c.id) AS cantCompras
                        FROM compras c
                        WHERE c.idProveedor = :noProveedor";

                if (self::$debug) {
                    $params = [':noProveedor' => $idProveedor];
                    $this->db->imprimirConsulta($sql, $params, 'Cantidad de Compras por Proveedor');
                }
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':noProveedor', $idProveedor, PDO::PARAM_INT);
                $stmt->execute();
                $comprasresult = $stmt->fetch(PDO::FETCH_ASSOC);

                if (self::$debug) {
                    echo '<br>Resultado de Query:';
                    var_dump($comprasresult);
                    echo '<br><br>';
                }

                return ['success' => true, 'data' => $comprasresult];
            } catch (\Exception $e) {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
                if (self::$debug) {
                    echo "Error al listar Compras: " . $e->getMessage(); // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Problemas al buscar este Documento, Notifica a tu administrador.'];
            }
        }
    }

    public function actualizarDataCompras($campos = [], $filtros = [])
    {
        $camposValidos = [
            'estatus' => ['tipoDato' => 'INT', 'sqlQuery' => ', estatus = :estatus'],
            'comentRegresa' => ['tipoDato' => 'STRING', 'sqlQuery' => ', comentRegresa = :comentRegresa'],
            'fechaProbablePago' => ['tipoDato' => 'STRING', 'sqlQuery' => ', fechaProbablePago = :fechaProbablePago'],
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id'],
        ];

        try {
            if (!is_array($campos) || !is_array($filtros)) {
                throw new \Exception('Los campos y filtros deben ser arreglos.');
            }

            if (count($campos) == 0) {
                throw new \Exception('Los campos no pueden estar vacíos.');
            }

            if (count($filtros) == 0) {
                throw new \Exception('Los filtros no pueden estar vacíos.');
            }

            $params = [];
            $invalidCampos = [];
            $invalidFiltros = [];
            $camposSQL = '';
            $filtrosSQL = '';

            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    $invalidCampos[] = $campo;
                } else {
                    $camposSQL .= $camposValidos[$campo]['sqlQuery'];
                    $params[":$campo"] = $valor;
                }
            }

            foreach ($filtros as $filtro => $valor) {
                if (!array_key_exists($filtro, $filtrosValidos)) {
                    $invalidFiltros[] = $filtro;
                } else {
                    $filtrosSQL .= (empty($filtrosSQL) ? ' WHERE ' : ' AND ') . $filtrosValidos[$filtro]['sqlQuery'];
                    $params[":$filtro"] = $valor;
                }
            }

            // Si hay errores de validación, lanza excepción con detalles
            if (!empty($invalidCampos) || !empty($invalidFiltros)) {
                throw new \Exception(
                    (!empty($invalidCampos) ? 'Campos no válidos: ' . implode(', ', $invalidCampos) . '. ' : '') .
                        (!empty($invalidFiltros) ? 'Filtros no válidos: ' . implode(', ', $invalidFiltros) . '.' : '')
                );
            }

            $idUser = $_SESSION['EQXident'];

            $sql = "UPDATE compras SET " . ltrim($camposSQL, ',') . ", idUserValida = '$idUser', fechaVal = NOW() " . $filtrosSQL;

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Actualiza Datos Del Proveedor');
            }

            $stmt = $this->db->prepare($sql);

            $allParametros = array_merge($camposValidos, $filtrosValidos); // Unimos ambos arreglos
            foreach ($params as $param => $value) {
                $clave = trim($param, ':'); // Elimina ":" del nombre del parámetro
                if (isset($allParametros[$clave])) { // Verifica que la clave exista
                    $stmt->bindValue($param, $value, $allParametros[$clave]['tipoDato'] == 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas >= 1) {
                return [
                    'success' => true,
                    'message' => 'Datos Actualizados Correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Los Datos.<br>";
                }
                return ['success' => false, 'message' => 'No Se Actualizo Ningún Dato.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error Al Actualizar Datos De La Factura: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
