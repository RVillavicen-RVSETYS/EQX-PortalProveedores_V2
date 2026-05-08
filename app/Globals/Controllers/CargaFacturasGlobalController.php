<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Globals\Controllers\SubirFacturaController;

class CargaFacturasGlobalController extends Controller
{
    protected $debug = 0; // Debug activo (carga factura / admin)

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de Globals\Controllers\CargaFacturasGlobalController.php.</h2>";
        }
    }

    public function cargaFormNotaCredito()
    {
        $data = [];
        // Cargar la vista correspondiente
        $this->view('VistasCompartidas/NotasCredito_Form', $data);
    }

    public function registraNuevaFactura($postData, $filesData, $noProveedor, $isAdmin)
    {
        if ($this->debug == 1) {
            echo '<br>---- CargaFacturasGlobalController -> registraNuevaFactura----<br>';
            echo '<br>----postData----<br>';
            print_r($postData);
            echo '<br>----filesData----<br>';
            print_r($filesData);
            echo "<br>----noProveedor: $noProveedor ----<br>";
            echo "<br>----isAdmin: " . ($isAdmin ? 'true' : 'false') . " ----<br>";
        }

        // Validaciones iniciales (Guard Clauses)
        if (empty($noProveedor)) {
            echo json_encode(['success' => false, 'message' => 'Error Crítico: El número de proveedor no fue encontrado.']);
            return;
        }

        $ordenCompra = $postData['ordenCompra'] ?? '';
        if (empty($ordenCompra)) {
            echo json_encode(['success' => false, 'message' => 'El campo Orden de Compra es obligatorio.']);
            return;
        }

        if (empty($filesData['facturaXML']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'El archivo XML de la factura es obligatorio.']);
            return;
        }

        if (empty($filesData['facturaPDF']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'El archivo PDF de la factura es obligatorio.']);
            return;
        }

        // HES separado por comas
        $hes_raw = $postData['listaHES'] ?? '';
        $hes = implode(',', array_filter(array_map('trim', preg_split('/[\r\n]+/', $hes_raw))));

        // ExcepcionesAdmin (only if admin)
        $excepcionesAdmin = $isAdmin ? ($postData['excepcionesAdmin'] ?? []) : [];

        // NotasCredito
        $notasCreditoPost = $postData['notaCredito'] ?? [];
        $archivosNotas = $filesData['notaCreditoArchivo'] ?? [];
        $notasCredito = [];

        foreach ($notasCreditoPost as $id => $notasArray) {
            $identNotasCred = implode(',', $notasArray);
            $pdfArray = [];
            $xmlArray = [];

            if (isset($archivosNotas['name'][$id]['pdf']) && is_array($archivosNotas['name'][$id]['pdf'])) {
                foreach ($archivosNotas['name'][$id]['pdf'] as $idx => $namePdf) {
                    $pdfArray[] = ['name' => $namePdf, 'tmp_name' => $archivosNotas['tmp_name'][$id]['pdf'][$idx] ?? null];
                }
            } else {
                $pdfArray[] = ['name' => $archivosNotas['name'][$id]['pdf'] ?? '', 'tmp_name' => $archivosNotas['tmp_name'][$id]['pdf'] ?? null];
            }

            if (isset($archivosNotas['name'][$id]['xml']) && is_array($archivosNotas['name'][$id]['xml'])) {
                foreach ($archivosNotas['name'][$id]['xml'] as $idx => $nameXml) {
                    $xmlArray[] = ['name' => $nameXml, 'tmp_name' => $archivosNotas['tmp_name'][$id]['xml'][$idx] ?? null];
                }
            } else {
                $xmlArray[] = ['name' => $archivosNotas['name'][$id]['xml'] ?? '', 'tmp_name' => $archivosNotas['tmp_name'][$id]['xml'] ?? null];
            }

            $notasCredito[$id] = [
                'identNotasCred' => $identNotasCred,
                'documentos' => ['PDF' => $pdfArray, 'XML' => $xmlArray],
            ];
        }

        // Archivos Factura
        $facturaPDF = [];
        $facturaXML = [];

        if (isset($filesData['facturaPDF'])) {
            $facturaPDF[] = ['name' => $filesData['facturaPDF']['name'], 'tmp_name' => $filesData['facturaPDF']['tmp_name']];
        }

        if (isset($filesData['facturaXML'])) {
            $facturaXML[] = ['name' => $filesData['facturaXML']['name'], 'tmp_name' => $filesData['facturaXML']['tmp_name']];
        }

        // Construcción arreglo final
        $valores = [
            'ordenCompra' => $ordenCompra,
            'noProveedor' => $noProveedor,
            'listaHES' => $hes,
            'ExcepcionesAdmin' => $excepcionesAdmin,
            'NotasCredito' => $notasCredito,
            'Factura' => [
                'FactPDF' => $facturaPDF,
                'FactXML' => $facturaXML,
            ],
            'isAdmin' => $isAdmin,
        ];

        $Ctrl_SubirFacturas = new SubirFacturaController();
        return $Ctrl_SubirFacturas->cargarFacturas($valores);
    }

    public function registraNuevaNotaCredito($postData, $filesData, $isAdmin)
    {
        if ($this->debug == 1) {
            echo '<br>---- CargaFacturasGlobalController -> registraNuevaNotaCredito ----<br>';
            echo '<br>----postData----<br>';
            print_r($postData);
            echo '<br>----filesData----<br>';
            print_r($filesData);
            echo "<br>----isAdmin: " . ($isAdmin ? 'true' : 'false') . " ----<br>";
        }

        // 1.- Validaciones iniciales y obtención de datos
        $noProveedor = $isAdmin ? ($postData['noProveedorNC'] ?? '') : ($_SESSION['EQXnoProveedor'] ?? '');
        $ordenCompra = $postData['ordenCompraNC'] ?? '';
        $idCompra = $postData['idCompraNC'] ?? ''; // ID de la factura a la que se aplica la NC
        $notasCreditoPost = $postData['notaCredito'] ?? [];
        $archivosNotas = $filesData['notaCreditoArchivo'] ?? [];

        if (empty($noProveedor) || empty($ordenCompra) || empty($idCompra) || empty($notasCreditoPost)) {
            echo json_encode(['success' => false, 'message' => 'Error Crítico: Faltan datos esenciales (Proveedor, OC, Factura Ingresada o Notas de Crédito).']);
            return;
        }

        // 2.- Reestructurar datos para un procesamiento más sencillo
        $notasParaProcesar = [];
        foreach ($notasCreditoPost as $idPlantilla => $notasSeleccionadas) {
            if (!isset($archivosNotas['name'][$idPlantilla])) {
                continue;
            }
            // Si hay múltiples políticas seleccionadas, las unimos con coma para guardarlas en idNCExterno
            // Esto permite que una NC cubra múltiples conceptos/políticas
            $idNotaCredito = is_array($notasSeleccionadas) && count($notasSeleccionadas) > 1
                ? implode(',', array_map('intval', $notasSeleccionadas))
                : (is_array($notasSeleccionadas) ? $notasSeleccionadas[0] : $notasSeleccionadas);

            $notasParaProcesar[] = [
                'idPlantilla' => $idPlantilla,
                'idNotaCredito' => $idNotaCredito, // Puede ser un ID único o múltiples IDs separados por coma
                'pdf' => [
                    'name' => $archivosNotas['name'][$idPlantilla]['pdf'] ?? '',
                    'type' => $archivosNotas['type'][$idPlantilla]['pdf'] ?? '',
                    'tmp_name' => $archivosNotas['tmp_name'][$idPlantilla]['pdf'] ?? '',
                    'error' => $archivosNotas['error'][$idPlantilla]['pdf'] ?? '',
                    'size' => $archivosNotas['size'][$idPlantilla]['pdf'] ?? ''
                ],
                'xml' => [
                    'name' => $archivosNotas['name'][$idPlantilla]['xml'] ?? '',
                    'type' => $archivosNotas['type'][$idPlantilla]['xml'] ?? '',
                    'tmp_name' => $archivosNotas['tmp_name'][$idPlantilla]['xml'] ?? '',
                    'error' => $archivosNotas['error'][$idPlantilla]['xml'] ?? '',
                    'size' => $archivosNotas['size'][$idPlantilla]['xml'] ?? ''
                ]
            ];
        }

        if (empty($notasParaProcesar)) {
            echo json_encode(['success' => false, 'message' => 'No se encontraron notas de crédito con archivos para procesar.']);
            return;
        }

        // --- INICIA LÓGICA DE PROCESAMIENTO ---
        $Ctrl_Documentos = new DocumentosController();
        $Ctrl_CFDIs = new CfdisController();
        $Ctrl_ProcesaNotasCredito = new FacturasNacionalesController();

        $resultados = [];

        // 3.- Bucle de procesamiento para cada nota de crédito
        foreach ($notasParaProcesar as $nota) {
            if ($this->debug == 1) {
                echo "<br>--- Procesando Nota de Crédito de Plantilla #{$nota['idPlantilla']} ---<br>";
            }

            // 3.1.- Verificamos el PDF y XML
            $pdfVerificado = $Ctrl_Documentos->verificadorDeDocumentoARecibir($nota['pdf'], 'pdf');
            if (!$pdfVerificado['success']) {
                $resultados[] = ['success' => false, 'message' => "Error en PDF de plantilla #{$nota['idPlantilla']}: " . $pdfVerificado['message']];
                break;
            } else {
                if ($this->debug == 1) {
                    echo "<br>3.1.- PDF verificado correctamente para plantilla #{$nota['idPlantilla']}.<br>";
                }
            }

            $xmlVerificado = $Ctrl_Documentos->verificadorDeDocumentoARecibir($nota['xml'], 'xml');
            if (!$xmlVerificado['success']) {
                $resultados[] = ['success' => false, 'message' => "Error en XML de plantilla #{$nota['idPlantilla']}: " . $xmlVerificado['message']];
                break;
            } else {
                if ($this->debug == 1) {
                    echo "<br>3.1.- XML verificado correctamente para plantilla #{$nota['idPlantilla']}.<br>";
                }
            }

            // 3.2.- Leer el XML
            $dataNotaCredXML = $Ctrl_CFDIs->leerCfdiXML($xmlVerificado['data']['tmp_name'], 'Egreso');
            if (!$dataNotaCredXML['success']) {
                $resultados[] = ['success' => false, 'message' => "Error al leer XML de plantilla #{$nota['idPlantilla']}: " . $dataNotaCredXML['message']];
                break;
            } else {
                if ($this->debug == 1) {
                    echo "<br>3.2.- XML leído correctamente para plantilla #{$nota['idPlantilla']}.<br>";
                }
            }

            // 3.3.- Llamar a la validación de la Nota de Crédito
            $notaValidada = $Ctrl_ProcesaNotasCredito->verificaNuevaNotaCredito(
                $dataNotaCredXML,
                $noProveedor,
                $idCompra,
                $nota['idNotaCredito'],
                $isAdmin
            );

            if (!$notaValidada['success']) {
                if ($this->debug == 1) {
                    echo "<br>3.3.- Error de validación en NC de plantilla #{$nota['idPlantilla']}: " . $notaValidada['message'] . "<br>";
                }
                $resultados[] = ['success' => false, 'message' => "Error de validación en NC {$nota['idPlantilla']}: " . $notaValidada['message']];
                break;
            } else {
                if ($this->debug == 1) {
                    echo "<br>3.3.- Nota de Crédito validada correctamente para plantilla #{$nota['idPlantilla']}.<br>";
                }
            }

            // 3.4.- Llamar al registro de la Nota de Crédito
            $datosParaRegistrar = $notaValidada['data'];
            $datosParaRegistrar['ruta_temporal_pdf'] = $pdfVerificado['data']['tmp_name'];
            $datosParaRegistrar['ruta_temporal_xml'] = $xmlVerificado['data']['tmp_name'];
            $datosParaRegistrar['idCompra'] = $idCompra;

            $notaRegistrada = $Ctrl_ProcesaNotasCredito->registraNuevaNotaCredito($datosParaRegistrar);

            if (!$notaRegistrada['success']) {
                $resultados[] = ['success' => false, 'message' => "Error al registrar NC de plantilla #{$nota['idPlantilla']}: " . $notaRegistrada['message']];
                break;
            } else {
                if ($this->debug == 1) {
                    echo "<br>3.4.- Nota de Crédito registrada correctamente para plantilla #{$nota['idPlantilla']}.<br>";
                }
            }

            $resultados[] = ['success' => true, 'message' => "Nota de Crédito registrada con éxito."];
        }

        // 4.- Evaluar resultados y responder
        $todosExitosos = true;
        $mensajes = [];
        foreach ($resultados as $res) {
            $mensajes[] = $res['message'];
            if (!$res['success']) {
                $todosExitosos = false;
            }
        }

        echo json_encode(['success' => $todosExitosos, 'message' => implode('<br>', $mensajes)]);
        return;
    }

    public function registraNuevoComplementoPago($postData, $filesData, $isAdmin)
    {
        $this->debug = 0;
        if ($this->debug == 1) {
            echo '<br>---- CargaFacturasGlobalController -> registraNuevoComplementoPago ----<br>';
            echo '<br>----postData----<br>';
            print_r($postData);
            echo '<br>----filesData----<br>';
            print_r($filesData);
            echo "<br>----isAdmin: " . ($isAdmin ? 'true' : 'false') . " ----<br>";
        }

        // 1.- Validaciones iniciales y obtención de datos
        if ($this->debug == 1) {
            echo '<br>===== 1.- Validaciones iniciales y obtención de datos =====<br>';
        }

        $noProveedor = $isAdmin ? ($postData['noProveedorCP'] ?? '') : ($_SESSION['EQXnoProveedor'] ?? '');
        $complementoPagoPDF = $filesData['complementoPagoPDF'] ?? null;
        $complementoPagoXML = $filesData['complementoPagoXML'] ?? null;

        if (empty($noProveedor)) {
            echo json_encode(['success' => false, 'message' => 'Error Crítico: El número de proveedor es obligatorio.']);
            return;
        }

        if (empty($complementoPagoPDF) || empty($complementoPagoXML)) {
            echo json_encode(['success' => false, 'message' => 'Error: Los archivos PDF y XML del Complemento de Pago son obligatorios.']);
            return;
        }

        if (empty($complementoPagoPDF['tmp_name']) || empty($complementoPagoXML['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Error: Los archivos del Complemento de Pago no se recibieron correctamente.']);
            return;
        }

        // 2.- Verificar archivos (PDF y XML)
        if ($this->debug == 1) {
            echo '<br>===== 2.- Verificar archivos (PDF y XML) =====<br>';
        }
        $Ctrl_Documentos = new DocumentosController();
        $Ctrl_CFDIs = new CfdisController();
        $Ctrl_ProcesaComplementoPago = new FacturasNacionalesController();

        // 2.1.- Verificar PDF
        $pdfVerificado = $Ctrl_Documentos->verificadorDeDocumentoARecibir($complementoPagoPDF, 'pdf');
        if (!$pdfVerificado['success']) {
            echo json_encode(['success' => false, 'message' => 'Error en PDF del Complemento de Pago: ' . $pdfVerificado['message']]);
            return;
        }

        // 2.2.- Verificar XML
        $xmlVerificado = $Ctrl_Documentos->verificadorDeDocumentoARecibir($complementoPagoXML, 'xml');
        if (!$xmlVerificado['success']) {
            echo json_encode(['success' => false, 'message' => 'Error en XML del Complemento de Pago: ' . $xmlVerificado['message']]);
            return;
        }

        // 3.- Leer el XML para obtener información básica
        if ($this->debug == 1) {
            echo '<br>===== 3.- Leer el XML para obtener información básica =====<br>';
        }
        $dataComplementoXML = $Ctrl_CFDIs->leerCfdiXML($xmlVerificado['data']['tmp_name'], 'Pago');
        if (!$dataComplementoXML['success']) {
            echo json_encode(['success' => false, 'message' => 'Error al leer XML del Complemento de Pago: ' . $dataComplementoXML['message']]);
            return;
        }

        // 4.- Validar el Complemento de Pago (sin requerir facturas relacionadas)
        if ($this->debug == 1) {
            echo '<br>===== 4.- Validar el Complemento de Pago (sin requerir facturas relacionadas) =====<br>';
        }
        $complementoValidado = $Ctrl_ProcesaComplementoPago->verificaNuevoComplementoPago(
            $complementoPagoPDF,
            $complementoPagoXML,
            $noProveedor,
            $isAdmin ? 1 : 0,
            [] // reglasAdmin vacío por ahora
        );

        if (!$complementoValidado['success']) {
            echo json_encode(['success' => false, 'message' => 'Error de validación del Complemento de Pago: ' . $complementoValidado['message']]);
            return;
        }

        // 5.- Registrar el Complemento de Pago
        if ($this->debug == 1) {
            echo '<br>===== 5.- Registrar el Complemento de Pago =====<br>';
        }
        $datosParaRegistrar = $complementoValidado['data'];
        $datosParaRegistrar['ruta_temporal_pdf'] = $pdfVerificado['data']['tmp_name'];
        $datosParaRegistrar['ruta_temporal_xml'] = $xmlVerificado['data']['tmp_name'];

        $complementoRegistrado = $Ctrl_ProcesaComplementoPago->registraNuevoComplementoPago($datosParaRegistrar);

        if (!$complementoRegistrado['success']) {
            echo json_encode(['success' => false, 'message' => 'Error al registrar Complemento de Pago: ' . $complementoRegistrado['message']]);
            return;
        }

        echo json_encode(['success' => true, 'message' => 'Complemento de Pago registrado con éxito.']);
        return;
    }
}
