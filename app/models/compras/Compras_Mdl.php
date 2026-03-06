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
    private static $debug = 0; // Debug desactivado

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Compras_Mdl.</h2>";
        }

        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function listaComprasFacturadas($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
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
            'pendienteAprobacion' => ['tipoDato' => 'INT', 'sqlFiltro' => '(c.estatus = 1 OR COALESCE(ncp.CantNotasPendientes, 0) > 0 OR COALESCE(cpp.CantComplementosPendientes, 0) > 0)'],
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
                    switch ($nombreFiltro) {
                        case 'entreFechasRecepcion':
                        case 'entreFechasPago':
                            list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                            if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                                throw new \Exception('Las fechas proporcionadas no son válidas.');
                            }
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':fechaInicial'] = $fechaInicial;
                            $params[':fechaFinal'] = $fechaFinal;
                            break;

                        case 'nacional':
                            $filtrosSQL .= $valorFiltro == 1 ? " AND pv.pais = 'MX'" : " AND pv.pais <> 'MX'";
                            break;

                        case 'pendientePago':
                            if ($valorFiltro == 1) {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            }
                            break;

                        case 'pagada':
                            $filtrosSQL .= $valorFiltro == 1 ? ' AND c.totalPagos > 0' : ' AND c.totalPagos = 0';
                            break;
                        case 'pendienteAprobacion':
                            if ($valorFiltro == 1) {
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
                echo '<br><br>';
            }

            $sql = "SELECT c.id AS acuse, c.claseDocto, dc.ordenCompra, c.estatus, c.totalPagos, c.totalComplementos,
                    GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                    c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML, pv.pais, pv.id AS 'IdProveedor', pv.razonSocial AS 'RazonSocial', pv.rfc AS 'RFC',
                    cf.serie AS 'SerieFact', cf.folio AS 'FolioFact', cf.fechaReg AS 'FechaReg', c.total AS 'Total', c.fechaProbablePago AS 'FechaPago', 
                    c.fechaVence AS 'FechaVence', cf.idCatTipoMoneda AS 'TipoMonedaFac', c.notaCredito AS 'NotaCredito', cpd.CantComplementos,
                    COALESCE(ncp.CantNotasPendientes, 0) AS 'CantNotasPendientes',
                    COALESCE(cpp.CantComplementosPendientes, 0) AS 'CantComplementosPendientes'
                    FROM compras c
                    INNER JOIN proveedores pv ON c.idProveedor = pv.id
                    INNER JOIN detcompras dc ON c.id = dc.idCompra
                    LEFT JOIN cfdi_facturas cf ON cf.idCompra = c.id
                    LEFT JOIN (SELECT cpd.uuidFact, COUNT(cpd.id) AS 'CantComplementos' FROM cfdi_complementoPagoDet cpd GROUP BY uuidFact) cpd ON cf.uuid = cpd.uuidFact
                    LEFT JOIN (SELECT idCompra, COUNT(id) AS 'CantNotasPendientes' FROM cfdi_notasCreditos WHERE estatus = 1 GROUP BY idCompra) ncp ON ncp.idCompra = c.id
                    LEFT JOIN (
                        SELECT cpd.idCompra, COUNT(DISTINCT cpd.idComplementoPago) AS 'CantComplementosPendientes'
                        FROM cfdi_complementoPagoDet cpd
                        INNER JOIN cfdi_complementoPago cp ON cp.id = cpd.idComplementoPago
                        WHERE cp.estatus = 1
                        GROUP BY cpd.idCompra
                    ) cpp ON cpp.idCompra = c.id
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

    public function listaAprobacionesCFDI($filtros = [], $orden = 'DESC')
    {
        self::$debug = 0;
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }

        $params = [];
        $whereFactura = [];
        $whereNC = [];
        $whereCP = [];
        $tipoCFDI = $filtros['tipoCFDI'] ?? '';

        try {
            if (!in_array($orden, ['DESC', 'ASC'])) {
                throw new \Exception('El orden debe ser DESC o ASC.');
            } else {
                $orden = strtoupper($orden);
            }

            if (!empty($filtros['idProveedor'])) {
                $params[':idProveedor_fact'] = $filtros['idProveedor'];
                $params[':idProveedor_nc'] = $filtros['idProveedor'];
                $params[':idProveedor_cp'] = $filtros['idProveedor'];
                $whereFactura[] = 'c.idProveedor = :idProveedor_fact';
                $whereNC[] = 'c.idProveedor = :idProveedor_nc';
                $whereCP[] = 'c.idProveedor = :idProveedor_cp';
            }

            if (!empty($filtros['tipoMoneda'])) {
                $params[':tipoMoneda_fact'] = $filtros['tipoMoneda'];
                $params[':tipoMoneda_nc'] = $filtros['tipoMoneda'];
                $params[':tipoMoneda_cp'] = $filtros['tipoMoneda'];
                $whereFactura[] = 'c.idCatTipoMoneda = :tipoMoneda_fact';
                $whereNC[] = 'nc.idCatTipoMoneda = :tipoMoneda_nc';
                $whereCP[] = 'cp.moneda = :tipoMoneda_cp';
            }

            if (!empty($filtros['nacional'])) {
                $whereFactura[] = "pv.pais = 'MX'";
                $whereNC[] = "pv.pais = 'MX'";
                $whereCP[] = "pv.pais = 'MX'";
            }

            if (!empty($filtros['entreFechasRecepcion'])) {
                list($fechaInicial, $fechaFinal) = explode(',', $filtros['entreFechasRecepcion']);
                if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                    throw new \Exception('Las fechas proporcionadas no son válidas.');
                }
                $params[':fechaInicial_fact'] = $fechaInicial;
                $params[':fechaFinal_fact'] = $fechaFinal;
                $params[':fechaInicial_nc'] = $fechaInicial;
                $params[':fechaFinal_nc'] = $fechaFinal;
                $params[':fechaInicial_cp'] = $fechaInicial;
                $params[':fechaFinal_cp'] = $fechaFinal;
                $whereFactura[] = '(c.fechaReg BETWEEN :fechaInicial_fact AND :fechaFinal_fact)';
                $whereNC[] = '(nc.fechaReg BETWEEN :fechaInicial_nc AND :fechaFinal_nc)';
                $whereCP[] = '(cp.fechaReg BETWEEN :fechaInicial_cp AND :fechaFinal_cp)';
            }

            if (!empty($filtros['entreFechasPago'])) {
                list($fechaInicial, $fechaFinal) = explode(',', $filtros['entreFechasPago']);
                if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                    throw new \Exception('Las fechas proporcionadas no son válidas.');
                }
                $params[':fechaPagoInicial_fact'] = $fechaInicial;
                $params[':fechaPagoFinal_fact'] = $fechaFinal;
                $params[':fechaPagoInicial_nc'] = $fechaInicial;
                $params[':fechaPagoFinal_nc'] = $fechaFinal;
                $params[':fechaPagoInicial_cp'] = $fechaInicial;
                $params[':fechaPagoFinal_cp'] = $fechaFinal;
                $whereFactura[] = '(c.fechaProbablePago BETWEEN :fechaPagoInicial_fact AND :fechaPagoFinal_fact)';
                $whereNC[] = '(nc.fechaReg BETWEEN :fechaPagoInicial_nc AND :fechaPagoFinal_nc)';
                $whereCP[] = '(cp.fechaReg BETWEEN :fechaPagoInicial_cp AND :fechaPagoFinal_cp)';
            }

            $whereFacturaSQL = !empty($whereFactura) ? implode(' AND ', $whereFactura) : '1=1';
            $whereNCSQL = !empty($whereNC) ? implode(' AND ', $whereNC) : '1=1';
            $whereCPSQL = !empty($whereCP) ? implode(' AND ', $whereCP) : '1=1';

            $selects = [];
            if (empty($tipoCFDI) || $tipoCFDI === 'FACT') {
                $selects[] = "
                    SELECT
                        c.id AS acuse,
                        'FACT' AS tipoRegistro,
                        c.claseDocto,
                        dc.ordenCompra,
                        GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                        c.fechaReg,
                        c.referencia,
                        cf.monto AS total,
                        c.estatus,
                        pv.id AS IdProveedor,
                        pv.razonSocial AS RazonSocial,
                        pv.rfc AS RFC
                    FROM compras c
                    INNER JOIN proveedores pv ON c.idProveedor = pv.id
                    INNER JOIN detcompras dc ON c.id = dc.idCompra
                    LEFT JOIN cfdi_facturas cf ON cf.idCompra = c.id
                    WHERE $whereFacturaSQL AND c.estatus = 1
                    GROUP BY c.id, c.claseDocto, dc.ordenCompra, c.fechaReg, c.referencia, pv.id, pv.razonSocial, pv.rfc";
            }

            if (empty($tipoCFDI) || $tipoCFDI === 'NC') {
                $selects[] = "
                    SELECT
                        c.id AS acuse,
                        'NC' AS tipoRegistro,
                        c.claseDocto,
                        dc.ordenCompra,
                        GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                        nc.fechaReg,
                        CONCAT(nc.serie, nc.folio) AS referencia,
                        nc.total AS total,
                        nc.estatus,
                        pv.id AS IdProveedor,
                        pv.razonSocial AS RazonSocial,
                        pv.rfc AS RFC
                    FROM cfdi_notasCreditos nc
                    INNER JOIN compras c ON nc.idCompra = c.id
                    INNER JOIN proveedores pv ON c.idProveedor = pv.id
                    INNER JOIN detcompras dc ON c.id = dc.idCompra
                    WHERE $whereNCSQL AND nc.estatus = 1
                    GROUP BY nc.id, c.id, c.claseDocto, dc.ordenCompra, nc.fechaReg, nc.serie, nc.folio, nc.estatus, pv.id, pv.razonSocial, pv.rfc";
            }

            if (empty($tipoCFDI) || $tipoCFDI === 'CP') {
                $selects[] = "
                    SELECT
                        c.id AS acuse,
                        'CP' AS tipoRegistro,
                        c.claseDocto,
                        dc.ordenCompra,
                        GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                        cp.fechaReg,
                        CONCAT(cp.serie, cp.folio) AS referencia,
                        cp.montoTotalPagos AS total,
                        cp.estatus,
                        pv.id AS IdProveedor,
                        pv.razonSocial AS RazonSocial,
                        pv.rfc AS RFC
                    FROM cfdi_complementoPago cp
                    INNER JOIN cfdi_complementoPagoDet cpd ON cp.id = cpd.idComplementoPago
                    INNER JOIN compras c ON cpd.idCompra = c.id
                    INNER JOIN proveedores pv ON c.idProveedor = pv.id
                    INNER JOIN detcompras dc ON c.id = dc.idCompra
                    WHERE $whereCPSQL AND cp.estatus = 1
                    GROUP BY cp.id, c.id, c.claseDocto, dc.ordenCompra, cp.fechaReg, cp.serie, cp.folio, cp.estatus, pv.id, pv.razonSocial, pv.rfc";
            }

            if (empty($selects)) {
                throw new \Exception('No se encontraron filtros válidos.');
            }

            // Usar solo los parámetros del tipo solicitado para evitar HY093
            $paramsUsed = [];
            if (empty($tipoCFDI) || $tipoCFDI === 'FACT') {
                foreach ($params as $k => $v) {
                    if (str_ends_with($k, '_fact')) {
                        $paramsUsed[$k] = $v;
                    }
                }
            }
            if (empty($tipoCFDI) || $tipoCFDI === 'NC') {
                foreach ($params as $k => $v) {
                    if (str_ends_with($k, '_nc')) {
                        $paramsUsed[$k] = $v;
                    }
                }
            }
            if (empty($tipoCFDI) || $tipoCFDI === 'CP') {
                foreach ($params as $k => $v) {
                    if (str_ends_with($k, '_cp')) {
                        $paramsUsed[$k] = $v;
                    }
                }
            }
            $params = $paramsUsed;

            $sql = "
                SELECT * FROM (
                    " . implode(' UNION ALL ', $selects) . "
                ) t
                ORDER BY t.fechaReg $orden";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista de Aprobaciones CFDI (FACT/NC/CP)');
            }

            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cantRes = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($result);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantRes, 'data' => $result];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error en listaAprobacionesCFDI: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar aprobaciones CFDI: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas al listar aprobaciones CFDI, notifica a tu administrador.'];
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

                        case 'estatusPagado':
                            if ($valorFiltro === 0) {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'] . ' IS NULL';
                            } elseif ($valorFiltro === 1) {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'] . ' IS NOT NULL';
                            } else {
                                throw new \Exception('El valor de estatusPagado debe ser 0 o 1.');
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
        if (empty($idUser) || empty($acuse)) {
            return ['success' => false, 'message' => 'Se requiere No. de Acuse.'];
        } else {
            try {
                if ($_SESSION['EQXAdmin']) {
                    $validaUsuario = '';
                } else {
                    $validaUsuario = "AND c.idProveedor = $idUser";
                }

                $sql = "SELECT c.id AS acuse, c.sociedad, c.claseDocto, c.estatus AS CpaEstatus, c.fechaVal, c.comentRegresa, c.subTotal, c.idCatTipoMoneda AS CpaTipoMoneda,
                            c.idProveedor, c.notaCredito, c.totalPagos,	c.totalComplementos, c.fechaReg, c.referencia, c.fechaVence AS 'FechaVence', c.fechaProbablePago AS 'FechaProbablePago',
                            dcp.ordenCompra, dcp.noRecepcion, cf.totalImpuestosTrasladados, cf.totalImpuestosRetenidos,
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
                        LEFT JOIN (
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

                // Obtener todas las notas de crédito relacionadas a esta compra
                $notasCredito = [];
                if (!empty($comprasresult['acuse'])) {
                    $sqlNotas = "SELECT 
                                    nc.id,
                                    nc.uuid,
                                    nc.serie,
                                    nc.folio,
                                    nc.urlPDF,
                                    nc.urlXML,
                                    nc.estatus,
                                    nc.total,
                                    nc.subtotal,
                                    nc.idCatTipoMoneda AS moneda,
                                    nc.fechaReg,
                                    nc.fechaPago,
                                    nc.formaDePago,
                                    nc.numOperacion,
                                    nc.uuidRelacionado
                                FROM cfdi_notasCreditos nc
                                WHERE nc.idCompra = :idCompra AND nc.estatus > 0
                                ORDER BY nc.fechaReg DESC";

                    $stmtNotas = $this->db->prepare($sqlNotas);
                    $stmtNotas->bindParam(':idCompra', $acuse, PDO::PARAM_INT);
                    $stmtNotas->execute();
                    $notasCredito = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

                    if (self::$debug) {
                        echo '<br>Resultado de Notas de Crédito:';
                        var_dump($notasCredito);
                        echo '<br><br>';
                    }
                }

                // Obtener todos los complementos de pago relacionados a esta compra
                $complementosPago = [];
                if (!empty($comprasresult['acuse'])) {
                    $sqlComplementos = "SELECT
                                            cpg.id AS idComplemento,
                                            cpg.uuid,
                                            cpg.serie,
                                            cpg.folio,
                                            cpg.urlPDF,
                                            cpg.urlXML,
                                            cpg.estatus,
                                            cpg.total,
                                            cpg.subtotal,
                                            cpg.moneda,
                                            cpg.fecha,
                                            cpg.fechaReg,
                                            cpg.montoTotalPagos,
                                            cpd.id AS idDetalle,
                                            cpd.fechaPago,
                                            cpd.formaPago,
                                            cpd.totalPagado,
                                            cpd.idCatTipoMoneda,
                                            cpd.tipoCambio,
                                            cpd.uuidFact,
                                            cpd.serie AS serieFact,
                                            cpd.folio AS folioFact,
                                            cpd.monedaDR,
                                            cpd.noParcialidad,
                                            cpd.saldoAnterior,
                                            cpd.importePagado,
                                            cpd.saldoInsoluto
                                        FROM cfdi_facturas cf
                                        INNER JOIN cfdi_complementoPagoDet cpd ON cf.uuid = cpd.uuidFact
                                        INNER JOIN cfdi_complementoPago cpg ON cpd.idComplementoPago = cpg.id
                                        WHERE cf.idCompra = :idCompra
                                        ORDER BY cpg.fechaReg DESC, cpd.noParcialidad DESC";

                    $stmtComplementos = $this->db->prepare($sqlComplementos);
                    $stmtComplementos->bindParam(':idCompra', $acuse, PDO::PARAM_INT);
                    $stmtComplementos->execute();
                    $complementosRows = $stmtComplementos->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($complementosRows as $row) {
                        $idComplemento = $row['idComplemento'];
                        if (!isset($complementosPago[$idComplemento])) {
                            $complementosPago[$idComplemento] = [
                                'id' => $row['idComplemento'],
                                'uuid' => $row['uuid'],
                                'serie' => $row['serie'],
                                'folio' => $row['folio'],
                                'urlPDF' => $row['urlPDF'],
                                'urlXML' => $row['urlXML'],
                                'estatus' => $row['estatus'],
                                'total' => $row['total'],
                                'subtotal' => $row['subtotal'],
                                'moneda' => $row['moneda'],
                                'fecha' => $row['fecha'],
                                'fechaReg' => $row['fechaReg'],
                                'montoTotalPagos' => $row['montoTotalPagos'],
                                'detalles' => []
                            ];
                        }
                        $complementosPago[$idComplemento]['detalles'][] = [
                            'idDetalle' => $row['idDetalle'],
                            'fechaPago' => $row['fechaPago'],
                            'formaPago' => $row['formaPago'],
                            'totalPagado' => $row['totalPagado'],
                            'idCatTipoMoneda' => $row['idCatTipoMoneda'],
                            'tipoCambio' => $row['tipoCambio'],
                            'uuidFact' => $row['uuidFact'],
                            'serieFact' => $row['serieFact'],
                            'folioFact' => $row['folioFact'],
                            'monedaDR' => $row['monedaDR'],
                            'noParcialidad' => $row['noParcialidad'],
                            'saldoAnterior' => $row['saldoAnterior'],
                            'importePagado' => $row['importePagado'],
                            'saldoInsoluto' => $row['saldoInsoluto']
                        ];
                    }

                    if (self::$debug) {
                        echo '<br>Resultado de Complementos de Pago:';
                        var_dump($complementosPago);
                        echo '<br><br>';
                    }
                }

                // Agregar el array de notas de crédito al resultado
                $comprasresult['notasCredito'] = $notasCredito;
                $comprasresult['complementosPago'] = array_values($complementosPago);

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

    public function dataCompras($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {
        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
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
            'complementosPendientes' => ['tipoDato' => 'INT', 'sqlFiltro' => ''] //0 si no tiene complementos, 1 si tiene complementos
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
                        case 'entreFechasRecepcion':
                        case 'entreFechasPago':
                            list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                            if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                                throw new \Exception('Las fechas proporcionadas no son válidas.');
                            }
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':fechaInicial'] = $fechaInicial;
                            $params[':fechaFinal'] = $fechaFinal;
                            break;

                        case 'pendientePago':
                            if ($valorFiltro == 1) {
                                $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            }
                            break;

                        case 'pagada':
                            $filtrosSQL .= $valorFiltro == 1 ? ' AND c.totalPagos > 0' : ' AND c.totalPagos = 0';
                            break;

                        case 'complementosPendientes':
                            $filtrosSQL .= $valorFiltro == 1 ? ' AND c.totalPagos > c.totalComplementos' : ' AND c.totalComplementos >= c.totalPagos';
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

            $sql = "SELECT COUNT(c.id) AS cantCompras
                        FROM compras c
                        WHERE $filtrosSQL";
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Lista ultimas Compras');
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $comprasresult = $stmt->fetch(PDO::FETCH_ASSOC);

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
                echo "Error al listar Compras: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al buscar este Documento, Notifica a tu administrador.'];
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

    public function buscarFacturasPorOC($ordenCompra, $idsNotaCredito = [])
    {
        self::$debug = 0;
        if (self::$debug) {
            echo "Buscando facturas para la OC: $ordenCompra";
            echo "<br>Ids de Notas de Crédito a filtrar: ";
            var_dump($idsNotaCredito);
        }

        try {
            if (empty($ordenCompra)) {
                throw new \Exception('El número de Orden de Compra no puede estar vacío.');
            }

            // Construir la consulta base
            $sql = "SELECT DISTINCT
                        cf.idCompra,
                        cf.serie,
                        cf.folio
                    FROM
                        detcompras AS dc
                    INNER JOIN
                        cfdi_facturas AS cf ON dc.idCompra = cf.idCompra
                    WHERE
                        dc.ordenCompra = :ordenCompra AND cf.estatus > 0";

            $params = [':ordenCompra' => $ordenCompra];

            // Si hay políticas de NC disponibles, excluir facturas que ya tienen NC registrada con esos IdNotaCredito
            // IMPORTANTE: idNCExterno puede contener múltiples IDs separados por coma (ej: "1,2,3")
            if (!empty($idsNotaCredito) && is_array($idsNotaCredito)) {
                // Filtrar valores válidos
                $idsNotaCredito = array_filter(array_map('intval', $idsNotaCredito));

                if (!empty($idsNotaCredito)) {
                    // Construir condiciones para cada ID usando FIND_IN_SET
                    // FIND_IN_SET busca un valor dentro de una lista separada por comas
                    $findInSetConditions = [];
                    foreach ($idsNotaCredito as $index => $id) {
                        $placeholder = ":idNC_" . $index;
                        $params[$placeholder] = $id;
                        $findInSetConditions[] = "FIND_IN_SET(" . $placeholder . ", nc.idNCExterno) > 0";
                    }

                    // Excluir facturas que tienen una NC activa con idNCExterno que contiene alguno de los IdNotaCredito
                    // Usamos NOT EXISTS para verificar que no existe ninguna NC activa con esos idNCExterno
                    // FIND_IN_SET permite buscar valores dentro de campos que contienen listas separadas por coma
                    $sql .= " AND NOT EXISTS (
                        SELECT 1 
                        FROM cfdi_notasCreditos AS nc 
                        WHERE nc.idCompra = cf.idCompra 
                            AND nc.estatus = 1 
                            AND nc.idNCExterno IS NOT NULL
                            AND nc.idNCExterno != ''
                            AND (" . implode(' OR ', $findInSetConditions) . ")
                    )";
                }
            }

            $sql .= " ORDER BY cf.idCompra ASC";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Buscar Facturas por OC (filtradas por NC)');
            }

            $stmt = $this->db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $result];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php -> Error en buscarFacturasPorOC: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al buscar facturas por OC: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas al buscar las facturas por OC, notifica a tu administrador.'];
        }
    }

    public function recalcularComplementosPorCompra(INT $idCompra, array $estatuses = ['1', '2'])
    {
        self::$debug = 0;
        if (empty($idCompra)) {
            return ['success' => false, 'message' => 'El idCompra es requerido.'];
        }
        if (empty($estatuses)) {
            return ['success' => false, 'message' => 'Se requiere al menos un estatus para recalcular complementos.'];
        }

        try {
            $placeholders = [];
            $params = [
                ':idCompra' => $idCompra,
                ':idCompra_2' => $idCompra
            ];
            foreach (array_values($estatuses) as $index => $estatus) {
                $key = ':estatus_' . $index;
                $placeholders[] = $key;
                $params[$key] = (string)$estatus;
            }

            $sqlTotales = "SELECT
                                COALESCE(SUM(cpd.importePagado), 0) AS totalComplementos,
                                COALESCE(MIN(cpd.saldoInsoluto), 0) AS insolutoPendiente
                           FROM cfdi_complementoPagoDet cpd
                           INNER JOIN cfdi_complementoPago cp ON cpd.idComplementoPago = cp.id
                           LEFT JOIN cfdi_facturas cf ON cpd.uuidFact = cf.uuid
                           WHERE cp.estatus IN (" . implode(', ', $placeholders) . ")
                             AND (cpd.idCompra = :idCompra OR (cpd.idCompra IS NULL AND cf.idCompra = :idCompra_2))";

            if (self::$debug) {
                $this->db->imprimirConsulta($sqlTotales, $params, 'Recalcular Complementos (totales)');
            }

            $stmt = $this->db->prepare($sqlTotales);
            if ($stmt->execute($params) === false) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception($errorInfo[2] ?? 'Error al ejecutar el cálculo de complementos.');
            }
            $totales = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['totalComplementos' => 0, 'insolutoPendiente' => 0];

            $sqlUpdate = "UPDATE compras
                          SET totalComplementos = :totalComplementos,
                              insolutoPendiente = :insolutoPendiente
                          WHERE id = :idCompra";

            $paramsUpdate = [
                ':totalComplementos' => $totales['totalComplementos'] ?? 0,
                ':insolutoPendiente' => $totales['insolutoPendiente'] ?? 0,
                ':idCompra' => $idCompra
            ];

            if (self::$debug) {
                $this->db->imprimirConsulta($sqlUpdate, $paramsUpdate, 'Recalcular Complementos (update compras)');
            }

            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute($paramsUpdate);

            return ['success' => true, 'message' => 'Complementos recalculados correctamente.'];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            $extra = '';
            if (isset($stmt) && $stmt instanceof \PDOStatement) {
                $errorInfo = $stmt->errorInfo();
                if (!empty($errorInfo[2])) {
                    $extra = " | SQL error: " . $errorInfo[2];
                }
            }
            if (isset($stmtUpdate) && $stmtUpdate instanceof \PDOStatement) {
                $errorInfo = $stmtUpdate->errorInfo();
                if (!empty($errorInfo[2])) {
                    $extra .= " | SQL update error: " . $errorInfo[2];
                }
            }
            error_log("[$timestamp] app/Models/compras/Compras_Mdl.php ->Error recalcular complementos: " . $e->getMessage() . $extra, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al recalcular complementos: " . $e->getMessage() . $extra;
            }
            return ['success' => false, 'message' => 'Error al recalcular complementos.'];
        }
    }
}
