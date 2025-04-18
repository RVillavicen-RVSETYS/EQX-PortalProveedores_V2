<?php

namespace App\Models\DatosCFDIs;

use PDO;
use BD_Connect;
use App\Globals\Controllers\DocumentosController;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class RegistroCFDIsv40_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la RegistroCFDIsv40_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

    public function registrarCFDI_Ingresosv40($dataDeValidacion)
    {
        $response = ["success" => true, "message" => "", "debug" => ""];

        try {
            // Iniciar transacción
            BD_Connect::beginTransaction();

            //self::$debug = 1;
            $facturaAlmacenada = 0;
            
            if (self::$debug) {
                $response["debug"] .= "\n* Iniciando transacción...<br>";
                echo "<br> * Iniciando transacción...<br>";
                echo "<br> * Datos de validación: <br>";
                var_dump($dataDeValidacion);
            }

            // Calcular fecha de vencimiento
            // Ajustar la zona horaria a la de México
            $timezone = new \DateTimeZone('America/Mexico_City');
            $fechaActual = new \DateTime('now', $timezone);

            $dias = intval(str_replace(["DAY-", "MONTH-"], "", $dataDeValidacion["dataMontosHES"]["CPago"]));
            $intervalo = strpos($dataDeValidacion["dataMontosHES"]["CPago"], "MONTH") !== false ? "P{$dias}M" : "P{$dias}D";
            $fechaVence = (clone $fechaActual)->add(new \DateInterval($intervalo))->format('Y-m-d');
            if (self::$debug) {
                echo "<br> * Fecha de vencimiento calculada: $fechaVence";
                echo "<br> * Tiempo Agregado: $intervalo <br>";
                echo "<br> * Dias de Pago: ".$dataDeValidacion["dataEmpresa"]["diasPago"]." <br>";
                $response["debug"] .= "\n* Fecha de vencimiento calculada: $fechaVence<br>";
            }

            // Revisar en qué día cae $fechaVence
            $diaSemana = (new \DateTime($fechaVence))->format('N'); // 1 (lunes) a 7 (domingo)
            $diasPago = explode(',', $dataDeValidacion["dataEmpresa"]["diasPago"]); // Convertir días de pago a un array

            // Ajustar $fechaPago al día de pago más cercano
            $fechaPago = $fechaVence;
            while (!in_array($diaSemana, $diasPago)) {
                $fechaPago = (new \DateTime($fechaPago))->modify('-1 day')->format('Y-m-d');
                $diaSemana = (new \DateTime($fechaPago))->format('N');
            }

            if (self::$debug) {
                echo "<br> * Fecha de probable de pago ajustada: $fechaPago";
                $response["debug"] .= "\n* Fecha probable de pago ajustada: $fechaPago<br>";
            }

            // Comparar si $fechaPago es menor que la fecha actual
            if ($fechaPago < $fechaActual->format('Y-m-d')) {
                $fechaPago = $fechaVence;
            }

            if (self::$debug) {
                echo "<br> * Fecha Final de pago ajustada: $fechaPago";
                $response["debug"] .= "\n* Fecha de pago ajustada: $fechaPago<br>";
            }


            // Insertar en compras
            $sqlCompras = "INSERT INTO compras (idProveedor, subTotal, total, idCatTipoMoneda, sociedad, cPago, estatus, idUserReg, tipoUserReg, fechaReg, fechaVence, fechaProbablePago, claseDocto, referencia, descuento, notaCredito)
                            VALUES (:idProveedor, :subTotal, :total, :idCatTipoMoneda, :sociedad, :cPago, :estatus, :idUserReg, :tipoUserReg, NOW(), :fechaVence, :fechaProbablePago, :claseDocto, :referencia, :descuento, :notaCredito)";
            
            $params = [
                ":idProveedor" => $dataDeValidacion["dataMontosHES"]["idProveedor"],
                ":subTotal" => $dataDeValidacion["dataFactXML"]["Comprobante"]["SubTotal"],
                ":total" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Total"],
                ":idCatTipoMoneda" => $dataDeValidacion["dataMontosHES"]["idMoneda"],
                ":sociedad" => $dataDeValidacion["dataMontosHES"]["sociedad"],
                ":cPago" => $dataDeValidacion["dataMontosHES"]["CPago"],
                ":estatus" => 1,
                ":idUserReg" => $_SESSION['EQXident'],
                ":tipoUserReg" => ($dataDeValidacion["isAdmin"] == 0) ? "PROV" : "ADMIN",
                ":fechaVence" => $fechaVence,
                ":fechaProbablePago" => $fechaPago,
                ":claseDocto" => $dataDeValidacion["dataMontosHES"]["resultQuery"][0]["TipoDocumento"],
                ":referencia" => $dataDeValidacion["dataFactXML"]["Serializado"],
                ":descuento" => ($dataDeValidacion["dataFactXML"]["Comprobante"]["SubTotal"] > $dataDeValidacion["dataFactXML"]["Comprobante"]["Total"]) ? 1 : 0,
                ":notaCredito" => ($dataDeValidacion["anticipo"] > 0) ? 1 : 0
            ];

            if (self::$debug) {                
                $this->db->imprimirConsulta($sqlCompras, $params, "Registro de compra");
            }
            
            $stmt = $this->db->prepare($sqlCompras);
            $stmt->execute($params);
            
            $idCompra = $this->db->getConnection()->lastInsertId();
            if (!$idCompra) {
                throw new \Exception("No se pudo registrar la compra.");
            }

            if (self::$debug) {
                echo "<br> * Compra registrada con ID: $idCompra <br>";
                $response["debug"] .= "\n* Compra registrada con ID: $idCompra<br>";
            }

            // Almacenar Facturas
            $almacenaDoctos = new DocumentosController();
            //$almacenaDoctos = new \App\Globals\Controllers\DocumentosController();
            if (class_exists('DocumentosController')) {
                if (self::$debug) {
                    echo "<br> * Clase DocumentosController cargada correctamente. <br>";
                }
            } else {
                if (self::$debug) {
                    echo "<br> * Error al cargar la clase DocumentosController. <br>";
                }
            }

            if (method_exists($almacenaDoctos, 'almacenaCFDI')) {
                if (self::$debug) {
                    echo "<br> * Método almacenaCFDI() encontrado. <br>";
                }   
            }

            $idProveedor = $dataDeValidacion["dataMontosHES"]["idProveedor"];
            $sociedad = $dataDeValidacion["dataMontosHES"]["sociedad"];
            $almacenaPDF = $almacenaDoctos->almacenaCFDI($_FILES['facturaPDF']['tmp_name'], 'FACT', $idProveedor, $idCompra, $sociedad,'PDF');
            $almacenaXML = $almacenaDoctos->almacenaCFDI($_FILES['facturaXML']['tmp_name'], 'FACT', $idProveedor, $idCompra, $sociedad,'XML');
            if (self::$debug) {
                echo "<br> * PDF de Factura Registrado: <br>";
                var_dump($almacenaPDF);
                echo "<br><br> * XML de Factura Registrado: <br>";
                var_dump($almacenaXML);
            }

            if (!$almacenaPDF["success"] || !$almacenaXML["success"]) {
                throw new \Exception("Problemas al Almacenar la Factura, Notifica a tu Administrador.");
            } else {
                $urlFacturaPDF = $almacenaPDF["data"]["rutaParaBD"];
                $urlFacturaXML = $almacenaXML["data"]["rutaParaBD"];
                $facturaAlmacenada = 1;

                if (self::$debug) {
                    echo "<br> * Factura almacenada correctamente. <br>";
                    $response["debug"] .= "\n* Fact PDF Almacenada: $urlFacturaPDF<br>";
                    $response["debug"] .= "\n* Fact XML Almacenada: $urlFacturaXML<br>";
                }
            }
            
            // Insertar en detCompras
            $valuesInsert = '';
            foreach ($dataDeValidacion["dataMontosHES"]["resultQuery"] as $item) {
                $valuesInsert .= "($idCompra, '{$item["sociedad"]}', {$item["idDetRecepcion"]}, '{$item["OC"]}', '{$item["HES"]}', {$item["subtotal"]}, '{$item["idMoneda"]}', 1, '{$item["CPago"]}', '{$item["TipoDocumento"]}', '{$item["fechaRecepcion"]}', {$item["idDetRecepcion"]}),";
            }
            $valuesInsert = rtrim($valuesInsert, ',');
            if (self::$debug) {
                echo '<br><br> Insert para detCompras: '.$valuesInsert;
                $response["debug"] .= "\n* Detalle de Entrada Registrada correctamente.<br>";
            }

            $sqlDetCompras = "INSERT INTO detcompras (idCompra, sociedad, identifMovimiento, ordenCompra, noRecepcion, monto, idCatTipoMoneda, estatus, cPag, claseDocto, fechaDocto, noHes) VALUES $valuesInsert";
            if (self::$debug) {
                $this->db->imprimirConsulta($sqlDetCompras, [], 'Registro de detCompras');
            }
            $stmt = $this->db->prepare($sqlDetCompras);
            $stmt->execute();

            // Insertar en cfdi_facturas
            $sqlFactura = "INSERT INTO cfdi_facturas (uuid, idCompra, rfcEmisor, rfcReceptor, razonSocialEm, monto, subtotal, descuento, idCatTipoMoneda, 
            idCatMetodoPago, idCatFormaPago, fechaFac, usoCfdi, folio, serie, noCertificadoSAT, urlXML, urlPDF, estatus, idUserReg, fechaReg, reglasNegocio, 
            validada, codigoEstatusSAT, estadoValidaSAT, estadoEFO, serializado, totalImpuestosTrasladados, totalImpuestosRetenidos, regimenFiscEmisor,
            razonSocialRec, regimenFiscRec, exportacion, tipoCambio, version, tipoComprobante)
                            VALUES (:uuid, :idCompra, :rfcEmisor, :rfcReceptor, :razonSocialEm, :monto, :subtotal, :descuento, :idCatTipoMoneda, 
            :idCatMetodoPago, :idCatFormaPago, :fechaFac, :usoCfdi, :folio, :serie, :noCertificadoSAT, :urlXML, :urlPDF, :estatus, :idUserReg, NOW(), '1', 
            :validada, :codigoEstatusSAT, :estadoValidaSAT, :estadoEFO, :serializado, :totalImpuestosTrasladados, :totalImpuestosRetenidos, :regimenFiscEmisor,
            :razonSocialRec, :regimenFiscRec, :exportacion, :tipoCambio, :version, :tipoComprobante)";
            
            
            $params = [
                ":estatus" => '2',
                ":urlXML" => isset($urlFacturaXML) ? $urlFacturaXML : NULL,
                ":urlPDF" => isset($urlFacturaPDF) ? $urlFacturaPDF : NULL,
                ":uuid" => $dataDeValidacion["dataFactXML"]["TimbreFiscal"]["UUID"],
                ":idCompra" => $idCompra,
                ":rfcEmisor" => $dataDeValidacion["dataFactXML"]["Emisor"]["Rfc"],
                ":razonSocialEm" => $dataDeValidacion["dataFactXML"]["Emisor"]["Nombre"],
                ":regimenFiscEmisor" => $dataDeValidacion["dataFactXML"]["Emisor"]["RegimenFiscal"],
                ":rfcReceptor" => $dataDeValidacion["dataFactXML"]["Receptor"]["Rfc"],
                ":razonSocialRec" => $dataDeValidacion["dataFactXML"]["Receptor"]["Nombre"],
                ":regimenFiscRec" => $dataDeValidacion["dataFactXML"]["Receptor"]["RegimenFiscalReceptor"],
                ":monto" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Total"],
                ":subtotal" => $dataDeValidacion["dataFactXML"]["Comprobante"]["SubTotal"],
                ":descuento" => 0,
                ":idCatTipoMoneda" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Moneda"],
                ":idCatMetodoPago" => $dataDeValidacion["dataFactXML"]["Comprobante"]["MetodoPago"],
                ":idCatFormaPago" => $dataDeValidacion["dataFactXML"]["Comprobante"]["FormaPago"],
                ":fechaFac" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Fecha"],
                ":usoCfdi" => $dataDeValidacion["dataFactXML"]["Receptor"]["UsoCFDI"],
                ":folio" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Folio"],
                ":serie" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Serie"],
                ":exportacion" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Exportacion"],
                ":noCertificadoSAT" => NULL,
                ":idUserReg" => $_SESSION['EQXident'],
                ":tipoCambio" => $dataDeValidacion["dataFactXML"]["Comprobante"]["TipoCambio"],
                ":version" => $dataDeValidacion["dataFactXML"]["Comprobante"]["Version"],
                ":tipoComprobante" => $dataDeValidacion["dataFactXML"]["Comprobante"]["TipoDeComprobante"],
                ":totalImpuestosTrasladados" => $dataDeValidacion["dataFactXML"]["Impuestos"]["TotalImpuestosTrasladados"],
                ":totalImpuestosRetenidos" => $dataDeValidacion["dataFactXML"]["Impuestos"]["TotalImpuestosRetenidos"],
                ":validada" => "2",
                ":codigoEstatusSAT" => $dataDeValidacion["ValidFiscal"]["CodigoEstatus"],
                ":estadoValidaSAT" => $dataDeValidacion["ValidFiscal"]["Estado"],
                ":estadoEFO" => $dataDeValidacion["ValidFiscal"]["ValidacionEFOS"],
                ":serializado" => $dataDeValidacion["dataFactXML"]["Serializado"]
            ];

            if (self::$debug) {
                $this->db->imprimirConsulta($sqlFactura, $params, "Registro de CFDI");
            }
            
            $stmt = $this->db->prepare($sqlFactura);
            $stmt->execute($params);
            
            $idCFDI = $this->db->getConnection()->lastInsertId();
            if (!$idCFDI) {
                throw new \Exception("No se pudo registrar la Factura.");
            }

            if (self::$debug) {
                echo "<br> * Factura registrada con ID: $idCFDI <br>";
                $response["debug"] .= "\n* Factura registrada con ID: $idCFDI <br>";
            }

            // Insertar impuestos trasladados y retenidos
            $sqlImpuestos = "INSERT INTO cfdi_facturasImpuestos (idFactura, idCompra, tipo, impuesto, TipoFactor, TasaOCuota, Base, Importe) VALUES ";
            $valuesImpuestos = [];
            
            foreach ($dataDeValidacion["dataFactXML"]["Impuestos"]["Traslados"] as $impuesto) {
                if (self::$debug) {
                    echo '<br> * Impuesto Traslado: '. $impuesto["Impuesto"] . '--'. $impuesto["TipoFactor"] . '--'. $impuesto["TasaOCuota"] . '--'. $impuesto["Base"] . '--'. $impuesto["Importe"];
                }
                $valuesImpuestos[] = "($idCFDI, '$idCompra', 'Traslado', '{$impuesto["Impuesto"]}', '{$impuesto["TipoFactor"]}', '{$impuesto["TasaOCuota"]}', '{$impuesto["Base"]}', '{$impuesto["Importe"]}')";
            }
            
            foreach ($dataDeValidacion["dataFactXML"]["Impuestos"]["Retenciones"] as $impuesto) {
                echo '<br> * Impuesto Retencion: '. $impuesto["Impuesto"] . '--'. $impuesto["TipoFactor"] . '--'. $impuesto["TasaOCuota"] . '--'. $impuesto["Base"] . '--'. $impuesto["Importe"];
                $valuesImpuestos[] = "($idCFDI, '$idCompra', 'Retencion', '{$impuesto["Impuesto"]}', '{$impuesto["TipoFactor"]}', '{$impuesto["TasaOCuota"]}', '{$impuesto["Base"]}', '{$impuesto["Importe"]}')";
            }
            
            if (!empty($valuesImpuestos)) {
                $sqlImpuestos .= implode(",", $valuesImpuestos);
                $stmt = $this->db->prepare($sqlImpuestos);
                $stmt->execute();
            }
            
            // Commit final si todo salió bien
            BD_Connect::commit();
            $response["message"] = "La Factura se ha agregado correctamente con el Acuse: $idCompra.";
            $response["debug"] .= "\n* Impuestos Registrados correctamente.";
        } catch (\Exception $e) {
            BD_Connect::rollBack();
            $timestamp = date("Y-m-d H:i:s");
            if ($facturaAlmacenada == 1) {
                $borraDocumento = $almacenaDoctos->eliminaDocumento($urlFacturaPDF, 'PDF');
                if ($borraDocumento["success"] == false) {
                    error_log("[$timestamp] app/Models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al borrar la Factura: " . $urlFacturaPDF, 3, LOG_FILE);
                    $response["debug"] .= "\n* Error al eliminar el PDF de la Factura: " . $borraDocumento["message"];
                } else {
                    $response["debug"] .= "\n* PDF de la Factura eliminado correctamente.";
                }
                if (self::$debug) {
                    var_dump($borraDocumento);
                    echo "<br> * Factura PDF eliminada correctamente. <br>";
                }

                $borraDocumento = $almacenaDoctos->eliminaDocumento($urlFacturaXML, 'XML');
                if ($borraDocumento["success"] == false) {
                    error_log("[$timestamp] app/Models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al borrar la Factura: " . $urlFacturaXML, 3, LOG_FILE);
                    $response["debug"] .= "\n* Error al eliminar el XML de la Factura: " . $borraDocumento["message"];
                } else {
                    $response["debug"] .= "\n* XML de la Factura eliminado correctamente.";
                }
                if (self::$debug) {
                    var_dump($borraDocumento);
                    echo "<br> * Factura XML eliminada correctamente. <br>";
                }   
                
                if (self::$debug) {
                    echo "<br> * Facturas eliminada correctamente. <br>";
                    $response["debug"] .= "\n* Facturas eliminada correctamente.<br>";
                }
            }

            error_log("[$timestamp] app/Models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al registrar la Compra: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al registrar la Compra: " . $e->getMessage();
            }
            $response["success"] = false;
            $response["message"] = "Ocurrió un error al registrar su Factura. Notifica al administrador.";
        }

        return $response;
    }
}
