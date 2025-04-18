<?php
class ReglasAplicadasv40
{
    protected $debug = 1;

    public function validarReglasInternasNacional_Ingresos($dataProveedor, $dataEmpresa, $dataXML) {
        $response = [
            "success" => true,
            "message" => "",
            "isValid" => true,
            "debug" => ""
        ];

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

            if ($mesActual !== $mesPago) {
                $response["isValid"] = false;
                $errorMessages[] = "* No podemos recibir la factura con Método de Pago PUE porque no sería pagable dentro del mismo mes.<br>";
                $debugMessages[] = "ERROR - Validación de Pagabilidad para PUE: Fecha Pago Permitida <b>" . date('Y-m-d', $fechaPagoPermitida) . "</b>, Mes Actual <b>$mesActual</b>, Mes de Pago <b>$mesPago</b>.";
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
        $subtotalXML = $dataXML['Comprobante']['SubTotal'] ?? 0;
        $descuentoXML = $dataXML['Comprobante']['Descuento'] ?? 0;
        $ignoraDescuento = $configParaValidaciones['excepcionesProveedor']['IgnoraDescuento'] ?? false;
        if (!$ignoraDescuento) {
            $subtotalXML = $subtotalXML - $descuentoXML;
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

            if ($subtotalXML < $minimo || $subtotalXML > $maximo) {

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

    
}
