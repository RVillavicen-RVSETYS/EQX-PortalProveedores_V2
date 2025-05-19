<?php

class cfdisv40
{
    protected $debug = 0;

    public function leerCfdi_Ingreso($xmlPath, $version)
    {
        $response = [
            'success' => false,
            'version' => $version,
            'message' => '',
            'data' => []
        ];

        try {
            // Cargar el archivo XML
            $xml = simplexml_load_file($xmlPath, null, LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception('No se pudo cargar el archivo XML.');
            }

            // Registrar namespaces del XML
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
            if (isset($namespaces['tfd'])) {
                $xml->registerXPathNamespace('tfd', $namespaces['tfd']);
            }

            // Datos del comprobante
            $comprobante = $xml->attributes();
            $serie = (string) ($comprobante['Serie'] ?? '');
            $folio = (string) ($comprobante['Folio'] ?? '');

            $response['data']['Comprobante'] = [
                'Version' => (string) ($comprobante['Version'] ?? ''),
                'Serie' => (string) ($comprobante['Serie'] ?? ''),
                'Folio' => (string) ($comprobante['Folio'] ?? ''),
                'Fecha' => (string) ($comprobante['Fecha'] ?? ''),
                'FormaPago' => (string) ($comprobante['FormaPago'] ?? ''),
                'CondicionesDePago' => (string) ($comprobante['CondicionesDePago'] ?? ''),
                'SubTotal' => (float) ($comprobante['SubTotal'] ?? 0),
                'Moneda' => (string) ($comprobante['Moneda'] ?? ''),
                'NoCertificado' => (string) ($comprobante['NoCertificado'] ?? ''),
                'TipoCambio' => (float) ($comprobante['TipoCambio'] ?? 0),
                'Total' => (float) ($comprobante['Total'] ?? 0),
                'TipoDeComprobante' => (string) ($comprobante['TipoDeComprobante'] ?? ''),
                'Exportacion' => (string) ($comprobante['Exportacion'] ?? ''),
                'MetodoPago' => (string) ($comprobante['MetodoPago'] ?? ''),
                'LugarExpedicion' => (string) ($comprobante['LugarExpedicion'] ?? '')
            ];

            // Datos del emisor
            $emisor = $xml->xpath('//cfdi:Emisor');
            $response['data']['Emisor'] = $emisor ? [
                'Rfc' => (string) ($emisor[0]['Rfc'] ?? ''),
                'Nombre' => (string) ($emisor[0]['Nombre'] ?? ''),
                'RegimenFiscal' => (string) ($emisor[0]['RegimenFiscal'] ?? '')
            ] : [];

            // Datos del receptor
            $receptor = $xml->xpath('//cfdi:Receptor');
            $response['data']['Receptor'] = $receptor ? [
                'Rfc' => (string) ($receptor[0]['Rfc'] ?? ''),
                'Nombre' => (string) ($receptor[0]['Nombre'] ?? ''),
                'DomicilioFiscalReceptor' => (string) ($receptor[0]['DomicilioFiscalReceptor'] ?? ''),
                'RegimenFiscalReceptor' => (string) ($receptor[0]['RegimenFiscalReceptor'] ?? ''),
                'UsoCFDI' => (string) ($receptor[0]['UsoCFDI'] ?? '')
            ] : [];

            // Conceptos
            $conceptos = [];
            foreach ($xml->xpath('//cfdi:Concepto') as $concepto) {
                $traslados = [];
                foreach ($concepto->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado') as $traslado) {
                    $traslados[] = [
                        'Base' => (float) ($traslado['Base'] ?? 0),
                        'Impuesto' => (string) ($traslado['Impuesto'] ?? ''),
                        'TipoFactor' => (string) ($traslado['TipoFactor'] ?? ''),
                        'TasaOCuota' => (float) ($traslado['TasaOCuota'] ?? 0),
                        'Importe' => (float) ($traslado['Importe'] ?? 0)
                    ];
                }

                $conceptos[] = [
                    'ClaveProdServ' => (string) ($concepto['ClaveProdServ'] ?? ''),
                    'NoIdentificacion' => (string) ($concepto['NoIdentificacion'] ?? ''),
                    'Cantidad' => (float) ($concepto['Cantidad'] ?? 0),
                    'ClaveUnidad' => (string) ($concepto['ClaveUnidad'] ?? ''),
                    'Unidad' => (string) ($concepto['Unidad'] ?? ''),
                    'Descripcion' => (string) ($concepto['Descripcion'] ?? ''),
                    'ValorUnitario' => (float) ($concepto['ValorUnitario'] ?? 0),
                    'Importe' => (float) ($concepto['Importe'] ?? 0),
                    'ObjetoImp' => (string) ($concepto['ObjetoImp'] ?? ''),
                    'Impuestos' => ['Traslados' => $traslados]
                ];
            }
            $response['data']['Conceptos'] = $conceptos;

            // Impuestos generales
            $impuestos = $xml->xpath('//cfdi:Impuestos');
            $response['data']['Impuestos'] = $impuestos ? [
                'TotalImpuestosTrasladados' => (float) ($impuestos[0]['TotalImpuestosTrasladados'] ?? 0),
                'TotalImpuestosRetenidos' => (float) ($impuestos[0]['TotalImpuestosRetenidos'] ?? 0),
                'Traslados' => [],
                'Retenciones' => []
            ] : [];

            if ($impuestos) {
                foreach ($impuestos[0]->xpath('cfdi:Traslados/cfdi:Traslado') as $traslado) {
                    $response['data']['Impuestos']['Traslados'][] = [
                        'Base' => (float) ($traslado['Base'] ?? 0),
                        'Impuesto' => (string) ($traslado['Impuesto'] ?? ''),
                        'TipoFactor' => (string) ($traslado['TipoFactor'] ?? ''),
                        'TasaOCuota' => (float) ($traslado['TasaOCuota'] ?? 0),
                        'Importe' => (float) ($traslado['Importe'] ?? 0)
                    ];
                }
                foreach ($impuestos[0]->xpath('cfdi:Retenciones/cfdi:Retencion') as $retencion) {
                    $response['data']['Impuestos']['Retenciones'][] = [
                        'Impuesto' => (string) ($retencion['Impuesto'] ?? ''),
                        'Importe' => (float) ($retencion['Importe'] ?? 0)
                    ];
                }
            }

            // Complemento Timbre Fiscal
            $timbre = $xml->xpath('//tfd:TimbreFiscalDigital');
            $uuid = $timbre ? (string) ($timbre[0]['UUID'] ?? '') : '';
            $response['data']['TimbreFiscal'] = $timbre ? [
                'UUID' => strtoupper((string) ($timbre[0]['UUID'] ?? '')),
                'FechaTimbrado' => (string) ($timbre[0]['FechaTimbrado'] ?? ''),
                'RfcProvCertif' => (string) ($timbre[0]['RfcProvCertif'] ?? '')
            ] : [];

            // Generar el campo serializado
            if (!empty($serie) || !empty($folio)) {
                $serie = preg_replace('/[^A-Za-z0-9]/', '', $serie);
                $folio = preg_replace('/[^A-Za-z0-9]/', '', $folio);
                $response['data']['Serializado'] = $serie . $folio;
            } elseif (!empty($uuid)) {
                $response['data']['Serializado'] = substr(preg_replace('/[^A-Za-z0-9]/', '', $uuid), 0, 16);
            } else {
                $response['data']['Serializado'] = '';
            }

            $response['success'] = true;
            $response['message'] = 'CFDI leído correctamente.';
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar el XML: ' . $e->getMessage();
        }

        return $response;
    }

    public function leerCfdi_Pago($xmlPath, $version)
    {
        $response = [
            'success' => false,
            'version' => $version,
            'message' => '',
            'data' => []
        ];

        try {
            // Cargar el archivo XML
            $xml = simplexml_load_file($xmlPath, null, LIBXML_NOCDATA);
            if ($xml === false) {
                throw new Exception('No se pudo cargar el archivo XML.');
            }

            // Registrar namespaces del XML
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
            if (isset($namespaces['pago20'])) {
                $xml->registerXPathNamespace('pago20', $namespaces['pago20']);
            }
            if (isset($namespaces['tfd'])) {
                $xml->registerXPathNamespace('tfd', $namespaces['tfd']);
            }

            // Datos del comprobante
            $comprobante = $xml->attributes();
            $serie = (string) ($comprobante['Serie'] ?? '');
            $folio = (string) ($comprobante['Folio'] ?? '');

            $response['data']['Comprobante'] = [
                'Version' => (string) ($comprobante['Version'] ?? ''),
                'Serie' => (string) ($comprobante['Serie'] ?? ''),
                'Folio' => (string) ($comprobante['Folio'] ?? ''),
                'Fecha' => (string) ($comprobante['Fecha'] ?? ''),
                'SubTotal' => (float) ($comprobante['SubTotal'] ?? 0),
                'Moneda' => (string) ($comprobante['Moneda'] ?? ''),
                'NoCertificado' => (string) ($comprobante['NoCertificado'] ?? ''),
                'Total' => (float) ($comprobante['Total'] ?? 0),
                'TipoDeComprobante' => (string) ($comprobante['TipoDeComprobante'] ?? ''),
                'Exportacion' => (string) ($comprobante['Exportacion'] ?? ''),
                'LugarExpedicion' => (string) ($comprobante['LugarExpedicion'] ?? '')
            ];

            // Datos del emisor
            $emisor = $xml->xpath('//cfdi:Emisor');
            $response['data']['Emisor'] = $emisor ? [
                'Rfc' => (string) ($emisor[0]['Rfc'] ?? ''),
                'Nombre' => (string) ($emisor[0]['Nombre'] ?? ''),
                'RegimenFiscal' => (string) ($emisor[0]['RegimenFiscal'] ?? '')
            ] : [];

            // Datos del receptor
            $receptor = $xml->xpath('//cfdi:Receptor');
            $response['data']['Receptor'] = $receptor ? [
                'Rfc' => (string) ($receptor[0]['Rfc'] ?? ''),
                'Nombre' => (string) ($receptor[0]['Nombre'] ?? ''),
                'DomicilioFiscalReceptor' => (string) ($receptor[0]['DomicilioFiscalReceptor'] ?? ''),
                'RegimenFiscalReceptor' => (string) ($receptor[0]['RegimenFiscalReceptor'] ?? ''),
                'UsoCFDI' => (string) ($receptor[0]['UsoCFDI'] ?? '')
            ] : [];

            // Complemento de pagos
            $pagos = $xml->xpath('//cfdi:Complemento/pago20:Pagos');
            if ($pagos) {
                $pagoData = [];
                foreach ($pagos[0]->xpath('pago20:Pago') as $pago) {
                    $doctosRelacionados = [];
                    foreach ($pago->xpath('pago20:DoctoRelacionado') as $docto) {
                        $impuestosDR = [];
                        foreach ($docto->xpath('pago20:ImpuestosDR/pago20:TrasladosDR/pago20:TrasladoDR') as $trasladoDR) {
                            $impuestosDR[] = [
                                'BaseDR' => (float) ($trasladoDR['BaseDR'] ?? 0),
                                'ImpuestoDR' => (string) ($trasladoDR['ImpuestoDR'] ?? ''),
                                'TipoFactorDR' => (string) ($trasladoDR['TipoFactorDR'] ?? ''),
                                'TasaOCuotaDR' => (float) ($trasladoDR['TasaOCuotaDR'] ?? 0),
                                'ImporteDR' => (float) ($trasladoDR['ImporteDR'] ?? 0)
                            ];
                        }

                        $doctosRelacionados[] = [
                            'IdDocumento' => strtoupper((string) ($docto['IdDocumento'] ?? '')),
                            'Folio' => (string) ($docto['Folio'] ?? ''),
                            'MonedaDR' => (string) ($docto['MonedaDR'] ?? ''),
                            'NumParcialidad' => (int) ($docto['NumParcialidad'] ?? 0),
                            'ImpSaldoAnt' => (float) ($docto['ImpSaldoAnt'] ?? 0),
                            'ImpPagado' => (float) ($docto['ImpPagado'] ?? 0),
                            'ImpSaldoInsoluto' => (float) ($docto['ImpSaldoInsoluto'] ?? 0),
                            'ImpuestosDR' => $impuestosDR
                        ];
                    }

                    $impuestosP = [];
                    foreach ($pago->xpath('pago20:ImpuestosP/pago20:TrasladosP/pago20:TrasladoP') as $trasladoP) {
                        $impuestosP[] = [
                            'BaseP' => (float) ($trasladoP['BaseP'] ?? 0),
                            'ImpuestoP' => (string) ($trasladoP['ImpuestoP'] ?? ''),
                            'TipoFactorP' => (string) ($trasladoP['TipoFactorP'] ?? ''),
                            'TasaOCuotaP' => (float) ($trasladoP['TasaOCuotaP'] ?? 0),
                            'ImporteP' => (float) ($trasladoP['ImporteP'] ?? 0)
                        ];
                    }

                    $pagoData[] = [
                        'FechaPago' => (string) ($pago['FechaPago'] ?? ''),
                        'FormaDePagoP' => (string) ($pago['FormaDePagoP'] ?? ''),
                        'MonedaP' => (string) ($pago['MonedaP'] ?? ''),
                        'Monto' => (float) ($pago['Monto'] ?? 0),
                        'TipoCambioP' => (float) ($pago['TipoCambioP'] ?? 0),
                        'DoctosRelacionados' => array_map(function ($docto) {
                            $impuestosDR = [];
                            foreach ($docto->xpath('pago20:ImpuestosDR/pago20:TrasladosDR/pago20:TrasladoDR') as $trasladoDR) {
                                $impuestosDR[] = [
                                    'BaseDR' => (float) ($trasladoDR['BaseDR'] ?? 0),
                                    'ImpuestoDR' => (string) ($trasladoDR['ImpuestoDR'] ?? ''),
                                    'TipoFactorDR' => (string) ($trasladoDR['TipoFactorDR'] ?? ''),
                                    'TasaOCuotaDR' => (float) ($trasladoDR['TasaOCuotaDR'] ?? 0),
                                    'ImporteDR' => (float) ($trasladoDR['ImporteDR'] ?? 0)
                                ];
                            }

                            return [
                                'IdDocumento' => strtoupper((string) ($docto['IdDocumento'] ?? '')),
                                'Folio' => (string) ($docto['Folio'] ?? ''),
                                'Serie' => (string) ($docto['Serie'] ?? ''),
                                'MonedaDR' => (string) ($docto['MonedaDR'] ?? ''),
                                'NumParcialidad' => (int) ($docto['NumParcialidad'] ?? 0),
                                'ImpSaldoAnt' => (float) ($docto['ImpSaldoAnt'] ?? 0),
                                'ImpPagado' => (float) ($docto['ImpPagado'] ?? 0),
                                'ImpSaldoInsoluto' => (float) ($docto['ImpSaldoInsoluto'] ?? 0),
                                'ImpuestosDR' => $impuestosDR
                            ];
                        }, $pago->xpath('pago20:DoctoRelacionado')),
                        'ImpuestosP' => $impuestosP
                    ];
                }

                $totales = $pagos[0]->xpath('pago20:Totales');
                $response['data']['Pagos'] = [
                    'Version' => (string) ($pagos[0]['Version'] ?? ''),
                    'Totales' => $totales ? [
                        'MontoTotalPagos' => (float) ($totales[0]['MontoTotalPagos'] ?? 0),
                        'TotalTrasladosBaseIVA0' => (float) ($totales[0]['TotalTrasladosBaseIVA0'] ?? 0),
                        'TotalTrasladosImpuestoIVA0' => (float) ($totales[0]['TotalTrasladosImpuestoIVA0'] ?? 0)
                    ] : [],
                    'Pagos' => $pagoData
                ];
            }

            // Complemento Timbre Fiscal
            $timbre = $xml->xpath('//tfd:TimbreFiscalDigital');
            $uuid = $timbre ? (string) ($timbre[0]['UUID'] ?? '') : '';
            $response['data']['TimbreFiscal'] = $timbre ? [
                'UUID' => strtoupper((string) ($timbre[0]['UUID'] ?? '')),
                'FechaTimbrado' => (string) ($timbre[0]['FechaTimbrado'] ?? ''),
                'RfcProvCertif' => (string) ($timbre[0]['RfcProvCertif'] ?? '')
            ] : [];

            // Generar el campo serializado
            if (!empty($serie) || !empty($folio)) {
                $serie = preg_replace('/[^A-Za-z0-9]/', '', $serie);
                $folio = preg_replace('/[^A-Za-z0-9]/', '', $folio);
                $response['data']['Serializado'] = $serie . $folio;
            } elseif (!empty($uuid)) {
                $response['data']['Serializado'] = substr(preg_replace('/[^A-Za-z0-9]/', '', $uuid), 0, 16);
            } else {
                $response['data']['Serializado'] = '';
            }

            $response['success'] = true;
            $response['message'] = 'CFDI de Pago leído correctamente.';
        } catch (Exception $e) {
            $response['message'] = 'Error al procesar el XML: ' . $e->getMessage();
        }

        return $response;
    }

    public function validarCFDIv1($dataXML) {
        $response = [
            "success" => true,
            "message" => "",
            "isValid" => true,
            "debug" => "",
            "data" => ""
        ];

        // Validar entradas
        if (!is_array($dataXML) || empty($dataXML)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del XML.";
            $response["isValid"] = false;
            $response["data"] = "";
            return $response;
        }

        $errorMessages = [];
        $debugMessages = [];

        // Extraer datos necesarios del $dataXML
        $emisor = $dataXML['Emisor']['Rfc'] ?? '';
        $receptor = $dataXML['Receptor']['Rfc'] ?? '';
        $total = number_format($dataXML['Comprobante']['Total'] ?? 0, 6, '.', '');
        $uuid = $dataXML['TimbreFiscal']['UUID'] ?? '';

        // Validar que los datos necesarios estén presentes
        if (empty($emisor) || empty($receptor) || empty($total) || empty($uuid)) {
            $response["success"] = false;
            $response["message"] = "Faltan datos necesarios para la validación.";
            $response["isValid"] = false;
            return $response;
        }

        // Construir la expresión impresa
        $expresionImpresa = "?re=$emisor&rr=$receptor&tt=$total&id=$uuid";

        // Construir el cuerpo SOAP
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"><soapenv:Header/><soapenv:Body><tem:Consulta><tem:expresionImpresa>?re=' . $emisor . '&amp;rr=' . $receptor . '&amp;tt=' . $total . '&amp;id=' . $uuid . '</tem:expresionImpresa></tem:Consulta></soapenv:Body></soapenv:Envelope>';

        // Debug para inspeccionar el SOAP enviado
        if ($this->debug === 1) {
            echo '<br><br>===============================<br> RESULTADO DE WEBSERVICE<br>';
            //echo '<br>SOAP Enviado: ';
            //echo htmlspecialchars($soap);
            echo '<br>Expresion del CFDI: '.$expresionImpresa;
            echo '<br><br>';
        }

        // Configurar los encabezados
        $headers = [
            'Content-Type: text/xml;charset=utf-8',
            'SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta',
            'Content-length: ' . strlen($soap)
        ];

        // URL del servicio web del SAT
        $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';

        // Inicializar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Ejecutar la solicitud
        $res = curl_exec($ch);

        // Debug para inspeccionar la respuesta del web service
        if ($this->debug === 1) {
            echo 'Respueta del Validador: ';
            var_dump($res);
            echo '<br><br>==============<br>';
        }

        // Obtener el código de estado HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Validar si el servicio responde correctamente
        if ($httpCode >= 400) { // Códigos 4xx y 5xx indican errores
            $response["success"] = false;
            $response["message"] = "El servicio del SAT no está activo en este momento. Intente más tarde.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - HTTP Code: $httpCode. La solicitud no fue exitosa.";
            curl_close($ch);
            return $response;
        }

        // Manejo de errores de cURL
        if ($res === false) {
            $response["success"] = false;
            $response["message"] = "Error en la comunicación con el servicio del SAT.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - cURL: " . curl_error($ch);
            curl_close($ch);
            return $response;
        }

        // Cerrar cURL
        curl_close($ch);

        // Convertir la respuesta a JSON para facilitar su manipulación
        $xml = simplexml_load_string($res);
        $data = $xml->children('s', true)->children('', true)->children('', true);
        $data = json_encode($data->children('a', true), JSON_UNESCAPED_UNICODE);
        $dataObject = json_decode($data);

        // Guardar respuesta completa para depuración
        $response["debug"] .= "Respuesta completa del SAT: " . $data . "<br>";

        // Validar que se pudo decodificar correctamente
        if ($dataObject === null) {
            $response["success"] = false;
            $response["message"] = "Error al procesar la respuesta del SAT.";
            $response["isValid"] = false;
            $response["debug"] .= "ERROR - No se pudo decodificar la respuesta JSON.";
            return $response;
        }

        // Extraer valores directamente desde $dataObject
        $estado = $dataObject->Estado ?? 'Desconocido';
        $codigoEstatus = $dataObject->CodigoEstatus ?? 'Desconocido';
        $esCancelable = $dataObject->EsCancelable ?? 'Desconocido';
        $estatusCancelacion = isset($dataObject->EstatusCancelacion) && is_object($dataObject->EstatusCancelacion) ? 'Sin información' : (string)$dataObject->EstatusCancelacion;
        $validacionEFOS = $dataObject->ValidacionEFOS ?? 'Desconocido';

        // Agregar información de depuración
        $debugMessages[] = "Estado: $estado";
        $debugMessages[] = "Código Estatus: $codigoEstatus";
        $debugMessages[] = "Es Cancelable: " . (is_object($esCancelable) ? json_encode($esCancelable) : $esCancelable);
        $debugMessages[] = "Estatus Cancelación: $estatusCancelacion";
        $debugMessages[] = "Validación EFOS: " . (is_object($validacionEFOS) ? json_encode($validacionEFOS) : $validacionEFOS);

        // Interpretar el estado del CFDI
        switch ($estado) {
            case 'Vigente':
                $response["message"] = "El CFDI está vigente.";
                $response["isValid"] = true;
                break;
            case 'Cancelado':
                $response["message"] = "El CFDI ha sido cancelado.";
                $response["isValid"] = false;
                $errorMessages[] = "El CFDI ha sido cancelado.";
                break;
            case 'No Encontrado':
                $response["message"] = "El CFDI no se encuentra en la base de datos del SAT.";
                $response["isValid"] = false;
                $errorMessages[] = "El CFDI no se encuentra en la base de datos del SAT.";
                break;
            default:
                $response["message"] = "Estado desconocido del CFDI.";
                $response["isValid"] = false;
                $errorMessages[] = "Estado desconocido del CFDI.";
                break;
        }

        // Manejar la validación EFOS
        if ($validacionEFOS === '100') {
            $response["message"] .= " El emisor está en la lista de EFOS.";
            $response["isValid"] = false;
            $errorMessages[] = "El emisor está en la lista de EFOS.";
        } elseif ($validacionEFOS === '200') {
            $debugMessages[] = "El emisor no está en la lista de EFOS.";
        } else {
            $debugMessages[] = "El estado de la validación EFOS es desconocido: " . (is_object($validacionEFOS) ? json_encode($validacionEFOS) : $validacionEFOS);
        }

        // Generar mensajes finales
        if (!empty($errorMessages)) {
            $response["message"] = implode(" ", $errorMessages);
        }
        $response["debug"] .= implode("<br>", $debugMessages);

        // Agregar los datos relevantes al response
        $response["data"] = [
            "CodigoEstatus" => $codigoEstatus,
            "EsCancelable" => $esCancelable,
            "Estado" => $estado,
            "EstatusCancelacion" => $estatusCancelacion,
            "ValidacionEFOS" => $validacionEFOS
        ];

        return $response;
    }
}
