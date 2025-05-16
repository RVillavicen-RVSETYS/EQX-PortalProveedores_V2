<?php
use App\Models\DatosCFDIs\CFDIs_Mdl;

class ReglasAplicadasv40
{
    protected $debug = 0;

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

        if($this->debug == 1) {
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
                if($this->debug == 1) {
                    echo "<br> * ERROR -- El UUID de la Factura ya existe en la base de datos.";
                }
                $acuse = $obtenerFacturas['data'][0]['acuse'] ?? 'N/A';
                $response["success"] = false;
                $response["message"] = "Esa Factura ya fue registrada en el acuse: {$acuse}.";
                $response["debug"] = " * ERROR - El UUID dla Factura ya existe en la base de datos.";
                return $response;
            } else {
                if($this->debug == 1) {
                    echo "<br> * El UUID de la Factura no existe en la base de datos.";
                }
            }
        } else {
            if($this->debug == 1) {
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

    public function validarReglasInternasNacional_Pagos($dataProveedor, $dataEmpresa, $dataXML, $dataCompras, $configParaValidaciones = [])
    {
        $this->debug = 1; // Cambia esto a 0 para desactivar el modo de depuración
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

        // VALIDAR AQUI SI EL UUID DEL COMPLEMENTO DE PAGO YA EXISTE EN LA BD
        if (empty($dataXML['TimbreFiscal']['UUID'])) {
            $response["message"] = "El nodo UUID del Timbre Fiscal no existe o está vacío.";
            $response["debug"] = " * ERROR - El nodo UUID del Timbre Fiscal no está presente en el XML.";
            return $response;
        }

        if($this->debug == 1) {
            echo "<br>UUID del Timbre Fiscal: " . $dataXML['TimbreFiscal']['UUID'];
            echo "<br>Validamos si el Complemento de Pago ya existe en la base de datos...";
        }
        $cfdis_Mdl = new CFDIs_Mdl();
        $filtrosComplemento = [
            'uuids' => $dataXML['TimbreFiscal']['UUID'] ?? null,
        ];
        $obtenerCompDePago = $cfdis_Mdl->obtenerComplementosDePago($filtrosComplemento);
        if ($obtenerCompDePago['success']) {
            if ($obtenerCompDePago['cantRes'] > 0) {
                if($this->debug == 1) {
                    echo "<br> * ERROR -- El UUID del complemento de pago ya existe en la base de datos.";
                }
                $fechaRegistro = $obtenerCompDePago['data'][0]['fechaReg'] ?? 'N/A';
                $response["success"] = false;
                $response["message"] = "Ese complemento de pago ya fue registrado el {$fechaRegistro}.";
                $response["debug"] = " * ERROR - El UUID del complemento de pago ya existe en la base de datos.";
                return $response;
            } else {
                if($this->debug == 1) {
                    echo "<br> * El UUID del complemento de pago no existe en la base de datos.";
                }
            }
        } else {
            if($this->debug == 1) {
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
        $this->debug = 1; // Cambia esto a 0 para desactivar el modo de depuración
        
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
    
        // 1) Agrupar $dataPagos por uuid
        if ($this->debug == 1) {
            echo "<br>Agrupando \$dataPagos por uuid...";
        }
        $pagosGrouped = [];
        foreach ($dataPagos as $p) {
            $key = $p['uuid'];
            if (!isset($pagosGrouped[$key])) {
                $pagosGrouped[$key] = [
                    'monto'  => 0.0,
                    'moneda' => $p['moneda'],
                    'fechas' => [],
                    'formas' => [],
                ];
            }
            $pagosGrouped[$key]['monto']  += floatval($p['montoPagado']);
            $pagosGrouped[$key]['fechas'][] = $p['fechaPago'];
            $pagosGrouped[$key]['formas'][] = $p['formaPago'];
        }
        foreach ($pagosGrouped as &$grp) {
            $grp['formas'] = array_unique($grp['formas']);
            $grp['fechas'] = array_unique($grp['fechas']);
        }
        unset($grp);
        if ($this->debug == 1) {
            echo "<br> * Pagos agrupados: " . count($pagosGrouped) . " uuid(s).<br>";
        }
    
        // 2) Agrupar XML por IdDocumento
        if ($this->debug == 1) {
            echo "<br>Agrupando datos del XML por IdDocumento...";
        }
        $xmlGrouped = [];
        foreach ($dataXML['Pagos']['Pagos'] as $pagoNodo) {
            $formaXML  = $pagoNodo['FormaDePagoP'];
            $monedaXML = $pagoNodo['MonedaP'];
            $fechaXML  = substr($pagoNodo['FechaPago'], 0, 10);
            foreach ($pagoNodo['DoctosRelacionados'] as $dr) {
                $doc = $dr['IdDocumento'];
                if (!isset($xmlGrouped[$doc])) {
                    $xmlGrouped[$doc] = [
                        'monto'  => 0.0,
                        'moneda' => $monedaXML,
                        'fechas' => [],
                        'formas' => [],
                    ];
                }
                $xmlGrouped[$doc]['monto']  += floatval($dr['ImpPagado']);
                $xmlGrouped[$doc]['fechas'][] = $fechaXML;
                $xmlGrouped[$doc]['formas'][] = $formaXML;
            }
        }
        foreach ($xmlGrouped as &$grp) {
            $grp['formas'] = array_unique($grp['formas']);
            $grp['fechas'] = array_unique($grp['fechas']);
        }
        unset($grp);
        if ($this->debug == 1) {
            echo "<br> * XML agrupado: " . count($xmlGrouped) . " documento(s).<br>";
        }
    
        // 3) Comparar cada documento
        if ($this->debug == 1) {
            echo "<br> Inicia Comparación por Documento...<br>";
        }
        foreach ($xmlGrouped as $docId => $xmlData) {
            if ($this->debug == 1) {
                echo "<br>---- Procesando UUID: {$docId} ----<br>";
            }
            $out = [
                'montoXML'   => round($xmlData['monto'], 2),
                'montoPago'  => null,
                'monedaXML'  => $xmlData['moneda'],
                'monedaPago' => null,
                'formasXML'  => $xmlData['formas'],
                'formasPago' => [],
                'fechasXML'  => $xmlData['fechas'],
                'fechasPago' => [],
                'coincide'   => [
                    'monto'  => false,
                    'moneda' => false,
                    'formas' => false,
                    'fechas' => false,
                ],
            ];
    
            if (isset($pagosGrouped[$docId])) {
                $pagoData = $pagosGrouped[$docId];
                $out['montoPago']  = round($pagoData['monto'], 2);
                $out['monedaPago'] = $pagoData['moneda'];
                $out['formasPago'] = $pagoData['formas'];
                $out['fechasPago'] = $pagoData['fechas'];
    
                if ($this->debug == 1) {
                    echo " * Monto XML: {$out['montoXML']} vs Pago: {$out['montoPago']}<br>";
                    echo " * Moneda XML: {$out['monedaXML']} vs Pago: {$out['monedaPago']}<br>";
                    echo " * Formas XML: [" . implode(',', $out['formasXML']) . "] vs Pago: [" . implode(',', $out['formasPago']) . "]" .
                     (!empty($configParaValidaciones['Excepciones']['NoValidarFormasPago']) ? " <b>*** Aplica Excepción</b>" : "") . "<br>";
                    echo " * Fechas XML: [" . implode(',', $out['fechasXML']) . "] vs Pago: [" . implode(',', $out['fechasPago']) . "]" .
                     (!empty($configParaValidaciones['Excepciones']['NoValidarFechasPago']) ? " <b>*** Aplica Excepción</b>" : "") . "<br>";
                }
    
                // Validaciones
                $out['coincide']['monto']  = ($out['montoPago']  === $out['montoXML']);
                $out['coincide']['moneda'] = ($out['monedaPago'] === $out['monedaXML']);
                sort($out['formasPago']);
                sort($out['formasXML']);
                $out['coincide']['formas'] = ($out['formasPago'] === $out['formasXML']);
                sort($out['fechasPago']);
                sort($out['fechasXML']);
                $out['coincide']['fechas'] = ($out['fechasPago'] === $out['fechasXML']);
    
                if (!$out['coincide']['monto']) {
                    $errores[] = "* UUID {$docId}: monto XML ({$out['montoXML']}) ≠ pago ({$out['montoPago']}).";
                }
                if (!$out['coincide']['moneda']) {
                    $errores[] = "* UUID {$docId}: moneda XML ({$out['monedaXML']}) ≠ pago ({$out['monedaPago']}).";
                }
                if (!$out['coincide']['formas'] 
                    && empty($configParaValidaciones['Excepciones']['NoValidarFormasPago'])
                ) {
                    $errores[] = "* UUID {$docId}: formas diferentes: XML=[" . implode(',', $out['formasXML']) . "] vs pago=[" . implode(',', $out['formasPago']) . "].";
                }
                if (!$out['coincide']['fechas'] 
                    && empty($configParaValidaciones['Excepciones']['NoValidarFechasPago'])
                ) {
                    $errores[] = "* UUID {$docId}: fechas diferentes: XML=[" . implode(',', $out['fechasXML']) . "] vs pago=[" . implode(',', $out['fechasPago']) . "].";
                }
            } else {
                if ($this->debug == 1) {
                    echo " * ERROR: UUID {$docId} sin pagos asociados.<br>";
                }
                $errores[] = "* UUID {$docId} presente en XML pero sin pagos asociados.";
            }
        }
    
        // 4) Detectar UUID extras en dataPagos
        if ($this->debug == 1) {
            echo "<br>--> Detectando UUID en pagos no presentes en XML...<br>";
        }
        $extras = array_diff(array_keys($pagosGrouped), array_keys($xmlGrouped));
        foreach ($extras as $uuidExtra) {
            if ($this->debug == 1) {
                echo " * EXTRA: UUID {$uuidExtra} en pagos sin XML.<br>";
            }
            $errores[] = " * UUID {$uuidExtra} presente en pagos pero no en el XML.";
        }
    
        // 5) Fin validación
        if ($this->debug == 1) {
            echo "<br>=== Fin Validación de Pagos. Errores encontrados: " . count($errores) . " ===<br>";
        }

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
                $xmlMoneda = $xmlGrouped[$uuidFactura]['moneda'];
        
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
            $response['message'] = "Todo OK";
            $response['debug']   = "No se encontraron errores.";
        }
    
        return $response;
    }
    
    
}
