<?php

namespace App\Models\DatosCFDIs;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class RegistroCFDIsv40_Mdl
{
    private $db;
    private static $debug = 1;

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
            
            if (self::$debug) {
                $response["debug"] .= "\nIniciando transacción...";
            }
            // Calcular fecha de vencimiento
            $dias = intval(str_replace(["DAY-", "MONTH-"], "", $dataDeValidacion["dataMontosHES"]["CPago"]));
            $intervalo = strpos($dataDeValidacion["dataMontosHES"]["CPago"], "MONTH") !== false ? "P{$dias}M" : "P{$dias}D";
            $fechaVence = (new \DateTime())->add(new \DateInterval($intervalo))->format('Y-m-d');

            // Insertar en compras
            $sqlCompras = "INSERT INTO compras (idProveedor, subTotal, total, idCatTipoMoneda, sociedad, cPago, estatus, idUserReg, tipoUserReg, fechaReg, fechaVence, claseDocto, referencia, descuento, notaCredito)
                            VALUES (:idProveedor, :subTotal, :total, :idCatTipoMoneda, :sociedad, :cPago, :estatus, :idUserReg, :tipoUserReg, NOW(), :fechaVence, :claseDocto, :referencia, :descuento, :notaCredito)";
            
            if (self::$debug) {
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
                    ":claseDocto" => $dataDeValidacion["dataMontosHES"]["resultQuery"][0]["TipoDocumento"],
                    ":referencia" => empty($dataDeValidacion["dataFactXML"]["serializado"]),
                    ":descuento" => ($dataDeValidacion["dataFactXML"]["Comprobante"]["SubTotal"] > $dataDeValidacion["dataFactXML"]["Comprobante"]["Total"]) ? 1 : 0,
                    ":notaCredito" => ($dataDeValidacion["anticipo"] > 0) ? 1 : 0
                ];
                $this->db->imprimirConsulta($sqlCompras, $params, "Registro de compra");
            }
            
            $stmt = $this->db->prepare($sqlCompras);
            $stmt->execute($params);
            
            $idCompra = $this->db->getConnection()->lastInsertId();
            if (!$idCompra) {
                throw new \Exception("No se pudo registrar la compra.");
            }

            if (self::$debug) {
                $response["debug"] .= "\nCompra registrada con ID: $idCompra";
            }
            
            // Insertar en detCompras
            $valuesInsert = '';
            foreach ($dataDeValidacion["dataMontosHES"]["resultQuery"] as $item) {
                $valuesInsert .= "($idCompra, '{$item["sociedad"]}', {$item["idDetRecepcion"]}, '{$item["OC"]}', '{$item["HES"]}', {$item["subtotal"]}, '{$item["idMoneda"]}', 1, '{$item["CPago"]}', '{$item["TipoDocumento"]}', '{$item["fechaRecepcion"]}', {$item["idDetRecepcion"]}),";
            }
            $valuesInsert = rtrim($valuesInsert, ',');
            if (self::$debug) {
                echo '<br><br> Insert para detCompras: '.$valuesInsert;
            }

            $sqlDetCompras = "INSERT INTO detcompras (idCompra, sociedad, identifMovimiento, ordenCompra, noRecepcion, monto, idCatTipoMoneda, estatus, cPag, claseDocto, fechaDocto, noHes) VALUES $valuesInsert";
            if (self::$debug) {
                $this->db->imprimirConsulta($sqlDetCompras, [], 'Registro de detCompras');
            }
            $stmt = $this->db->prepare($sqlDetCompras);
            $stmt->execute();

            // Insertar en cfdi_facturas
            $sqlFactura = "INSERT INTO cfdi_facturas (uuid, idCompra, rfcEmisor, rfcReceptor, razonSocialEm, monto, subtotal, descuento, idCatTipoMoneda, 
            idCatMetodoPago, idCatFormaPago, fechaFac, usoCfdi, folio, serie, noCertificadoSAT, estatus, idUserReg, fechaReg, reglasNegocio, 
            validada, codigoEstatusSAT, estadoValidaSAT, estadoEFO, serializado, totalImpuestosTrasladados, totalImpuestosRetenidos, regimenFiscEmisor,
            razonSocialRec, regimenFiscRec, exportacion, tipoCambio, version, tipoComprobante)
                            VALUES (:uuid, :idCompra, :rfcEmisor, :rfcReceptor, :razonSocialEm, :monto, :subtotal, :descuento, :idCatTipoMoneda, 
            :idCatMetodoPago, :idCatFormaPago, :fechaFac, :usoCfdi, :folio, :serie, :noCertificadoSAT, :estatus, :idUserReg, NOW(), '1', 
            :validada, :codigoEstatusSAT, :estadoValidaSAT, :estadoEFO, :serializado, :totalImpuestosTrasladados, :totalImpuestosRetenidos, :regimenFiscEmisor,
            :razonSocialRec, :regimenFiscRec, :exportacion, :tipoCambio, :version, :tipoComprobante)";
            
            
            $params = [
                ":estatus" => '2',
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
                ":serializado" => empty($dataDeValidacion["dataFactXML"]["serializado"])
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
            
            // Commit final si todo salió bien
            BD_Connect::commit();
            $response["message"] = "La Factura se ha agregado correctamente con el Acuse: $idCompra. - .$idCFDI";
        } catch (\Exception $e) {
            BD_Connect::rollBack();
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/datosCFDIs/RegistroCFDIsv40_Mdl.php -> Error al registrar la Compra: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al registrar la Compra: " . $e->getMessage();
            }
            $response["success"] = false;
            $response["message"] = "Ocurrió un error al registrar su Factura. Notifica al administrador.";
        }

        return $response;
    }
}
