<?php

use App\Models\DatosCFDIs\CFDIs_Mdl;

class ReglasAplicadasv40
{
    protected $debug = 0; // Debug desactivado

    public function validarReglasInternasNacional_Ingresos($dataProveedor, $dataEmpresa, $dataXML)
    {
        $response = [
            "success" => true,
            "message" => "",
            "isValid" => true,
            "debug" => ""
        ];

        // VALIDAR AQUI SI EL UUID DE LA FACTURA YA EXISTE EN LA BD
        if (empty($dataXML['TimbreFiscal']['UUID'])) {
            $response["message"] = "El nodo UUID del Timbre Fiscal no existe o está vacío.";
            $response["debug"] = " * ERROR - El nodo UUID del Timbre Fiscal no está presente en el XML.";
            return $response;
        }

        if ($this->debug == 1) {
            echo "<br>UUID del Timbre Fiscal: " . $dataXML['TimbreFiscal']['UUID'];
            echo "<br>Validamos si la Factura ya existe en la base de datos...";
        }
        $cfdis_Mdl = new CFDIs_Mdl();
        $filtrosFact = [
            'uuids' => $dataXML['TimbreFiscal']['UUID'] ?? null,
        ];
        $obtenerFacturas = $cfdis_Mdl->obtenerFacturasPorUUID($filtrosFact);
        if ($obtenerFacturas['success']) {
            if ($obtenerFacturas['cantRes'] > 0) {
                if ($this->debug == 1) {
                    echo "<br> * ERROR -- El UUID de la Factura ya existe en la base de datos.";
                }
                $acuse = $obtenerFacturas['data'][0]['acuse'] ?? 'N/A';
                $response["success"] = false;
                $response["message"] = "Esa Factura ya fue registrada en el acuse: {$acuse}.";
                $response["debug"] = " * ERROR - El UUID dla Factura ya existe en la base de datos.";
                return $response;
            } else {
                if ($this->debug == 1) {
                    echo "<br> * El UUID de la Factura no existe en la base de datos.";
                }
            }
        } else {
            if ($this->debug == 1) {
                echo "<br> * ERROR -- Problemas al verificar si existe el UUID. Notifica a tu Administrador.";
            }
            $response["success"] = false;
            $response["message"] = "Problemas al verificar si existe el UUID. Notifica a tu Administrador.";
            $response["debug"] = " * Problemas al verificar si existe el UUID. Notifica a tu Administrador.";
            return $response;
        }

        // Validar entradas
        if (!is_array($dataProveedor) || empty($dataProveedor)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del proveedor.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos del proveedor no son válidos o están vacíos.";
            return $response;
        }

        if (!is_array($dataEmpresa) || empty($dataEmpresa)) {
            $response["success"] = false;
            $response["message"] = "No hay datos de la empresa.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos de la empresa no son válidos o están vacíos.";
            return $response;
        }

        if (!is_array($dataXML) || empty($dataXML)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del XML.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos del XML no son válidos o están vacíos.";
            return $response;
        }

        $errorMessages = [];
        $debugMessages = [];

        // Validar la Versión del CFDI
        $versionXML = mb_strtoupper($dataXML['Comprobante']['Version'] ?? '', 'UTF-8');
        if ($versionXML !== '4.0') {
            $response["isValid"] = false;
            $errorMessages[] = "* La versión del CFDI no es válida. Se esperaba <b>4.0</b>, pero se recibió <b>$versionXML</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Versión: Versión recibida <b>$versionXML</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Versión: <b>$versionXML</b>.";
        }

        // Validar Emisor RFC
        $rfcEmisor = mb_strtoupper($dataXML['Emisor']['Rfc'] ?? '', 'UTF-8');
        $rfcProveedor = mb_strtoupper($dataProveedor['RFC'] ?? '', 'UTF-8');
        if ($rfcEmisor !== $rfcProveedor) {
            $response["isValid"] = false;
            $errorMessages[] = "* El RFC del emisor no coincide. Se esperaba <b>$rfcProveedor</b>, pero se recibió <b>$rfcEmisor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de RFC Emisor: RFC recibido <b>$rfcEmisor</b>, RFC esperado <b>$rfcProveedor</b>.";
        } else {
            $debugMessages[] = "OK - Validación de RFC Emisor: <b>$rfcEmisor</b>.";
        }

        // Validar Emisor RazonSocial
        $razonEmisor = mb_strtoupper($dataXML['Emisor']['Nombre'] ?? '', 'UTF-8');
        $razonProveedor = mb_strtoupper($dataProveedor['RazonSocial'] ?? '', 'UTF-8');
        if ($razonEmisor !== $razonProveedor) {
            $response["isValid"] = false;
            $errorMessages[] = "* La Razón Social del emisor no coincide. Se esperaba <b>$razonProveedor</b>, pero se recibió <b>$razonEmisor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Razón Social Emisor: Razón recibida <b>$razonEmisor</b>, Razón esperada <b>$razonProveedor</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Razón Social Emisor: <b>$razonEmisor</b>.";
        }

        // Validar Emisor Régimen Fiscal
        $regimenEmisor = mb_strtoupper($dataXML['Emisor']['RegimenFiscal'] ?? '', 'UTF-8');
        $regimenProveedor = mb_strtoupper($dataProveedor['RegimenFiscal'] ?? '', 'UTF-8');
        if ($regimenEmisor !== $regimenProveedor) {
            $response["isValid"] = false;
            $errorMessages[] = "* El Régimen Fiscal del emisor no coincide. Se esperaba <b>$regimenProveedor</b>, pero se recibió <b>$regimenEmisor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Régimen Fiscal Emisor: Régimen recibido <b>$regimenEmisor</b>, Régimen esperado <b>$regimenProveedor</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Régimen Fiscal Emisor: <b>$regimenEmisor</b>.";
        }

        // Validar Receptor RFC
        $rfcReceptor = mb_strtoupper($dataXML['Receptor']['Rfc'] ?? '', 'UTF-8');
        $rfcEmpresa = mb_strtoupper($dataEmpresa['rfc'] ?? '', 'UTF-8');
        if ($rfcReceptor !== $rfcEmpresa) {
            $response["isValid"] = false;
            $errorMessages[] = "* El RFC del receptor no coincide. Se esperaba <b>$rfcEmpresa</b>, pero se recibió <b>$rfcReceptor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de RFC Receptor: RFC recibido <b>$rfcReceptor</b>, RFC esperado <b>$rfcEmpresa</b>.";
        } else {
            $debugMessages[] = "OK - Validación de RFC Receptor: <b>$rfcReceptor</b>.";
        }

        // Validar Receptor Razón Social
        $razonReceptor = mb_strtoupper($dataXML['Receptor']['Nombre'] ?? '', 'UTF-8');
        $razonEmpresa = mb_strtoupper($dataEmpresa['razonSocial'] ?? '', 'UTF-8');
        if ($razonReceptor !== $razonEmpresa) {
            $response["isValid"] = false;
            $errorMessages[] = "* La Razón Social del receptor no coincide. Se esperaba <b>$razonEmpresa</b>, pero se recibió <b>$razonReceptor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Razón Social Receptor: Razón recibida <b>$razonReceptor</b>, Razón esperada <b>$razonEmpresa</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Razón Social Receptor: <b>$razonReceptor</b>.";
        }

        // Validar Receptor Domicilio Fiscal
        $domicilioReceptor = mb_strtoupper($dataXML['Receptor']['DomicilioFiscalReceptor'] ?? '', 'UTF-8');
        $domicilioEmpresa = mb_strtoupper($dataEmpresa['cp'] ?? '', 'UTF-8');
        if ($domicilioReceptor !== $domicilioEmpresa) {
            $response["isValid"] = false;
            $errorMessages[] = "* El Domicilio Fiscal del receptor no coincide. Se esperaba <b>$domicilioEmpresa</b>, pero se recibió <b>$domicilioReceptor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Domicilio Fiscal Receptor: Domicilio recibido <b>$domicilioReceptor</b>, Domicilio esperado <b>$domicilioEmpresa</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Domicilio Fiscal Receptor: <b>$domicilioReceptor</b>.";
        }

        // Validar Receptor Régimen Fiscal
        $regimenReceptor = mb_strtoupper($dataXML['Receptor']['RegimenFiscalReceptor'] ?? '', 'UTF-8');
        $regimenEmpresa = mb_strtoupper($dataEmpresa['idRegimen'] ?? '', 'UTF-8');
        if ($regimenReceptor !== $regimenEmpresa) {
            $response["isValid"] = false;
            $errorMessages[] = "* El Régimen Fiscal del receptor no coincide. Se esperaba <b>$regimenEmpresa</b>, pero se recibió <b>$regimenReceptor</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Régimen Fiscal Receptor: Régimen recibido <b>$regimenReceptor</b>, Régimen esperado <b>$regimenEmpresa</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Régimen Fiscal Receptor: <b>$regimenReceptor</b>.";
        }

        // Generar mensaje final
        $response["message"] = empty($errorMessages) ? "Todo OK" : implode("", $errorMessages);
        $response["debug"] = implode("<br>", $debugMessages);

        return $response;
    }

    public function validarReglasNegocioNacional_Ingresos(int $noProveedor, $dataXML, $configParaValidaciones)
    {
        $response = [
            "success" => true,
            "message" => "",
            "isValid" => true,
            "debug" => ""
        ];

        // Validar entradas
        if (!is_array($dataXML) || empty($dataXML)) {
            $response["success"] = false;
            $response["message"] = "* No obtuvimos hay datos del XML.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - El XML no es un arreglo o está vacío.";
            return $response;
        }

        if (!is_array($configParaValidaciones) || empty($configParaValidaciones)) {
            $response["success"] = false;
            $response["message"] = "* La configuración para validaciones no está disponible.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - La configuración para validaciones no es un arreglo o está vacía.";
            return $response;
        }

        $errorMessages = [];
        $debugMessages = [];

        // Validar el Tipo de CFDI
        $tipoDeComprobante = $dataXML['Comprobante']['TipoDeComprobante'] ?? '';
        if ($tipoDeComprobante !== 'I') {
            $response["isValid"] = false;
            $errorMessages[] = "* Tu CFDI no es de Ingresos, es tipo <b>$tipoDeComprobante</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Tipo de Comprobante: <b>$tipoDeComprobante</b>";
        } else {
            $debugMessages[] = "OK - Validación de Tipo de Comprobante: <b>$tipoDeComprobante</b>";
        }

        // Validación de Año Fiscal
        $fechaFactura = strtotime($dataXML['Comprobante']['Fecha'] ?? '');
        $fechaTimbrado = strtotime($dataXML['TimbreFiscal']['FechaTimbrado'] ?? '');
        $anioActual = date('Y');

        if (
            !$configParaValidaciones['excepcionesProveedor']['AnioFiscal'] &&
            (date('Y', $fechaFactura) !== $anioActual || date('Y', $fechaTimbrado) !== $anioActual)
        ) {
            $response["isValid"] = false;
            $errorMessages[] = "* La factura no pertenece al Año Fiscal, la fecha es <b>" . date('Y', $fechaFactura) . "</b> y el timbrado es <b>" . date('Y', $fechaTimbrado) . "</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Año Fiscal: Fecha Factura <b>" . date('Y', $fechaFactura) . "</b>, Fecha Timbrado <b>" . date('Y', $fechaTimbrado) . "</b>";
        } else {
            $debugMessages[] = "OK - Validación de Año Fiscal: Fecha Factura <b>" . date('Y', $fechaFactura) . "</b>, Fecha Timbrado <b>" . date('Y', $fechaTimbrado) . "</b>";
        }

        // Validación de Tiempo de Emisión
        $tiempoVigencia = $configParaValidaciones['configCFDI']['tiempoVigencia'] ?? '6 month';
        $fechaLimite = strtotime("- $tiempoVigencia");

        if (
            !$configParaValidaciones['excepcionesProveedor']['FechaEmision'] &&
            ($fechaFactura < $fechaLimite || $fechaTimbrado < $fechaLimite)
        ) {
            $response["isValid"] = false;
            $errorMessages[] = "* La fecha de la factura o timbrado excede el tiempo de emisión permitido.<br>";
            $debugMessages[] = "ERROR - Validación de Tiempo de Emisión: Fecha Límite <b>" . date('Y-m-d', $fechaLimite) . "</b>";
        } else {
            $debugMessages[] = "OK - Validación de Tiempo de Emisión: Fecha Factura <b>" . date('Y-m-d', $fechaFactura) . "</b>, Fecha Timbrado <b>" . date('Y-m-d', $fechaTimbrado) . "</b>";
        }

        // Validación del Uso de CFDI
        $usoCFDI = $dataXML['Receptor']['UsoCFDI'] ?? '';
        $usosValidos = explode(',', $configParaValidaciones['configCFDI']['usosCFDI'] ?? '');

        if ($configParaValidaciones['excepcionesProveedor']['UsoCfdiDistinto']) {
            $usosProveedor = explode(',', $configParaValidaciones['excepcionesProveedor']['UsoCfdi'] ?? '');
            $usosValidos = array_merge($usosValidos, $usosProveedor);
        }

        if (!in_array($usoCFDI, $usosValidos)) {
            $response["isValid"] = false;
            $errorMessages[] = "* El uso de CFDI <b>$usoCFDI</b> no está permitido.<br>";
            $debugMessages[] = "ERROR - Validación de Uso de CFDI: <b>$usoCFDI</b> no está en los válidos.";
        } else {
            $debugMessages[] = "OK - Validación de Uso de CFDI: <b>$usoCFDI</b> está en los válidos.";
        }

        // Validación de Método y Forma de Pago
        $metodoPago = $dataXML['Comprobante']['MetodoPago'] ?? '';
        $formaPago = $dataXML['Comprobante']['FormaPago'] ?? '';

        if ($metodoPago === 'PPD' && $formaPago !== '99') {
            $response["isValid"] = false;
            $errorMessages[] = "Como el Método de pago es PPD, la Forma de Pago debe ser 99 y es: <b>$formaPago</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Forma de Pago: Forma <b>$formaPago</b> no válida para Método PPD.";
        } elseif ($metodoPago === 'PUE') {
            $formasPUE = explode(',', $configParaValidaciones['configCFDI']['formasPagoPUE'] ?? '');
            if (!in_array($formaPago, $formasPUE)) {
                $response["isValid"] = false;
                $errorMessages[] = "Como el Método de pago es PUE, la Forma de Pago debe estar en las permitidas y es: <b>$formaPago</b>.<br>";
                $debugMessages[] = "ERROR - Validación de Forma de Pago para PUE: <b>$formaPago</b> no válida.";
            } else {
                $debugMessages[] = "OK - Validación de Forma de Pago para PUE: <b>$formaPago</b> válida.";
            }

            // Validar si es pagable en el mes
            $cPago = $configParaValidaciones['datosRecepciones']['CPago'] ?? '0 DAY';
            $fechaPagoPermitida = strtotime("+ $cPago");
            $mesActual = date('m');
            $mesPago = date('m', $fechaPagoPermitida);

            $permitirPueSiempre = $configParaValidaciones['excepcionesProveedor']['PermitirPueSiempre'] ?? false;
            $fechaExpiracionPue = $configParaValidaciones['excepcionesProveedor']['FechaExpiracionPueSiempre'] ?? null;
            $excepcionValida = false;

            if ($permitirPueSiempre && $fechaExpiracionPue) {
                $fechaActual = date('Y-m-d');
                // Validar que el permiso no haya expirado (fecha de expiración >= hoy)
                if (strtotime($fechaExpiracionPue) >= strtotime($fechaActual)) {
                    $excepcionValida = true;
                }
            }

            if ($mesActual !== $mesPago) {
                if ($excepcionValida) {
                    $debugMessages[] = "OK - Validación de Pagabilidad para PUE omitida: El proveedor tiene la excepción 'PermitirPueSiempre' vigente hasta <b>{$fechaExpiracionPue}</b>.";
                } else {
                    $response["isValid"] = false;
                    $errorMessages[] = "* No podemos recibir la factura con Método de Pago PUE porque no sería pagable dentro del mismo mes.<br>";
                    $debugMessages[] = "ERROR - Validación de Pagabilidad para PUE: Fecha Pago Permitida <b>" . date('Y-m-d', $fechaPagoPermitida) . "</b>, Mes Actual <b>$mesActual</b>, Mes de Pago <b>$mesPago</b>.";
                }
            } else {
                $debugMessages[] = "OK - Validación de Pagabilidad para PUE: Fecha Pago Permitida <b>" . date('Y-m-d', $fechaPagoPermitida) . "</b>.";
            }
        } else {
            $debugMessages[] = "OK - Validación de Método de Pago: Método <b>$metodoPago</b>, Forma <b>$formaPago</b>.";
        }


        // Validación de Exportación
        $exportacion = $dataXML['Comprobante']['Exportacion'] ?? '';
        if ($exportacion !== '01') {
            $response["isValid"] = false;
            $errorMessages[] = "* El nodo Exportación debe ser 01 y no existe o es: <b>$exportacion</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Exportación: El valor debe ser '01' y es <b>$exportacion</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Exportación: <b>$exportacion</b>.";
        }

        // Validación de Tipo de Moneda
        $monedaXML = $dataXML['Comprobante']['Moneda'] ?? '';
        $monedaConfig = $configParaValidaciones['datosRecepciones']['idMoneda'] ?? '';
        if ($monedaXML !== $monedaConfig) {
            $response["isValid"] = false;
            $errorMessages[] = " * El tipo de moneda no coincide. El XML tiene <b>$monedaXML</b>, pero se esperaba <b>$monedaConfig</b>.<br>";
            $debugMessages[] = "ERROR - Validación de Tipo de Moneda: XML <b>$monedaXML</b>, Configuración <b>$monedaConfig</b>.";
        } else {
            $debugMessages[] = "OK - Validación de Tipo de Moneda: <b>$monedaXML</b>.";
        }


        // Validación de Montos
        $subtotalXML = $dataXML['Comprobante']['Total'] ?? 0;
        $descuentoXML = $dataXML['Comprobante']['Descuento'] ?? 0;
        $ignoraDescuento = $configParaValidaciones['excepcionesProveedor']['IgnoraDescuento'] ?? false;
        if (!$ignoraDescuento) {
            $subtotalXML = $subtotalXML - $descuentoXML;
        }

        // Aplicar descuentos por notas de crédito si aplica. NOTA IMPORTANTE: Esto es sólo para SilmeAgro vimos que hay facturas donde 
        //   descuentan el valor de las notas de credito y hay veces que no como el caso de INNOVAK que la factura entra normal .
        if (isset($configParaValidaciones['descontarPromocionesAplicables']) && $configParaValidaciones['descontarPromocionesAplicables']) {
            $debugMessages[] = "<br>* Proveedor con configuración para descontar promociones aplicables.";

            // Preparación de Subtotal para SilmeAgro donde al subtotal le restamos los porcentaje promocionales aplicables para comparar con el valor de la OC
            if (isset($configParaValidaciones['notasCreditos']) && is_array($configParaValidaciones['notasCreditos'])) {
                $sumaMontoDescuentos = 0;
                foreach ($configParaValidaciones['notasCreditos'] as $notaCredito) {

                    if ($notaCredito['idPoliticaComercial'] > 0 && $notaCredito['FormaCobro'] == 'NC') {
                        if ($this->debug == 1) {
                            echo "<br>Aplicando nota de crédito: " . $notaCredito['IdNotaCredito'] . " por " . $notaCredito['Descripcion'];
                        }
                        if ($notaCredito['TipoDescuento'] == 'AMOUNT') {
                            // Aplicar descuento por monto
                            $montoDescuento = $notaCredito['ValorDescuento'] ?? 0;
                            $subtotalXML -= $montoDescuento;
                            $debugMessages[] = "<br>* OK - $ Descuento aplicado $ $montoDescuento por nota de crédito: <b>$ $montoDescuento</b> al subtotal <b>$subtotalXML</b>.";
                            $sumaMontoDescuentos += $montoDescuento;
                        } elseif ($notaCredito['TipoDescuento'] == 'PERCENT') {
                            // Aplicar descuento porcentual
                            $porcentajeDescuento = $notaCredito['ValorDescuento'] ?? 0;
                            $montoDescuento = ($subtotalXML * ($porcentajeDescuento / 100));
                            $subtotalXML -= $montoDescuento;
                            if ($this->debug == 1) {
                                echo "<br>Aplicando descuento $porcentajeDescuento %: $" . $montoDescuento;
                            }
                            $debugMessages[] = "<br>* OK - % Descuento aplicado $porcentajeDescuento % por nota de crédito: <b>$ $montoDescuento</b> al subtotal <b>$subtotalXML</b>.";
                            $sumaMontoDescuentos += $montoDescuento;
                        }
                    }
                }
            }
            $debugMessages[] = "<br>* Subtotal XML después de aplicar descuentos: <b>$subtotalXML</b>.";
        } else {
            $debugMessages[] = "<br>* Proveedor sin configuración para descontar promociones aplicables.";
        }

        $subtotalConfig = $configParaValidaciones['datosRecepciones']['Subtotal'] ?? 0;
        $bloqDiferencia = $configParaValidaciones['excepcionesProveedor']['BloqDiferenciaMonto'] ?? false;
        $tipoRegla = $configParaValidaciones['diferenciaMontos']['tipoRegla'] ?? 1;

        if ($bloqDiferencia) {
            if ($subtotalXML != $subtotalConfig) {
                $response["isValid"] = false;
                $errorMessages[] = "* El subtotal <b>$subtotalXML</b> no coincide con el valor esperado.<br>";
                $debugMessages[] = "ERROR - Bloqueo de Diferencia de Montos: Subtotal XML <b>$subtotalXML</b>, Subtotal Configuración <b>$subtotalConfig</b>.";
            } else {
                $debugMessages[] = "OK - Validación de Bloqueo de Diferencia de Montos: Subtotal XML <b>$subtotalXML</b>, Subtotal Configuración <b>$subtotalConfig</b>.";
            }
        } else {
            $subtXML = number_format($subtotalXML, 2, '.', ',');
            $subtHES = number_format($subtotalConfig, 2, '.', ',');
            $minimoMonto = $subtotalConfig - $configParaValidaciones['diferenciaMontos']['montoInferior'];
            $maximoMonto = $subtotalConfig + $configParaValidaciones['diferenciaMontos']['montoSuperior'];

            $porcentajeInferior = $subtotalConfig * ($configParaValidaciones['diferenciaMontos']['porcentajeInferior'] / 100);
            $porcentajeSuperior = $subtotalConfig * ($configParaValidaciones['diferenciaMontos']['porcentajeSuperior'] / 100);
            $minimoPorcentaje = $subtotalConfig - $porcentajeInferior;
            $maximoPorcentaje = $subtotalConfig + $porcentajeSuperior;

            if ($tipoRegla === 3) {
                $minimo = max($minimoMonto, $minimoPorcentaje);
                $maximo = min($maximoMonto, $maximoPorcentaje);
            } elseif ($tipoRegla === 1) {
                $minimo = $minimoMonto;
                $maximo = $maximoMonto;
            } elseif ($tipoRegla === 2) {
                $minimo = $minimoPorcentaje;
                $maximo = $maximoPorcentaje;
            }



            if (($subtotalXML <= $minimo || $subtotalXML >= $maximo) && $subtotalXML != 0) {

                $response["isValid"] = false;
                $errorMessages[] = "* El subtotal <b>$ $subtXML</b> es incorrecto.<br>";
                $debugMessages[] = "ERROR - Validación de Montos: El subtotal XML <b>$ $subtXML </b> deberia ser: <b>$ $subtHES</b> y está fuera del rango permitido (<b>$minimo - $maximo</b>).";
            } else {
                $debugMessages[] = "OK - Validación de Montos: Subtotal XML <b>$ $subtXML </b> deberia ser: <b>$ $subtHES</b> esta en el rango permitido (<b>$minimo - $maximo</b>).";
            }
        }

        // Generar mensaje final
        $response["message"] = empty($errorMessages) ? "Todo OK" : implode("", $errorMessages);
        $response["debug"] = implode("<br>", $debugMessages);

        return $response;
    }

    public function validarReglasInternasNacional_Pagos($dataProveedor, $dataEmpresa, $dataXML, $dataCompras, $configParaValidaciones = [])
    {
        $this->debug = 0; // Activado para pruebas (Complemento de Pago)
        $response = [
            "success" => true,
            "message" => "",
            "isValid" => true,
            "debug" => ""
        ];

        $excepcionesDisponibles = [
            'NoValidarPagos'
        ];

        // Validar que las excepciones estén definidas
        if (isset($configParaValidaciones['Excepciones']) && is_array($configParaValidaciones['Excepciones'])) {
            foreach ($configParaValidaciones['Excepciones'] as $excepcion => $valor) {
                if (!in_array($excepcion, $excepcionesDisponibles)) {
                    $response["success"] = false;
                    $response["message"] = "La excepción <b>$excepcion</b> no está permitida.";
                    $response["isValid"] = false;
                    $response["debug"] = "ERROR - La excepción <b>$excepcion</b> no está en la lista de excepciones disponibles.";
                    return $response;
                }
            }
        }

        // Validaciones de los arreglos de entrada
        if (!is_array($dataProveedor) || empty($dataProveedor)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del proveedor.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos del proveedor no son válidos o están vacíos.";
            return $response;
        }

        if (!is_array($dataEmpresa) || empty($dataEmpresa)) {
            $response["success"] = false;
            $response["message"] = "No hay datos de la empresa.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos de la empresa no son válidos o están vacíos.";
            return $response;
        }

        if (!is_array($dataXML) || empty($dataXML)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del XML.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos del XML no son válidos o están vacíos.";
            return $response;
        }

        if (!is_array($dataCompras) || empty($dataCompras)) {
            $response["success"] = false;
            $response["message"] = "No hay datos de las compras relacionadas.";
            $response["isValid"] = false;
            $response["debug"] = "ERROR - Los datos de la compra no son válidos o están vacíos.";
            return $response;
        }

        if ($this->debug == 1) {
            echo "<br>=======================<br>Inicia Validación de Reglas Internas Nacional Pagos...<br>";
        }

        $errores = [];

        // VALIDAR AQUI SI EL UUID DEL COMPLEMENTO DE PAGO YA EXISTE EN LA BD
        if (empty($dataXML['TimbreFiscal']['UUID'])) {
            $response["message"] = "El nodo UUID del Timbre Fiscal no existe o está vacío.";
            $response["debug"] = " * ERROR - El nodo UUID del Timbre Fiscal no está presente en el XML.";
            return $response;
        }

        if ($this->debug == 1) {
            echo "<br>UUID del Timbre Fiscal: " . $dataXML['TimbreFiscal']['UUID'];
            echo "<br>Validamos si el Complemento de Pago ya existe en la base de datos (solo activos)...";
        }
        $cfdis_Mdl = new CFDIs_Mdl();
        $filtrosComplemento = [
            'uuids' => $dataXML['TimbreFiscal']['UUID'] ?? null
        ];
        $obtenerCompDePago = $cfdis_Mdl->obtenerComplementosDePago($filtrosComplemento);
        if ($obtenerCompDePago['success']) {
            if ($obtenerCompDePago['cantRes'] > 0) {
                $complementosActivos = array_filter($obtenerCompDePago['data'], function ($comp) {
                    $estatus = (string)($comp['estatus'] ?? '');
                    return in_array($estatus, ['1', '2'], true);
                });

                if (count($complementosActivos) === 0) {
                    if ($this->debug == 1) {
                        echo "<br> * El UUID del complemento de pago existe pero está cancelado/rechazado; se permite nuevo registro.";
                    }
                    return $response;
                }

                if ($this->debug == 1) {
                    echo "<br> * ERROR -- El UUID del complemento de pago ya existe y está activo en la base de datos.";
                }
                $primerActivo = array_values($complementosActivos)[0];
                $fechaRegistro = $primerActivo['fechaReg'] ?? 'N/A';
                $estatus = $primerActivo['estatus'] ?? 'N/A';
                $estatusTexto = ['0' => 'Cancelado', '1' => 'Pendiente', '2' => 'Aceptado', '3' => 'Rechazado'][$estatus] ?? "Estatus $estatus";
                $response["success"] = false;
                $response["message"] = "Ese complemento de pago ya fue registrado el {$fechaRegistro} con estatus: {$estatusTexto}.";
                $response["debug"] = " * ERROR - El UUID del complemento de pago ya existe y está activo en la base de datos (estatus: $estatus).";
                return $response;
            } else {
                if ($this->debug == 1) {
                    echo "<br> * El UUID del complemento de pago no existe activo en la base de datos (puede haber sido rechazado antes).";
                }
            }
        } else {
            if ($this->debug == 1) {
                echo "<br> * ERROR -- Problemas al verificar si existe el UUID. Notifica a tu Administrador.";
            }
            $response["success"] = false;
            $response["message"] = "Problemas al verificar si existe el UUID. Notifica a tu Administrador.";
            $response["debug"] = " * Problemas al verificar si existe el UUID. Notifica a tu Administrador.";
            return $response;
        }

        // Validaciones de los Datos de las compras relacionadas
        $idProveedor = $dataProveedor['IdProveedor'] ?? null;

        foreach ($dataCompras as $factura) {
            $serie = $factura['serie'] ?? 'N/A';
            $folio = $factura['folio'] ?? 'N/A';
            $uuid = $factura['uuid'] ?? 'N/A';
            if ($this->debug == 1) {
                echo "<br>Compras y UUID Relacionado '{$serie}{$folio}' con UUID '{$uuid}'";
            }

            // Verificar que la compra este en estatus 2 (Aceptada)
            if ($this->debug == 1) {
                echo "<br> * La compra debe estar en estatus 2 (Aceptada): " . $factura['estatus'];
            }
            if ($factura['estatus'] != 2) {
                $errores[] = "* La factura '{$serie}' '{$folio}' con UUID '{$uuid}': No está Aceptada.";
            }

            // Verificar que la factura pertenezca al proveedor
            if ($this->debug == 1) {
                echo "<br> * La factura debe pertenece al proveedor {$idProveedor}: " . $factura['idProveedor'];
            }
            if ($factura['idProveedor'] != $idProveedor) {
                $errores[] = "* La factura '{$serie}' '{$folio}' con UUID '{$uuid}': No pertenece al proveedor.";
            }

            // Verificar que el Método de Pago sea PPD
            if ($this->debug == 1) {
                echo "<br> * La factura debe tener el método de Pago PPD:" . $factura['idCatMetodoPago'];
            }
            if ($factura['idCatMetodoPago'] !== 'PPD') {
                $errores[] = "* La factura '{$serie}' '{$folio}' con UUID '{$uuid}': El método de Pago no es PPD.";
            }

            // Verificar que la Forma de Pago sea 99
            if ($this->debug == 1) {
                echo "<br> * La factura debe tener la forma de Pago 99:" . $factura['idCatFormaPago'];
            }
            if ($factura['idCatFormaPago'] !== '99') {
                $errores[] = "* La factura '{$serie}' '{$folio}' con UUID '{$uuid}': La forma de Pago no es 99.";
            }

            // Verificar que la factura no tenga un complemento anterior con saldo insoluto en 0
            if ($this->debug == 1) {
                echo "<br> * La factura no debe tener un complemento de pago con saldo insoluto en 0." . $factura['minInsoluto'] <= 0;
            }
            if (!is_null($factura['idUltimoComplemento']) && $factura['minInsoluto'] <= 0) {
                $errores[] = "* La factura '{$serie}' '{$folio}' con UUID '{$uuid}': Ya tiene un complemento de pago con saldo insoluto en 0.";
            }

            // Verificar que la factura ya haya sido pagada
            if ($this->debug == 1) {
                echo "<br> * La factura debe tener pagos:" . $factura['totalPagos'];
            }
            if ($factura['totalPagos'] < $factura['totalComplementos']) {
                $errores[] = "* La factura '{$serie}' '{$folio}' con UUID '{$uuid}': No ha sido pagada.";
            }
        }

        // Validaciones de los Datos de los datos del XML
        if ($this->debug == 1) {
            echo "<br><br>Comienza Validación de Datos del XML...<br>";
        }

        //Verificar que el tipo de comprobante sea P 
        if ($this->debug == 1) {
            echo "<br> * El tipo de comprobante debe ser P: " . $dataXML['Comprobante']['TipoDeComprobante'];
        }
        if ($dataXML['Comprobante']['TipoDeComprobante'] != 'P') {
            $errores[] = "* El comprobante no es de Pago, es tipo: " . $dataXML['Comprobante']['TipoDeComprobante'];
        }

        //Verificar el Nombre del Emisor
        if ($this->debug == 1) {
            echo "<br> * El nombre del emisor debe ser: " . $dataProveedor['RazonSocial'];
        }
        if ($dataXML['Emisor']['Nombre'] != $dataProveedor['RazonSocial']) {
            $errores[] = "* El nombre del emisor no coincide con el proveedor: " . $dataXML['Emisor']['Nombre'];
        }

        //Verificar el Regimen Fiscal del Emisor
        if ($this->debug == 1) {
            echo "<br> * El régimen fiscal del emisor debe ser: " . $dataProveedor['RegimenFiscal'];
        }
        if ($dataXML['Emisor']['RegimenFiscal'] != $dataProveedor['RegimenFiscal']) {
            $errores[] = "* El régimen fiscal del emisor no coincide con el proveedor: " . $dataXML['Emisor']['RegimenFiscal'];
        }

        //verificar el RFC del Emisor
        if ($this->debug == 1) {
            echo "<br> * El RFC del emisor debe ser: " . $dataProveedor['RFC'];
        }
        if ($dataXML['Emisor']['Rfc'] != $dataProveedor['RFC']) {
            $errores[] = "* El RFC del emisor no coincide con el proveedor: " . $dataXML['Emisor']['Rfc'];
        }

        //Verificar el Nombre del Receptor
        if ($this->debug == 1) {
            echo "<br> * El nombre del receptor debe ser: " . $dataEmpresa['razonSocial'];
        }
        if ($dataXML['Receptor']['Nombre'] != $dataEmpresa['razonSocial']) {
            $errores[] = "* El nombre del receptor no coincide con la empresa: " . $dataXML['Receptor']['Nombre'];
        }

        //Verificar el Regimen Fiscal del Receptor
        if ($this->debug == 1) {
            echo "<br> * El régimen fiscal del receptor debe ser: " . $dataEmpresa['idRegimen'];
        }
        if ($dataXML['Receptor']['RegimenFiscalReceptor'] != $dataEmpresa['idRegimen']) {
            $errores[] = "* El régimen fiscal del receptor no coincide con la empresa: " . $dataXML['Receptor']['RegimenFiscalReceptor'];
        }

        //verificar el RFC del Receptor
        if ($this->debug == 1) {
            echo "<br> * El RFC del receptor debe ser: " . $dataEmpresa['rfc'];
        }
        if ($dataXML['Receptor']['Rfc'] != $dataEmpresa['rfc']) {
            $errores[] = "* El RFC del receptor no coincide con la empresa: " . $dataXML['Receptor']['Rfc'];
        }

        //Verificar el Domicilio Fiscal del Receptor
        if ($this->debug == 1) {
            echo "<br> * El domicilio fiscal del receptor debe ser: " . $dataEmpresa['cp'];
        }
        if ($dataXML['Receptor']['DomicilioFiscalReceptor'] != $dataEmpresa['cp']) {
            $errores[] = "* El domicilio fiscal del receptor no coincide con la empresa: " . $dataXML['Receptor']['DomicilioFiscalReceptor'];
        }

        //Verificar el Uso del CFDI sea CP01 (Definido para Complementos de Pago por el SAT)
        if ($this->debug == 1) {
            echo "<br> * El uso del CFDI debe ser CP01: " . $dataXML['Receptor']['UsoCFDI'];
        }
        if ($dataXML['Receptor']['UsoCFDI'] != 'CP01') {
            $errores[] = "* El uso del CFDI no es CP01: " . $dataXML['Receptor']['UsoCFDI'];
        }

        // Validación de Año Fiscal
        if ($this->debug == 1) {
            echo "<br><br>Comienza Validación de Año Fiscal...<br>";
        }
        $fechaComprobante = $dataXML['Comprobante']['Fecha'] ?? '';
        $fechaTimbrado = $dataXML['TimbreFiscal']['FechaTimbrado'] ?? '';
        $anioActual = date('Y');

        if (!empty($fechaComprobante) && !empty($fechaTimbrado)) {
            $fechaFactura = strtotime($fechaComprobante);
            $fechaTimbradoTimestamp = strtotime($fechaTimbrado);

            $excepcionAnioFiscal = $configParaValidaciones['excepcionesProveedor']['AnioFiscal'] ?? false;
            if (!$excepcionAnioFiscal) {
                if (date('Y', $fechaFactura) !== $anioActual || date('Y', $fechaTimbradoTimestamp) !== $anioActual) {
                    if ($this->debug == 1) {
                        echo "<br> * ERROR - El complemento de pago no pertenece al Año Fiscal. Año de Comprobante: " . date('Y', $fechaFactura) . ", Año de Timbrado: " . date('Y', $fechaTimbradoTimestamp);
                    }
                    $errores[] = "* El complemento de pago no pertenece al Año Fiscal. Fecha del comprobante: <b>" . date('Y', $fechaFactura) . "</b>, Fecha de timbrado: <b>" . date('Y', $fechaTimbradoTimestamp) . "</b>.";
                } else {
                    if ($this->debug == 1) {
                        echo "<br> * OK - Validación de Año Fiscal: Año del comprobante y timbrado coinciden con el año actual.";
                    }
                }
            } else {
                if ($this->debug == 1) {
                    echo "<br> * OK - Validación de Año Fiscal omitida por excepción del proveedor.";
                }
            }
        }

        // Validación de Tiempo de Emisión
        if ($this->debug == 1) {
            echo "<br><br>Comienza Validación de Tiempo de Emisión...<br>";
        }
        if (!empty($fechaComprobante) && !empty($fechaTimbrado)) {
            $tiempoVigencia = $configParaValidaciones['configCFDI']['tiempoVigencia'] ?? '6 month';
            $fechaLimite = strtotime("- $tiempoVigencia");

            $excepcionFechaEmision = $configParaValidaciones['excepcionesProveedor']['FechaEmision'] ?? false;
            if (!$excepcionFechaEmision) {
                if ($fechaFactura < $fechaLimite || $fechaTimbradoTimestamp < $fechaLimite) {
                    if ($this->debug == 1) {
                        echo "<br> * ERROR - La fecha del complemento de pago o timbrado excede el tiempo de emisión permitido. Fecha límite: " . date('Y-m-d', $fechaLimite);
                    }
                    $errores[] = "* La fecha del complemento de pago o timbrado excede el tiempo de emisión permitido (<b>" . date('Y-m-d', $fechaLimite) . "</b>). Fecha del comprobante: <b>" . date('Y-m-d', $fechaFactura) . "</b>, Fecha de timbrado: <b>" . date('Y-m-d', $fechaTimbradoTimestamp) . "</b>.";
                } else {
                    if ($this->debug == 1) {
                        echo "<br> * OK - Validación de Tiempo de Emisión: Las fechas están dentro del rango permitido.";
                    }
                }
            } else {
                if ($this->debug == 1) {
                    echo "<br> * OK - Validación de Tiempo de Emisión omitida por excepción del proveedor.";
                }
            }
        }

        //Preparar el mensaje de respuesta
        if (isset($errores) && count($errores) > 0) {
            $response["success"] = false;
            $response["message"] = implode("<br>", $errores);
            $response["isValid"] = false;
            $response["debug"] = implode("<br>", $errores);
        } else {
            $response["message"] = "Todo OK";
            $response["debug"] = "No se encontraron errores.";
        }
        $response["errores"] = $errores ?? [];

        return $response;
    }

    public function validarReglasNegocioNacional_Pagos($dataXML, $dataCompras, $dataPagos, $configParaValidaciones = [])
    {
        $response = [
            "success" => false,
            "message" => "",
            "isValid" => false,
            "debug"   => ""
        ];

        $excepcionesDisponibles = [
            'NoValidarPagos',
            'NoValidarFechasPago',
            'NoValidarFormasPago'
        ];

        // Validar que las excepciones estén definidas
        if (isset($configParaValidaciones['Excepciones']) && is_array($configParaValidaciones['Excepciones'])) {
            foreach ($configParaValidaciones['Excepciones'] as $excepcion => $valor) {
                if (!in_array($excepcion, $excepcionesDisponibles)) {
                    $response["message"] = "La excepción <b>$excepcion</b> no está permitida.";
                    $response["debug"] = "ERROR - La excepción <b>$excepcion</b> no está en la lista de excepciones disponibles.";
                    return $response;
                }
            }
        }

        $errores = [];

        // Debug inicio de validación
        if ($this->debug == 1) {
            echo "<br>=== Inicia Validación de Reglas de Negocio Nacional - Pagos ===<br>";
        }

        // 1) Preparar pagos registrados para emparejamiento por pago real
        if ($this->debug == 1) {
            echo "<br>Preparando pagos registrados para emparejamiento...";
            echo "<br>Total de registros en \$dataPagos: " . count($dataPagos);
        }
        $pagosDisponibles = [];
        $pagosSumByUuid = [];
        foreach ($dataPagos as $idx => $p) {
            $uuid = $p['uuid'];
            $montoPagado = floatval($p['montoPagado']);
            $montoPagoReal = floatval($p['montoTipoCambio'] ?? $p['montoPagado']);
            $monedaPagoReal = $p['monedaTipoCambio'] ?? $p['moneda'];
            $pagosDisponibles[] = [
                'index' => $idx,
                'id' => $p['id'] ?? null,
                'uuid' => $uuid,
                'montoPagado' => round($montoPagado, 2),
                'montoPagoReal' => round($montoPagoReal, 2),
                'monedaPagoReal' => $monedaPagoReal,
                'fechaPago' => $p['fechaPago'] ?? null,
                'formaPagoSAT' => $p['formaPagoSAT'] ?? $p['formaPago'] ?? null,
                'usado' => false,
            ];
            if (!isset($pagosSumByUuid[$uuid])) {
                $pagosSumByUuid[$uuid] = 0.0;
            }
            $pagosSumByUuid[$uuid] += $montoPagado;
        }
        if ($this->debug == 1) {
            echo "<br> * Pagos listos para emparejar: " . count($pagosDisponibles) . "<br>";
        }

        // 2) Acumulado de complementos ya registrados por UUID
        $complementosSumByUuid = [];
        $uuidList = array_unique(array_filter(array_column($dataCompras, 'uuid')));
        if (!empty($uuidList)) {
            $placeholders = [];
            $params = [];
            foreach ($uuidList as $i => $uuid) {
                $ph = ":uuid_$i";
                $placeholders[] = $ph;
                $params[$ph] = $uuid;
            }
            $sql = "SELECT uuidFact, SUM(importePagado) AS total
                    FROM cfdi_complementoPagoDet
                    WHERE uuidFact IN (" . implode(',', $placeholders) . ")
                    GROUP BY uuidFact";
            $stmt = BD_Connect::prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $complementosSumByUuid[$row['uuidFact']] = floatval($row['total']);
            }
        }
        if ($this->debug == 1) {
            echo "<br> * Complementos acumulados (BD): " . count($complementosSumByUuid) . "<br>";
        }

        // 3) Emparejar pagos del XML con pagos_compras y validar por pago
        $pagosMatch = [];
        $aplicadoEnEsteComplemento = [];
        $xmlGrouped = [];
        foreach ($dataXML['Pagos']['Pagos'] as $pagoIndex => $pagoNodo) {
            $montoXMLPago = round(floatval($pagoNodo['Monto']), 2);
            $monedaXMLPago = $pagoNodo['MonedaP'];
            $formaXMLPago = $pagoNodo['FormaDePagoP'];
            $fechaXMLPago = substr($pagoNodo['FechaPago'], 0, 10);
            $tipoCambioP = $pagoNodo['TipoCambioP'] ?? '';
            if ($this->debug == 1) {
                echo "<br>-- Pago XML {$pagoIndex}: monto={$montoXMLPago}, moneda={$monedaXMLPago}, forma={$formaXMLPago}, fecha={$fechaXMLPago} --";
            }

            // Buscar match en pagos_compras por pago real
            $matchId = null;
            $lastPagoBD = null;
            foreach ($pagosDisponibles as &$pagoBD) {
                if ($pagoBD['usado']) {
                    continue;
                }
                $lastPagoBD = $pagoBD;
                $montoMatch = abs($pagoBD['montoPagoReal'] - $montoXMLPago) < 0.01;
                $monedaMatch = ($pagoBD['monedaPagoReal'] === $monedaXMLPago);
                // $fechaMatch = empty($pagoBD['fechaPago']) || $pagoBD['fechaPago'] === $fechaXMLPago;
                $fechaMatch = true;
                $formaMatch = empty($pagoBD['formaPagoSAT']) || $pagoBD['formaPagoSAT'] === $formaXMLPago;
                if ($montoMatch && $monedaMatch && $fechaMatch && $formaMatch) {
                    $matchId = $pagoBD['id'];
                    $pagoBD['usado'] = true;
                    break;
                }
            }
            unset($pagoBD);

            if (empty($matchId)) {
                $errores[] = "* Pago XML {$pagoIndex}: no se encontró un pago registrado que coincida con Monto/Moneda/Fecha/Forma.";
                if ($this->debug == 1) {
                    $uuidDebug = $pagoNodo['DoctosRelacionados'][0]['IdDocumento'] ?? 'N/A';
                    echo "<br> * ERROR: Pago XML {$pagoIndex}: no se encontró un pago registrado que coincida con Monto/Moneda/Fecha/Forma. UUID: {$uuidDebug}.";
                    if (!empty($lastPagoBD)) {
                        echo "<br> * Monto: {$montoXMLPago} VS Monto Registrado: {$lastPagoBD['montoPagoReal']} - {$lastPagoBD['montoPagado']}";
                        echo "<br> * Moneda: {$monedaXMLPago} VS Moneda Registrada: {$lastPagoBD['monedaPagoReal']}";
                        echo "<br> * Fecha: {$fechaXMLPago} VS Fecha Registrada: {$lastPagoBD['fechaPago']}";
                        echo "<br> * Forma: {$formaXMLPago} VS Forma Registrada: {$lastPagoBD['formaPagoSAT']}";
                    } else {
                        echo "<br> * No hay pagos registrados disponibles para comparar.";
                    }
                }
            } else {
                $pagosMatch[$pagoIndex] = $matchId;
                if ($this->debug == 1) {
                    echo "<br> * MATCH: Pago XML {$pagoIndex} -> pagos_compras.id={$matchId}";
                }
            }

            // Validaciones por cada documento relacionado
            foreach ($pagoNodo['DoctosRelacionados'] as $dr) {
                $docId = $dr['IdDocumento'];
                if (!isset($xmlGrouped[$docId])) {
                    $xmlGrouped[$docId] = [
                        'impPagado'  => 0.0,
                        'monedaDR' => $dr['MonedaDR'] ?? '',
                        'monedaP' => $monedaXMLPago,
                        'tipoCambioP' => $tipoCambioP,
                    ];
                }
                $xmlGrouped[$docId]['impPagado']  += floatval($dr['ImpPagado']);
                $xmlGrouped[$docId]['monedaDR'] = $dr['MonedaDR'] ?? $xmlGrouped[$docId]['monedaDR'];
                $xmlGrouped[$docId]['monedaP'] = $monedaXMLPago;
                $xmlGrouped[$docId]['tipoCambioP'] = $tipoCambioP;

                $uuidFact = strtoupper($dr['IdDocumento'] ?? '');
                $monedaDR = $dr['MonedaDR'] ?? '';
                $impPagado = floatval($dr['ImpPagado'] ?? 0);

                if (!isset($aplicadoEnEsteComplemento[$uuidFact])) {
                    $aplicadoEnEsteComplemento[$uuidFact] = 0.0;
                }
                $aplicadoEnEsteComplemento[$uuidFact] += $impPagado;

                if (!empty($monedaDR) && !empty($monedaXMLPago) && $monedaDR !== $monedaXMLPago) {
                    $tipoCambioPNum = floatval($tipoCambioP);
                    if ($tipoCambioPNum <= 0) {
                        $errores[] = "* UUID {$uuidFact}: MonedaP ({$monedaXMLPago}) ≠ MonedaDR ({$monedaDR}) y TipoCambioP inválido o vacío.";
                    }
                }

                $totalPagosBD = floatval($pagosSumByUuid[$uuidFact] ?? 0);
                $totalComplementosBD = floatval($complementosSumByUuid[$uuidFact] ?? 0);
                $pendiente = $totalPagosBD - $totalComplementosBD;
                if ($this->debug == 1) {
                    echo "<br> * UUID {$uuidFact}: totalPagosBD={$totalPagosBD}, totalComplementosBD={$totalComplementosBD}, pendiente={$pendiente}, aplicadoComplemento={$aplicadoEnEsteComplemento[$uuidFact]}";
                }
                if ($aplicadoEnEsteComplemento[$uuidFact] > $pendiente + 0.01) {
                    $errores[] = "* UUID {$uuidFact}: el pago del complemento excede el pendiente disponible. Pendiente: {$pendiente}, aplicado en complemento: {$aplicadoEnEsteComplemento[$uuidFact]}.";
                }
            }
        }

        // 4) Fin validación
        if ($this->debug == 1) {
            echo "<br>=== Fin Validación de Pagos. Errores encontrados: " . count($errores) . " ===<br>";
        }

        $response['data']['pagosMatch'] = $pagosMatch;

        // Inicia validación de XML - Facturas
        if ($this->debug == 1) {
            echo "<br>=== Inicia Validación de XML - Facturas ===<br>";
        }

        // Aquí podrías iniciar la validación del complemento contra los datos de las facturas
        // que vienen en $dataCompras.

        foreach ($dataCompras as $factura) {
            $uuidFactura = $factura['uuid'];
            $monedaFactura = $factura['idCatTipoMoneda'];

            if ($this->debug == 1) {
                echo "<br>---- Validando Factura UUID: $uuidFactura ----<br>";
            }

            if (!isset($xmlGrouped[$uuidFactura])) {
                $errores[] = "* La factura con UUID $uuidFactura no está presente en el XML del complemento de pago.";
                if ($this->debug == 1) {
                    echo " * ERROR: UUID de factura no encontrado en el XML.<br>";
                }
            } else {
                $xmlMoneda = $xmlGrouped[$uuidFactura]['monedaDR'];

                if ($this->debug == 1) {
                    echo " * Moneda en Factura: $monedaFactura vs Moneda en XML: $xmlMoneda<br>";
                }

                if ($monedaFactura !== $xmlMoneda) {
                    $errores[] = "* La moneda de la factura UUID $uuidFactura no coincide con la del complemento. Factura: $monedaFactura, XML: $xmlMoneda.";
                    if ($this->debug == 1) {
                        echo " * ERROR: Moneda distinta detectada.<br>";
                    }
                }
            }
        }

        // Validar si algún UUID en el XML no existe en las facturas cargadas
        $uuidsFacturas = array_column($dataCompras, 'uuid');
        foreach (array_keys($xmlGrouped) as $uuidXML) {
            if (!in_array($uuidXML, $uuidsFacturas)) {
                $errores[] = "* El XML contiene el UUID $uuidXML, pero no existe en ninguna factura cargada.";
                if ($this->debug == 1) {
                    echo " * ERROR: UUID en XML sin factura correspondiente: $uuidXML<br>";
                }
            } else {
                if ($this->debug == 1) {
                    echo " * UUID $uuidXML verificado con factura existente.<br>";
                }
            }
        }

        // Fin de validación de reglas de negocio nacional - facturas
        if ($this->debug == 1) {
            echo "<br>=== Fin Validación de Reglas de Negocio Nacional - Facturas. Errores encontrados: " . count($errores) . " ===<br>";
        }

        // Generar mensaje final
        if (count($errores) > 0) {
            $response['success'] = false;
            $response['isValid'] = false;
            $response['message'] = implode("<br>", $errores);
            $response['debug']   = implode("<br>", $errores);
        } else {
            $response['success'] = true;
            $response['isValid'] = true;
            $response['message'] = "Todo OK";
            $response['debug']   = "No se encontraron errores.";
        }

        return $response;
    }

    public function validarReglasInternasNacional_Egresos($dataProveedor, $dataEmpresa, $dataXML)
    {
        $response = [
            "success" => true,
            "message" => "",
            "isValid" => true,
            "debug" => ""
        ];

        if ($this->debug == 1) {
            echo "<br>=======================<br>Inicia Validación de Reglas Internas Nacional Egresos...<br>";
        }

        // Validaciones de los arreglos de entrada
        if (!is_array($dataProveedor) || empty($dataProveedor)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del proveedor.";
            $response["isValid"] = false;
            return $response;
        }

        if (!is_array($dataEmpresa) || empty($dataEmpresa)) {
            $response["success"] = false;
            $response["message"] = "No hay datos de la empresa.";
            $response["isValid"] = false;
            return $response;
        }

        if (!is_array($dataXML) || empty($dataXML)) {
            $response["success"] = false;
            $response["message"] = "No hay datos del XML.";
            $response["isValid"] = false;
            return $response;
        }

        // 1. VALIDAR QUE EL UUID DE LA NOTA DE CRÉDITO NO EXISTA EN LA BD
        if (empty($dataXML['TimbreFiscal']['UUID'])) {
            $response["message"] = "El nodo UUID del Timbre Fiscal no existe o está vacío en la Nota de Crédito.";
            $response["isValid"] = false;
            return $response;
        }

        if ($this->debug == 1) {
            echo "<br> * Verifica si el UUID de la Nota de Crédito ya esta en la BD: " . $dataXML['TimbreFiscal']['UUID'];
        }

        $cfdis_Mdl = new CFDIs_Mdl();
        $filtrosNC = [
            'uuids' => $dataXML['TimbreFiscal']['UUID'] ?? null,
        ];
        // Llamamos a la nueva función correcta
        $obtenerNC = $cfdis_Mdl->obtenerNotasCredito($filtrosNC);

        if ($obtenerNC['success']) {
            if ($obtenerNC['cantRes'] > 0) {
                if ($this->debug == 1) {
                    echo "<br> <b>* ERROR -- El UUID de la Nota de Crédito ya existe en la base de datos.</b>";
                }
                // Línea corregida según tu solicitud
                $acuse = $obtenerNC['data'][0]['idCompra'] ?? 'N/A';
                $identificadorNc = $obtenerNC['data'][0]['id'] ?? 'N/A';
                $response["success"] = false;
                $response["isValid"] = false;
                $response["message"] = "Esta Nota de Crédito ya fue registrada previamente en el acuse: {$acuse}. Identificador: {$identificadorNc}.";
                return $response;
            } else {
                if ($this->debug == 1) {
                    echo "<br> * OK - El UUID de la Nota de Crédito no existe en la base de datos. Se puede continuar.";
                }
            }
        } else {
            $response["success"] = false;
            $response["isValid"] = false;
            $response["message"] = "Problemas al verificar si existe el UUID de la Nota de Crédito. Notifica a tu Administrador.";
            return $response;
        }

        // 2. VALIDACIONES DE DATOS (PROVEEDOR, EMPRESA, XML)
        $errorMessages = [];

        // Validar Emisor RFC
        $rfcEmisor = mb_strtoupper($dataXML['Emisor']['Rfc'] ?? '', 'UTF-8');
        $rfcProveedor = mb_strtoupper($dataProveedor['RFC'] ?? '', 'UTF-8');
        if ($rfcEmisor !== $rfcProveedor) {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- RFC Emisor: $rfcEmisor vs Proveedor: $rfcProveedor</b>";
            }
            $response["isValid"] = false;
            $errorMessages[] = "* El RFC del emisor no coincide. Se esperaba <b>$rfcProveedor</b>, pero se recibió <b> $rfcEmisor</b>.<br>";
        } elseif ($this->debug == 1) {
            echo "<br> * OK - RFC Emisor: $rfcEmisor vs Proveedor: $rfcProveedor";
        }

        // Validar Emisor RazonSocial
        $razonEmisor = mb_strtoupper($dataXML['Emisor']['Nombre'] ?? '', 'UTF-8');
        $razonProveedor = mb_strtoupper($dataProveedor['RazonSocial'] ?? '', 'UTF-8');
        if ($razonEmisor !== $razonProveedor) {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- Razon Social Emisor: $razonEmisor vs Proveedor: $razonProveedor</b>";
            }
            $response["isValid"] = false;
            $errorMessages[] = "* La Razón Social del emisor no coincide. Se esperaba <b>$razonProveedor</b>, pero se recibió <b>$razonEmisor</b>.<br>";
        } elseif ($this->debug == 1) {
            echo "<br> * OK - Razon Social Emisor: $razonEmisor vs Proveedor: $razonProveedor";
        }

        // Validar Receptor RFC
        $rfcReceptor = mb_strtoupper($dataXML['Receptor']['Rfc'] ?? '', 'UTF-8');
        $rfcEmpresa = mb_strtoupper($dataEmpresa['rfc'] ?? '', 'UTF-8');
        if ($rfcReceptor !== $rfcEmpresa) {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- RFC Receptor: $rfcReceptor vs Empresa: $rfcEmpresa</b>";
            }
            $response["isValid"] = false;
            $errorMessages[] = "* El RFC del receptor no coincide. Se esperaba <b>$rfcEmpresa</b>, pero se recibió <b> $rfcReceptor</b>.<br>";
        } elseif ($this->debug == 1) {
            echo "<br> * OK - RFC Receptor: $rfcReceptor vs Empresa: $rfcEmpresa";
        }

        // Validar Receptor Razón Social
        $razonReceptor = mb_strtoupper($dataXML['Receptor']['Nombre'] ?? '', 'UTF-8');
        $razonEmpresa = mb_strtoupper($dataEmpresa['razonSocial'] ?? '', 'UTF-8');
        if ($razonReceptor !== $razonEmpresa) {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- Razon Social Receptor: $razonReceptor vs Empresa: $razonEmpresa</b>";
            }
            $response["isValid"] = false;
            $errorMessages[] = "* La Razón Social del receptor no coincide. Se esperaba <b>$razonEmpresa</b>, pero se recibió <b>$razonReceptor</b>.<br>";
        } elseif ($this->debug == 1) {
            echo "<br> * OK - Razon Social Receptor: $razonReceptor vs Empresa: $razonEmpresa";
        }

        // Generar mensaje final
        if (!empty($errorMessages)) {
            $response["message"] = implode("", $errorMessages);
        } else {
            $response["message"] = "Validaciones internas de la Nota de Crédito OK.";
        }

        return $response;
    }

    public function validarReglasNegocioNacional_Egresos($dataXML, $configParaValidaciones)
    {
        $response = [
            "success" => false, // Se inicia en false. Solo se pondrá en true al final.
            "message" => "La función de validación de negocio no se completó.", // Mensaje por defecto
            "isValid" => false, // Se inicia en false por seguridad
            "debug" => ""
        ];

        if ($this->debug == 1) {
            echo "<br><br>=======================<br>Inicia Validación de Reglas Internas Nacional Egresos...<br>";
        }

        // Validar entradas
        if (!is_array($dataXML) || empty($dataXML)) {
            $response["message"] = "* No hay datos del XML de la Nota de Crédito.";
            return $response;
        }

        if (!is_array($configParaValidaciones) || empty($configParaValidaciones)) {
            $response["message"] = "* La configuración para las validaciones no está disponible.";
            return $response;
        }

        $errorMessages = [];
        $debugMessages = [];

        // 1. Validar el Tipo de CFDI
        $tipoDeComprobante = $dataXML['Comprobante']['TipoDeComprobante'] ?? '';
        if ($tipoDeComprobante !== 'E') {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- El CFDI no es de Egreso, es tipo $tipoDeComprobante.</b>";
            }
            $errorMessages[] = "* El CFDI no es de Egreso, es tipo <b>$tipoDeComprobante</b>.<br>";
        } else {
            if ($this->debug == 1) {
                echo "<br> * OK - El CFDI es de Egreso. Tipo <b>$tipoDeComprobante</b>.";
            }
            $debugMessages[] = "OK - Tipo de Comprobante: <b>$tipoDeComprobante</b>.";
        }

        // 2. Validar el Uso de CFDI
        $usoCFDI = $dataXML['Receptor']['UsoCFDI'] ?? '';
        if ($usoCFDI !== 'G02') {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- El Uso del CFDI debe ser 'G02', pero se recibió $usoCFDI.</b>";
            }
            $errorMessages[] = "* El Uso del CFDI debe ser 'G02' (Devoluciones, descuentos o bonificaciones), pero se recibió <b>$usoCFDI</b>.<br>";
        } else {
            if ($this->debug == 1) {
                echo "<br> * OK - El Uso del CFDI es correcto: <b>$usoCFDI</b>.";
            }
            $debugMessages[] = "OK - Uso de CFDI: <b>$usoCFDI</b>.";
        }

        // 3. Validar CFDI Relacionado
        $uuidFacturaOriginal = $configParaValidaciones['facturaOriginal']['FacUUID'] ?? null;
        $relacionEncontrada = false;

        if (empty($dataXML['CfdiRelacionados']) || !is_array($dataXML['CfdiRelacionados'])) {
            $errorMessages[] = "* La Nota de Crédito no tiene una sección 'CfdiRelacionados', la cual es obligatoria.<br>";
        } else {
            // Recorremos todos los nodos de 'CfdiRelacionados'
            foreach ($dataXML['CfdiRelacionados'] as $relacion) {
                // Buscamos específicamente el tipo de relación '01'
                if (isset($relacion['TipoRelacion']) && $relacion['TipoRelacion'] === '01') {
                    // Verificamos si el UUID de la factura original está en la lista de UUIDs de esta relación
                    // Convertir todos los UUIDs relacionados a mayúsculas para una comparación case-insensitive
                    $uuidsRelacionadosMayusculas = array_map('strtoupper', $relacion['UUIDs']);
                    if (!empty($uuidsRelacionadosMayusculas) && is_array($uuidsRelacionadosMayusculas) && in_array(strtoupper($uuidFacturaOriginal), $uuidsRelacionadosMayusculas)) {
                        $relacionEncontrada = true;
                        break; // Salimos del bucle una vez que encontramos la relación correcta
                    }
                }
            }

            if ($relacionEncontrada) {
                if ($this->debug == 1) {
                    echo "<br> * OK - La Nota de Crédito está correctamente relacionada (<b>Tipo 01</b>) con la factura original (<b>$uuidFacturaOriginal</b>).";
                }
                $debugMessages[] = "OK - CFDI Relacionado: La NC apunta correctamente a la factura <b>$uuidFacturaOriginal</b>.";
            } else {
                if ($this->debug == 1) {
                    echo "<br> <b>* ERROR -- No se encontró la relación de Tipo '01' con el UUID de la factura original ($uuidFacturaOriginal).</b>";
                }
                $errorMessages[] = "* La Nota de Crédito no está relacionada correctamente con la factura original (UUID: <b>$uuidFacturaOriginal</b>, Tipo de Relación: <b>01</b>).<br>";
            }
        }

        // 4. Validar Montos
        $totalNC = (float)($dataXML['Comprobante']['Total'] ?? 0);
        $idCompraOriginal = $configParaValidaciones['facturaOriginal']['acuse'];

        // Obtener los totales actualizados de la compra
        $cfdis_Mdl = new CFDIs_Mdl();
        $totalesCompra = $cfdis_Mdl->obtenerTotalesPorCompra($idCompraOriginal);

        if (!$totalesCompra['success']) {
            if ($this->debug == 1) {
                echo "<br> <b>* ERROR -- No se pudo calcular el saldo de la factura original para validar la Nota de Crédito.</b>";
            }
            $errorMessages[] = "* No se pudo calcular el saldo de la factura original para validar la Nota de Crédito.<br>";
        } else {
            if ($this->debug == 1) {
                echo "<br> * OK - Totales de la factura original obtenidos correctamente.";
            }
            $saldoReal = (float)($totalesCompra['data']['saldoCalculado'] ?? 0);
            $saldoReal_f = number_format($saldoReal, 2);
            $debugMessages[] = "OK - Saldo Real de la Factura: $ $saldoReal_f (<b>Total: {$totalesCompra['data']['totalFactura']} - NCs: {$totalesCompra['data']['totalNotasCredito']} - Pagos: {$totalesCompra['data']['totalPagado']}</b>).";

            if ($totalNC > $saldoReal) {
                $totalNC_f = number_format($totalNC, 2);
                $saldoReal_f = number_format($saldoReal, 2);
                if ($this->debug == 1) {
                    echo "<br> <b>* ERROR -- El total de la Nota de Crédito ($$totalNC_f) es mayor al saldo pendiente de la factura original ($$saldoReal_f).</b>";
                }
                $errorMessages[] = "* El total de la Nota de Crédito (<b>$$totalNC_f</b>) no puede ser mayor al saldo pendiente de la factura original (<b>$$saldoReal_f</b>).<br>";
            } else {
                if ($this->debug == 1) {
                    echo "<br> * OK - El total de la Nota de Crédito (<b>$$totalNC</b>) es válido contra el saldo de la factura (<b>$$saldoReal_f</b>).";
                }
                $debugMessages[] = "OK - Montos: El total de la NC es válido contra el saldo de la factura.";
            }
        }

        // --- Evaluación Final ---
        if (empty($errorMessages)) {
            // Solo si no hubo errores, la validación es exitosa
            $response["isValid"] = true;
            $response["message"] = "Validaciones de negocio de la Nota de Crédito OK.";
        } else {
            // Si hubo errores, se mantiene isValid = false y se listan los problemas
            $response["message"] = implode("", $errorMessages);
        }

        $response["debug"] = implode("<br>", $debugMessages);
        $response["success"] = true; // La función se ejecutó completamente
        return $response;
    }
}
