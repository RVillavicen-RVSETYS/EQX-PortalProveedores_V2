<?php

namespace App\Models\Facturas;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class HistorialFacturas_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase HistorialFacturas.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function obtenerHistorial($filtroFecha, $filtroProveedor, $filtroEstatusCont, $filtroEstatusComp)
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener el Historial De Facturas.<br>";
        }
        try {

            $sql = "SELECT
                        cp.id AS 'Acuse',
                        cp.fechaReg AS 'FechaReg',
                        cp.estatus AS 'EstatusContable',
                        cp.claseDocto AS 'ClaseDocto',
                        cp.idProveedor AS 'IdProveedor',
                        pvd.pais AS 'Pais',
                        cp.referencia AS 'Referencia',
                        cp.basePago AS 'BasePago',
                        cp.fechaVence AS 'FechaVence',
                        dtcp.OC AS 'OC',
                        dtcp.numsRecep AS 'NumRecep',
                        dtcp.monedaDtCp AS 'TipoMonedaDtcp',
                        fac.uuid AS 'UUID',
                        fac.urlPDF AS 'FacPDF',
                        fac.urlXML AS 'FacXML',
                        fac.rfcReceptor AS 'RfcReceptor',
                        fac.validada AS 'Validada',
                        pvd.nombre AS 'NombreProveedor',
                        fac.id AS 'IdFactura',
                        CONCAT( IFNULL( fac.serie, '' ), IFNULL( fac.folio, '' ) ) AS 'FolioFactura',
                        fac.idCatMetodoPago AS 'MetodoPago',
                        fac.monto AS 'MontoFactura',
                        fac.idCatTipoMoneda AS 'MonedaFactura',
                        dtpg.idFactura AS 'FactComp',
                        dtpg.montoComp AS 'MontoComp',
                        dtpg.monedaComp AS 'MonedaComp',
                        dtpg.cantComp AS 'CantComp',
                        dtpg.insolutos AS 'Insolutos',
                        cp.comentRegresa AS 'ComentRegresa',
                        ntac.id AS 'IdentNotaCred',
                        ntac.uuid AS 'UuidNotaCred',
                        ntac.urlPDF AS 'NotaCredPDF',
                        ntac.urlXML AS 'NotaCredXML',
                        ntac.subtotal AS 'SubTotalNC',
                        ntac.total AS 'TotalNC',
                        ntac.idCatTipoMoneda AS 'IdCatTipoMonedaNC',
                        CONCAT( ntac.serie, ' ', ntac.folio ) AS 'FolioNC',
                        uuidRelacionado,
                        ntac.fechaReg AS 'FechaRegNC',
                        ntac.idCatTipoMoneda AS 'TipoMonedaNotaCred',
                        DATE_FORMAT( cp.fechaReg, '%Y-%m-%d' ) AS 'FechaRegistro',
                    IF
                        ( cta.corto > 0, DATE_FORMAT( DATE_ADD( cp.fechaReg, INTERVAL cta.corto DAY ), '%Y-%m-%d' ), '' ) AS 'FechaPosibleVencimiento' 
                    FROM
                        compras cp
                        INNER JOIN proveedores pvd ON cp.idProveedor = pvd.id
                        INNER JOIN (
                        SELECT
                            dtcmp.idCompra,
                            dtcmp.ordenCompra AS OC,
                            GROUP_CONCAT( DISTINCT dtcmp.noRecepcion ORDER BY dtcmp.noRecepcion ASC SEPARATOR ', ' ) AS numsRecep,
                            SUM( dtcmp.monto ) AS montoTotal,
                            dtcmp.idCatTipoMoneda AS monedaDtCp 
                        FROM
                            detcompras dtcmp 
                        GROUP BY
                            dtcmp.idCompra 
                        ) dtcp ON cp.id = dtcp.idCompra
                        INNER JOIN cfdi_facturas fac ON cp.id = fac.idCompra
                        LEFT JOIN (
                        SELECT
                            dtComPag.uuidFact AS idFactura,
                            COUNT( dtComPag.id ) AS cantComp,
                            SUM( dtComPag.importePagado ) AS montoComp,
                            dtComPag.idCatTipoMoneda AS monedaComp,
                            MIN( dtComPag.saldoInsoluto ) AS insolutos 
                        FROM
                            cfdi_complementoPagoDet dtComPag 
                        GROUP BY
                            dtComPag.uuidFact 
                        ORDER BY
                            dtComPag.saldoInsoluto ASC 
                        ) dtpg ON fac.uuid = dtpg.idFactura 
                        AND cp.estatus = 2
                        LEFT JOIN cfdi_notasCreditos ntac ON cp.id = ntac.idCompra
                        LEFT JOIN conf_tiempoAtencion cta ON cp.idCatTiempoAtencion = cta.id 
                    WHERE
                        cp.id > 0
                        $filtroFecha 
                        $filtroProveedor 
                        $filtroEstatusCont 
                        $filtroEstatusComp";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener El Historial De Facturas:');
            }

            $stmt = $this->db->prepare($sql);
            //$stmt->bindParam('', $nivel, \PDO::PARAM_INT);
            $stmt->execute();

            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                if (self::$debug) {
                    echo "Error Al Obtener El Historial.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener El Historial.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/HistorialFacturas_Mdl.php -> Error Al Listar Facturas Recibidas: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Listar Facturas Recibidas. Notifica a tu administrador'];
        }
    }
}
