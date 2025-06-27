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
            if ($dataDeValidacion["dataFactXML"]["Comprobante"]["MetodoPago"] == 'PUE') {
                $valorTotalComplementos = $dataDeValidacion["dataFactXML"]["Comprobante"]["Total"];
            } else {
                $valorTotalComplementos = 0;
            }

            // Insertar en compras
            $sqlCompras = "INSERT INTO compras (idProveedor, subTotal, total, idCatTipoMoneda, sociedad, cPago, estatus, idUserReg, tipoUserReg, fechaReg, fechaVence, fechaProbablePago, claseDocto, referencia, descuento, notaCredito, totalComplementos)
                            VALUES (:idProveedor, :subTotal, :total, :idCatTipoMoneda, :sociedad, :cPago, :estatus, :idUserReg, :tipoUserReg, NOW(), :fechaVence, :fechaProbablePago, :claseDocto, :referencia, :descuento, :notaCredito, :totalComplementos)";
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
                ":notaCredito" => ($dataDeValidacion["anticipo"] > 0) ? 1 : 0,
                ":totalComplementos" => $valorTotalComplementos
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

    public function registrarCFDI_Pagosv40($dataDeValidacion)
    {
        $response = ["success" => true, "message" => "", "debug" => ""];

        try {
            // Iniciar transacción
            BD_Connect::beginTransaction();

            //self::$debug = 1;
            $complementoAlmacenado = 0;
            
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
            $idProveedor = $dataDeValidacion["dataProv"]["IdProveedor"];
            $sociedad = $dataDeValidacion["dataEmpresa"]["id"];

            // Insertar en cfdi_complementoPago
            //id	idProveedor	uuid	estatus	montoTotalPago	monto	serie	folio	version	tipoFactura	rfcEmisor	rfcReceptor	fechaPago	selloCFDI	selloSAT	formaDePago	numOperacion	urlPDF	urlXML	idUserReg	fechaReg	moneda	tipoCambioP	domicilioRec	domicilioEmisor	regimenFiscEmisor	razonSocialRec	razonSocialEm	regimenFiscRec	usoCFDI
            $sqlComplemento = "INSERT INTO cfdi_complementoPago (idProveedor, uuid, estatus, subtotal, total, montoTotalPagos, montoTotalTrasladobaseIVA, montoTotalTrasladoImpuestoIVA, moneda, serie, 
                            folio, version, tipoFactura, rfcEmisor, rfcReceptor, fecha, exportacion, noCertificado, idUserReg, fechaReg, domicilioRec, 
                            LugarExpedicion, regimenFiscEmisor, razonSocialRec, razonSocialEm, regimenFiscRec, usoCFDI, reglasNegocio, validada, codigoEstatusSAT, estadoValidaSAT, estadoEFO, serializado)
                                VALUES (:idProveedor, :uuid, :estatus, :subtotal, :total, :montoTotalPagos, :montoTotalTrasladobaseIVA, :montoTotalTrasladoImpuestoIVA, :moneda, :serie,
                            :folio,:version,:tipoFactura,:rfcEmisor,:rfcReceptor,:fecha, :exportacion, :noCertificado, :idUserReg, NOW(),:domicilioRec,
                            :LugarExpedicion,:regimenFiscEmisor,:razonSocialRec,:razonSocialEm, :regimenFiscRec,:usoCFDI, :reglasNegocio, :validada, :codigoEstatusSAT, :estadoValidaSAT, :estadoEFO, :serializado)";
            $paramsComplemento = [
                ":idProveedor" => $idProveedor,
                ":uuid" => $dataDeValidacion["dataComplementoXML"]["TimbreFiscal"]["UUID"],
                ":estatus" => 2,
                ":subtotal" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["SubTotal"],
                ":total" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Total"],
                ":montoTotalPagos" => $dataDeValidacion["dataComplementoXML"]["Pagos"]["Totales"]["MontoTotalPagos"],
                ":montoTotalTrasladobaseIVA" => $dataDeValidacion["dataComplementoXML"]["Pagos"]["Totales"]["TotalTrasladosBaseIVA0"],
                ":montoTotalTrasladoImpuestoIVA" => $dataDeValidacion["dataComplementoXML"]["Pagos"]["Totales"]["TotalTrasladosBaseIVA0"],
                ":moneda" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Moneda"],
                ":serie" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Serie"],
                ":folio" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Folio"],
                ":version" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Version"],
                ":tipoFactura" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["TipoDeComprobante"],
                ":rfcEmisor" => $dataDeValidacion["dataComplementoXML"]["Emisor"]["Rfc"],
                ":rfcReceptor" => $dataDeValidacion["dataComplementoXML"]["Receptor"]["Rfc"],
                ":fecha" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Fecha"],
                ":exportacion" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["Exportacion"],
                ":noCertificado" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["NoCertificado"],
                ":idUserReg" => $_SESSION['EQXident'],
                ":domicilioRec" => $dataDeValidacion["dataComplementoXML"]["Receptor"]["DomicilioFiscalReceptor"],
                ":LugarExpedicion" => $dataDeValidacion["dataComplementoXML"]["Comprobante"]["LugarExpedicion"],
                ":regimenFiscEmisor" => $dataDeValidacion["dataComplementoXML"]["Emisor"]["RegimenFiscal"],
                ":razonSocialRec" => $dataDeValidacion["dataComplementoXML"]["Receptor"]["Nombre"],
                ":razonSocialEm" => $dataDeValidacion["dataComplementoXML"]["Emisor"]["Nombre"],
                ":regimenFiscRec" => $dataDeValidacion["dataComplementoXML"]["Receptor"]["RegimenFiscalReceptor"],
                ":usoCFDI" => $dataDeValidacion["dataComplementoXML"]["Receptor"]["UsoCFDI"], 
                ":reglasNegocio" => 1,
                ":validada" => 2,
                ":codigoEstatusSAT" => $dataDeValidacion["ValidFiscal"]["CodigoEstatus"],
                ":estadoValidaSAT" => $dataDeValidacion["ValidFiscal"]["Estado"], 
                ":estadoEFO" => $dataDeValidacion["ValidFiscal"]["ValidacionEFOS"], 
                ":serializado" => $dataDeValidacion["dataComplementoXML"]["Serializado"]
            ];            

            if (self::$debug) {
                $this->db->imprimirConsulta($sqlComplemento, $paramsComplemento, "Registro de Complemento de Pago");
            }
            
            $stmt = $this->db->prepare($sqlComplemento);
            $stmt->execute($paramsComplemento);
            
            $idComplemento = $this->db->getConnection()->lastInsertId();
            if (!$idComplemento) {
                throw new \Exception("No se pudo registrar el Complemento de Pago.");
            }

            if (self::$debug) {
                echo "<br> * Complemento registrado con ID: $idComplemento <br>";
                $response["debug"] .= "\n* Complemento registrado con ID: $idComplemento<br>";
            }

            // Almacenar Complemento de Pago
            $almacenaDoctos = new DocumentosController();

            $doctoPDF = $dataDeValidacion['documentos']['ComplementoPDF']['tmp_name'];
            $doctoXML = $dataDeValidacion['documentos']['ComplementoXML']['tmp_name'];
            $almacenaPDF = $almacenaDoctos->almacenaCFDI($doctoPDF, 'COMPPAG', $idProveedor, $idComplemento, $sociedad,'PDF');
            $almacenaXML = $almacenaDoctos->almacenaCFDI($doctoXML, 'COMPPAG', $idProveedor, $idComplemento, $sociedad,'XML');
            if (self::$debug) {
                echo "<br> * PDF de Complemento de Pago Registrado: <br>";
                var_dump($almacenaPDF);
                echo "<br><br> * XML de Complemento de Pago Registrado: <br>";
                var_dump($almacenaXML);
            }

            if (!$almacenaPDF["success"] || !$almacenaXML["success"]) {
                throw new \Exception("Problemas al Almacenar el Complemento de Pago, Notifica a tu Administrador.");
            } else {
                $urlComplementoPDF = $almacenaPDF["data"]["rutaParaBD"];
                $urlComplementoXML = $almacenaXML["data"]["rutaParaBD"];
                $complementoAlmacenado = 1;

                if (self::$debug) {
                    echo "<br> * Factura almacenada correctamente. <br>";
                    $response["debug"] .= "\n* Fact PDF Almacenada: $urlComplementoPDF<br>";
                    $response["debug"] .= "\n* Fact XML Almacenada: $urlComplementoXML<br>";
                }
            }

            // Actualizar la ruta del complemento de pago en la base de datos
            $sqlUpdateComplemento = "UPDATE cfdi_complementoPago SET urlPDF = :urlPDF, urlXML = :urlXML WHERE id = :idComplemento";
            $paramsUpdateComplemento = [
                ":urlPDF" => $urlComplementoPDF,
                ":urlXML" => $urlComplementoXML,
                ":idComplemento" => $idComplemento
            ];
            if (self::$debug) {
                $this->db->imprimirConsulta($sqlUpdateComplemento, $paramsUpdateComplemento, "Actualización de Complemento de Pago");
            }   

            $stmt = $this->db->prepare($sqlUpdateComplemento);
            $stmt->execute($paramsUpdateComplemento);
            
            if ($stmt->rowCount() == 0) {
                throw new \Exception("No se pudo actualizar la ruta del complemento de pago.");
            }

            if (self::$debug) {
                echo "<br> * Ruta del complemento de pago actualizada correctamente. <br>";
                $response["debug"] .= "\n* Ruta del complemento de pago actualizada correctamente.<br>";
            }

            // Insertar en cfdi_complementoPagoDetalle
            $valuesInsert = '';
            $montosPagadosPorUUID = [];
            foreach ($dataDeValidacion["dataComplementoXML"]["Pagos"]["Pagos"] as $pago) {
                $fechaPago = $pago["FechaPago"];
                $formaPago = $pago["FormaDePagoP"];
                $totalPagado = $pago["Monto"];
                $idCatTipoMoneda = $pago["MonedaP"];
                $tipoCambioP = $pago["TipoCambioP"];

                foreach ($pago["DoctosRelacionados"] as $docto) {
                    $uuidFact = $docto["IdDocumento"];
                    $serie = $docto["Serie"];
                    $folio = $docto["Folio"];
                    $monedaDR = $docto["MonedaDR"];
                    $noParcialidad = $docto["NumParcialidad"];
                    $saldoAnterior = $docto["ImpSaldoAnt"];
                    $importePagado = $docto["ImpPagado"];
                    $saldoInsoluto = $docto["ImpSaldoInsoluto"];

                    $valuesInsert .= "($idComplemento, '$fechaPago', '$formaPago', $totalPagado, '$idCatTipoMoneda', '$tipoCambioP', '$uuidFact', '$serie', '$folio',  '$monedaDR', $noParcialidad, $saldoAnterior, $importePagado, $saldoInsoluto), ";
                    if (isset($montosPagadosPorUUID[$uuidFact])) {
                        $montosPagadosPorUUID[$uuidFact]['montoPagado'] += floatval($importePagado);
                        if ($saldoInsoluto < $montosPagadosPorUUID[$uuidFact]['insoluto']) {
                            $montosPagadosPorUUID[$uuidFact]['insoluto'] = floatval($saldoInsoluto);
                        }
                    }  else {
                        $montosPagadosPorUUID[$uuidFact]['montoPagado'] = floatval($importePagado);
                        $montosPagadosPorUUID[$uuidFact]['insoluto'] = floatval($saldoInsoluto);
                    }
                    
                }
            }
            $valuesInsert = rtrim($valuesInsert, ', ');
            if (self::$debug) {
                echo '<br><br> Datos para Insert de cfdi_complementoPagoDetalle: '.$valuesInsert;
            }

            $sqlDetComplemento = "INSERT INTO cfdi_complementoPagoDet(idComplementoPago, fechaPago, formaPago, totalPagado, idCatTipoMoneda, tipoCambio, uuidFact, serie, folio, monedaDR, noParcialidad, saldoAnterior, importePagado, saldoInsoluto) 
                        VALUES $valuesInsert";           
            if (self::$debug) {
                $this->db->imprimirConsulta($sqlDetComplemento, [], 'Registro de cfdi_complementoPagoDetalle');
            }
            $stmt = $this->db->prepare($sqlDetComplemento);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                throw new \Exception("No se pudo registrar el detalle del complemento de pago.");
            }
            if (self::$debug) {
                echo "<br> * Detalle del complemento de pago registrado correctamente. <br>";
                $response["debug"] .= "\n* Detalle del complemento de pago registrado correctamente.<br>";
            }
            
            // Actualizar los montos del Complemento de Pago en la tabla Compras            
            foreach ($montosPagadosPorUUID as $uuid => $data) {
                $montoPagado = $data['montoPagado'];
                $insoluto = $data['insoluto'];
                if (self::$debug) {
                    echo "<br> * UUID: $uuid - Monto Pagado: $montoPagado - Insoluto: $insoluto <br>";
                }
                
                $sqlUpdateMontos = "UPDATE compras c
                    INNER JOIN cfdi_facturas fc ON c.id = fc.idCompra
                    SET c.totalComplementos = c.totalComplementos + :montoPagado, 
                        c.insolutoPendiente = IF(ISNULL(c.insolutoPendiente), :insoluto, if(:insoluto2 < c.insolutoPendiente, :insoluto3, c.insolutoPendiente))
                    WHERE fc.uuid = :uuid";
                $paramsUpdateMontos = [
                    ":montoPagado" => $montoPagado,
                    ":insoluto" => $insoluto,
                    ":insoluto2" => $insoluto,
                    ":insoluto3" => $insoluto,
                    ":uuid" => $uuid
                ];
                if (self::$debug) {
                    $this->db->imprimirConsulta($sqlUpdateMontos, $paramsUpdateMontos, "Actualización de montos del Complemento de Pago en Compras");
                }
                
                $stmt = $this->db->prepare($sqlUpdateMontos);
                $stmt->execute($paramsUpdateMontos);
            }
            if (self::$debug) {
                echo "<br> * Montos del Complemento de Pago actualizados correctamente en Compras. <br>";
                $response["debug"] .= "\n* Montos del Complemento de Pago actualizados correctamente en Compras.<br>";
            }

            
            // Commit final si todo salió bien
            BD_Connect::commit();
            $response["message"] = "El Complemento de Pago se ha agregado correctamente con el Acuse: $idComplemento.";
            $response["debug"] .= "\n* Complemento de Pago registrado correctamente.";
        } catch (\Exception $e) {
            BD_Connect::rollBack();
            $timestamp = date("Y-m-d H:i:s");
            if ($complementoAlmacenado == 1) {
                $borraDocumento = $almacenaDoctos->eliminaDocumento($urlComplementoPDF, 'PDF');
                if ($borraDocumento["success"] == false) {
                    error_log("[$timestamp] app/Models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al borrar el Complemento: " . $urlComplementoPDF, 3, LOG_FILE);
                    $response["debug"] .= "\n* Error al eliminar el PDF del Complemento : " . $borraDocumento["message"];
                } else {
                    $response["debug"] .= "\n* PDF del Complemento de Pago eliminado correctamente.";
                }
                if (self::$debug) {
                    var_dump($borraDocumento);
                    echo "<br> * Complemento PDF eliminado correctamente. <br>";
                }

                $borraDocumento = $almacenaDoctos->eliminaDocumento($urlComplementoXML, 'XML');
                if ($borraDocumento["success"] == false) {
                    error_log("[$timestamp] app/Models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al borrar el Complemento: " . $urlComplementoXML, 3, LOG_FILE);
                    $response["debug"] .= "\n* Error al eliminar el XML de la Factura: " . $borraDocumento["message"];
                } else {
                    $response["debug"] .= "\n* XML del Complemento de Pago eliminado correctamente.";
                }
                if (self::$debug) {
                    var_dump($borraDocumento);
                    echo "<br> * Complemento XML eliminado correctamente. <br>";
                }   
                
                if (self::$debug) {
                    echo "<br> * Complemento eliminado correctamente. <br>";
                    $response["debug"] .= "\n* Complemento eliminado correctamente.<br>";
                }
            }

            error_log("[$timestamp] app/Models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al registrar el Complemento de Pago: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al registrar el Complemento: " . $e->getMessage();
            }
            $response["success"] = false;
            $response["message"] = "Ocurrió un error al registrar su Complemento. Notifica al administrador.";
        }

        return $response;

    }
}
