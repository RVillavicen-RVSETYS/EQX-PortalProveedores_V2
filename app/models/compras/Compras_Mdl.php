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

    public function listaComprasFacturadas(INT $idProveedor, INT $cantMaxRes = 0)
    {
        $limiteResult = ($cantMaxRes == 0) ? '' : 'LIMIT '.$cantMaxRes;
        try {
            $sql = "SELECT c.id AS acuse, c.claseDocto, dc.ordenCompra, c.estatus,
                    GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                    c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML 
                    FROM compras c
                    INNER JOIN detcompra dc ON c.id = dc.idCompra
                    LEFT JOIN cfdi_facturas cf ON cf.idCompra = c.id
                    WHERE c.idProveedor = :noProveedor
                    GROUP BY c.id, c.claseDocto, dc.ordenCompra, c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML 
                    ORDER BY c.id DESC
                    $limiteResult"; 

            if (self::$debug) {
                $params = [':noProveedor' => $idProveedor];
                $this->db->imprimirConsulta($sql, $params, 'Lista ultimas Compras');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':noProveedor', $idProveedor, PDO::PARAM_INT);
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
            error_log("[$timestamp] app/models/compras/Compras_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD); 
            if (self::$debug) {
                echo "Error al listar Compras: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar las Compras, Notifica a tu administrador.'];
        }
    }

    public function dataCompraPorAcuse(INT $idProveedor, INT $acuse)
    {
        if (empty($idProveedor) || empty($acuse)) {
            return ['success' => false, 'message' => 'Se requiere No. de Acuse.'];
        } else {
            try {
                $sql = "SELECT c.id AS acuse, c.claseDocto, dc.ordenCompra, c.estatus, c.fechaVal, c.comentRegresa, c.subTotal, c.idCatTipoMoneda,
                        c.idProveedor, GROUP_CONCAT(DISTINCT dc.noRecepcion ORDER BY dc.noRecepcion SEPARATOR ', ') AS noRecepcion,
                        c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML, cf.monto, cf.idCatMetodoPago, cf.idCatFormaPago, cf.usoCfdi, cf.uuid,
                        cf.fechaFac, cf.serie, cf.folio, cf.razonSocialEm
                        FROM compras c
                        INNER JOIN detcompra dc ON c.id = dc.idCompra
                        LEFT JOIN cfdi_facturas cf ON cf.idCompra = c.id
                        WHERE c.id = :acuse AND c.idProveedor = :noProveedor
                        GROUP BY c.id, c.claseDocto, dc.ordenCompra, c.fechaReg, c.referencia, cf.urlPDF, cf.urlXML"; 
    
                if (self::$debug) {
                    $params = [':noProveedor' => $idProveedor, ':acuse' => $acuse];
                    $this->db->imprimirConsulta($sql, $params, 'Lista ultimas Compras');
                }
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':noProveedor', $idProveedor, PDO::PARAM_INT);
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
                error_log("[$timestamp] app/models/compras/Compras_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD); 
                if (self::$debug) {
                    echo "Error al listar Compras: " . $e->getMessage(); // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Problemas al buscar este Documento, Notifica a tu administrador.'];
            }
        }        
    }
}