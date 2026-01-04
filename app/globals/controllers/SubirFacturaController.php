<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Globals\Controllers\DocumentosController;
use App\Globals\Controllers\FacturasNacionalesController;
use App\Globals\Controllers\CfdisController;
use App\Models\Proveedores\Proveedores_Mdl;
use BD_Connect;
use PDO;

class SubirFacturaController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\SubirFacturaController.php.</h2>";
        }
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        //$this->checkSessionAdmin();
    }

    public function cargarFacturas($arrayData)
    {
        $noProveedor = $arrayData['noProveedor'] ?? '';
        $ordenCompra = $arrayData['ordenCompra'] ?? '';
        $listaHES = $arrayData['listaHES'] ?? '';
        $excepcionesAdmin = $arrayData['ExcepcionesAdmin'] ?? '';
        $arrayNotasCredito = $arrayData['NotasCredito'] ?? '';
        $factura = $arrayData['Factura'] ?? '';
        $isAdmin = ($arrayData['isAdmin']) ? '1' : '0';
        $doctos = []; //Variable para paso de documentos Validados

        if ($ordenCompra == '' || $listaHES == '') {
            if ($this->debug == 1) {
                echo "<br>Faltan datos:<br> * NoProv: $noProveedor<br> * OC: $ordenCompra<br> * Lista HES: $listaHES<br>";
            }
        }

        //1.- Validamos la Orden de Compra
        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de verificaOrdenCompra: ' . PHP_EOL;
            var_dump($validOrdenCompra);
        }

        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];

            //2.- Verificamos si esta Orden de Compra debe algun anticipo para procesar la nota de credito
            $MDL_anticipos = new Anticipos_Mdl();
            $verificaDebeAnticipo = $MDL_anticipos->verificaAnticipoDeOrdenCompra(['folioCompra' => $ordenCompra]);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de verificaAnticipoDeOrdenCompra: ' . PHP_EOL;
                var_dump($verificaDebeAnticipo);
            }
            if ($verificaDebeAnticipo['success']) {

                //3.- Verificamos que las HES sean Correctas
                $MDL_hojaEntrada = new HojaEntrada_Mdl();
                $validHES = $MDL_hojaEntrada->verificaHojaEntrada($ordenCompra, $listaHES);
                if ($this->debug == 1) {
                    echo '<br><br>Resultado de verificaHojaEntrada: ' . PHP_EOL;
                    var_dump($validHES);
                }
                if ($validHES['success']) {
                    $Ctrl_Documentos = new DocumentosController();

                    //4.- Verificamos si requiere nota de credito
                    $reqNotaCredito = ($verificaDebeAnticipo['cantAnticipos'] == 0) ? 0 : 1;
                    if ($reqNotaCredito > 0) {
                        //4.1.- Revisamos y cargamos OBLIGATORIO la Nota de Credito
                        if ($this->debug == 1) {
                            echo '<br><br>Si requiere Nota de Credito' . PHP_EOL;
                        }

                        foreach ($arrayNotasCredito as $id => $nota) {
                            if ($this->debug == 1) {
                                echo "Plantilla: $id\n";
                                echo "IDs seleccionados: " . $nota['identNotasCred'] . "\n";

                                foreach ($nota['documentos']['PDF'] as $pdf) {
                                    echo "PDF: " . $pdf['name'] . " -> " . $pdf['tmp_name'] . "\n";
                                }
                                foreach ($nota['documentos']['XML'] as $xml) {
                                    echo "XML: " . $xml['name'] . " -> " . $xml['tmp_name'] . "\n";
                                }
                            }

                            // Extraemos los archivos correctos desde $_FILES usando el ID de plantilla
                            $notaCredPDF = [
                                'name'     => $_FILES['notaCreditoArchivo']['name'][$id]['pdf'] ?? '',
                                'type'     => $_FILES['notaCreditoArchivo']['type'][$id]['pdf'] ?? '',
                                'tmp_name' => $_FILES['notaCreditoArchivo']['tmp_name'][$id]['pdf'] ?? '',
                                'error'    => $_FILES['notaCreditoArchivo']['error'][$id]['pdf'] ?? '',
                                'size'     => $_FILES['notaCreditoArchivo']['size'][$id]['pdf'] ?? ''
                            ];

                            $resultadoPDF = $Ctrl_Documentos->verificadorDeDocumentoARecibir($notaCredPDF, 'pdf');

                            if ($this->debug == 1) {
                                echo '<br><br>Resultado de verificadorDeDocumentoARecibir NotaCreditoPDF: ' . PHP_EOL;
                                var_dump($resultadoPDF);
                            }

                            if ($resultadoPDF['success']) {
                                $doctos['NotaCreditoPDF'][$id] = $resultadoPDF['data'];

                                // Repetimos para XML
                                $notaCredXML = [
                                    'name'     => $_FILES['notaCreditoArchivo']['name'][$id]['xml'] ?? '',
                                    'type'     => $_FILES['notaCreditoArchivo']['type'][$id]['xml'] ?? '',
                                    'tmp_name' => $_FILES['notaCreditoArchivo']['tmp_name'][$id]['xml'] ?? '',
                                    'error'    => $_FILES['notaCreditoArchivo']['error'][$id]['xml'] ?? '',
                                    'size'     => $_FILES['notaCreditoArchivo']['size'][$id]['xml'] ?? ''
                                ];

                                $resultadoXML = $Ctrl_Documentos->verificadorDeDocumentoARecibir($notaCredXML, 'xml');

                                if ($this->debug == 1) {
                                    echo '<br><br>Resultado de verificadorDeDocumentoARecibir NotaCreditoXML: ' . PHP_EOL;
                                    var_dump($resultadoXML);
                                }

                                if ($resultadoXML['success']) {
                                    $doctos['NotaCreditoXML'][$id] = $resultadoXML['data'];
                                }
                            } else {
                                echo 'Horror: El PDF de la Nota de Credito tiene problemas: ' . $resultadoPDF['message'];
                            }

                            //echo 'Deberíamos comenzar a exigir recibir una nota de crédito.<br>';
                            //echo 'Validar la relación de la factura con el anticipo.<br>';
                            //echo 'Validar los documentos y cargarlos en la variable $doctos.<br>';
                        }
                    } else {
                        //4.2.- Aqui ya estamos seguros de que no requiere Nota de Credito
                        if ($this->debug == 1) {
                            echo '<br><br><b1>No requiere Nota de Credito</b1>' . PHP_EOL;
                        }
                    }

                    //5.- Verificamos el PDF de la Factura antes de subirlo
                    $factPDF = $Ctrl_Documentos->verificadorDeDocumentoARecibir($_FILES['facturaPDF'], 'pdf');
                    if ($this->debug == 1) {
                        echo '<br><br>Resultado de verificadorDeDocumentoARecibir facturaPDF: ' . PHP_EOL;
                        var_dump($factPDF);
                    }
                    if ($factPDF['success']) {
                        $doctos['FacturaPDF'] = $factPDF['data'];

                        //5.- Verificamos el XML de la Factura antes de subirlo
                        $factXML = $Ctrl_Documentos->verificadorDeDocumentoARecibir($_FILES['facturaXML'], 'xml');
                        if ($this->debug == 1) {
                            echo '<br><br>Resultado de verificadorDeDocumentoARecibir facturaXML: ' . PHP_EOL;
                            var_dump($factXML);
                        }
                        if ($factXML['success']) {
                            $doctos['FacturaXML'] = $factXML['data'];

                            //6.- Aplicar Reglas de Negocio, Fiscales y Carga de CFDI desde el Controlador Global para Proveedores Nacionales
                            $Ctrl_ProcesaFacturas = new FacturasNacionalesController();
                            $facturaProcesada = $Ctrl_ProcesaFacturas->verificaNuevaFacturaIngresos($reqNotaCredito, $ordenCompra, $validHES['hesOK'], $doctos, $isAdmin, $excepcionesAdmin, $noProveedor);
                            if ($this->debug == 1) {
                                echo '<br><br>Resultado de FacturasNacionalesController: ' . PHP_EOL;
                                var_dump($facturaProcesada);
                            }

                            if ($facturaProcesada['success']) {
                                //7.- Registrar Factura de Ingreso
                                $facturaRegistrada = $Ctrl_ProcesaFacturas->registraNuevaFacturaIngresos($facturaProcesada['data']);
                                if ($this->debug == 1) {
                                    echo '<br><br>Registro de Factura: ' . PHP_EOL;
                                    var_dump($facturaRegistrada);
                                }

                                if ($facturaRegistrada['success']) {
                                    $idCompra = $facturaRegistrada['idCompra'] ?? null;
                                    
                                    // 8.- Procesar Notas de Crédito si existen y la factura se registró correctamente
                                    if (!empty($arrayNotasCredito) && !empty($idCompra)) {
                                        $resultadoNC = $this->procesarNotasCreditoConFactura(
                                            $arrayNotasCredito,
                                            $idCompra,
                                            $noProveedor,
                                            $ordenCompra,
                                            $isAdmin,
                                            $facturaRegistrada
                                        );
                                        
                                        if (!$resultadoNC['success']) {
                                            // Si alguna NC falla, hacer rollback de la factura
                                            $this->rollbackFacturaRegistrada($idCompra, $facturaRegistrada);
                                            
                                            echo json_encode([
                                                'success' => false,
                                                'message' => 'La factura se registró pero hubo problemas con las Notas de Crédito. Todo fue revertido: ' . $resultadoNC['message'],
                                                'debug' => $facturaRegistrada['debug'] . '<br>Error en NC: ' . $resultadoNC['message']
                                            ]);
                                            return;
                                        }
                                        
                                        // Si todas las NC se procesaron correctamente
                                        if ($this->debug == 1) {
                                            echo '<br><h1>Factura y Notas de Crédito Registradas correctamente</h1>';
                                            echo 'Mensaje Factura: ' . $facturaRegistrada['message'] . '<br>';
                                            echo 'Mensaje NC: ' . $resultadoNC['message'] . '<br>';
                                            echo 'Debug: ' . $facturaRegistrada['debug'] . '<br>';
                                        } else {
                                            echo json_encode([
                                                'success' => true,
                                                'message' => $facturaRegistrada['message'] . '<br>' . $resultadoNC['message'],
                                                'debug' => $facturaRegistrada['debug']
                                            ]);
                                        }
                                    } else {
                                        // No hay NC o no se obtuvo idCompra, solo retornar éxito de factura
                                        if ($this->debug == 1) {
                                            echo '<br><h1>Factura Registrada correctamente</h1>Mensaje:' . $facturaRegistrada['message'] . '<br>Debug:' . $facturaRegistrada['debug'] . '<br>';
                                        } else {
                                            echo json_encode([
                                                'success' => true,
                                                'message' => $facturaRegistrada['message'],
                                                'debug' => $facturaRegistrada['debug']
                                            ]);
                                        }
                                    }
                                } else {
                                    echo json_encode([
                                        'success' => false,
                                        'message' => 'Problemas al Registrar la Factura: ' . $facturaRegistrada['message'],
                                        'debug' => $facturaRegistrada['debug']
                                    ]);
                                }
                            } else {
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'Problemas al Validar el XML: ' . $facturaProcesada['message']
                                ]);
                            }
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'El XML de la Factura tiene problemas: ' . $factXML['message']
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'El PDF de la Factura tiene problemas: ' . $factPDF['message']
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Una o mas HES no son validas: ' . $validHES['message']
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No pudimos verificar si debe Anticipo para exigir la Nota de Credito: ' . $verificaDebeAnticipo['message']
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'La Orden de Compra no es Valida: ' . $validOrdenCompra['message']
            ]);
        }
    }

    /**
     * Procesa las Notas de Crédito que vienen junto con una factura recién registrada
     * 
     * @param array $arrayNotasCredito Array con los datos de las NC desde el formulario
     * @param int $idCompra ID de la factura recién registrada
     * @param string $noProveedor Número de proveedor
     * @param string $ordenCompra Orden de compra
     * @param string $isAdmin Indica si es administrador
     * @param array $facturaRegistrada Datos de la factura registrada (para rollback si es necesario)
     * @return array Resultado del procesamiento
     */
    private function procesarNotasCreditoConFactura($arrayNotasCredito, $idCompra, $noProveedor, $ordenCompra, $isAdmin, $facturaRegistrada)
    {
        $Ctrl_Documentos = new DocumentosController();
        $Ctrl_CFDIs = new CfdisController();
        $Ctrl_ProcesaNotasCredito = new FacturasNacionalesController();

        $resultados = [];
        $notasParaProcesar = [];

        // 1. Reestructurar datos de las NC para procesamiento
        foreach ($arrayNotasCredito as $idPlantilla => $nota) {
            // Verificar que tenga la estructura correcta con documentos
            if (!isset($nota['documentos']) || empty($nota['documentos']['PDF']) || empty($nota['documentos']['XML'])) {
                continue;
            }

            // Si hay múltiples políticas seleccionadas, las unimos con coma
            // identNotasCred ya viene como string separado por comas desde CargaFacturasGlobalController
            $identNotasCred = $nota['identNotasCred'] ?? '';
            $notasSeleccionadas = !empty($identNotasCred) ? explode(',', $identNotasCred) : [];
            
            $idNotaCredito = count($notasSeleccionadas) > 1 
                ? implode(',', array_map('intval', $notasSeleccionadas))
                : (!empty($notasSeleccionadas) ? (int)$notasSeleccionadas[0] : '');

            // Los archivos ya vienen estructurados desde CargaFacturasGlobalController
            // Tomar el primer elemento de cada array (PDF y XML)
            $pdfData = $nota['documentos']['PDF'][0] ?? [];
            $xmlData = $nota['documentos']['XML'][0] ?? [];

            $notasParaProcesar[] = [
                'idPlantilla' => $idPlantilla,
                'idNotaCredito' => $idNotaCredito,
                'pdf' => [
                    'name'     => $pdfData['name'] ?? '',
                    'type'     => 'application/pdf', // Tipo inferido
                    'tmp_name' => $pdfData['tmp_name'] ?? '',
                    'error'    => 0,
                    'size'     => isset($pdfData['tmp_name']) && file_exists($pdfData['tmp_name']) ? filesize($pdfData['tmp_name']) : 0
                ],
                'xml' => [
                    'name'     => $xmlData['name'] ?? '',
                    'type'     => 'text/xml', // Tipo inferido
                    'tmp_name' => $xmlData['tmp_name'] ?? '',
                    'error'    => 0,
                    'size'     => isset($xmlData['tmp_name']) && file_exists($xmlData['tmp_name']) ? filesize($xmlData['tmp_name']) : 0
                ]
            ];
        }

        if (empty($notasParaProcesar)) {
            return ['success' => false, 'message' => 'No se encontraron notas de crédito con archivos para procesar.'];
        }

        // 2. Procesar cada NC
        foreach ($notasParaProcesar as $nota) {
            if ($this->debug == 1) {
                echo "<br>--- Procesando Nota de Crédito de Plantilla #{$nota['idPlantilla']} ---<br>";
            }

            // 2.1.- Verificar PDF y XML
            $pdfVerificado = $Ctrl_Documentos->verificadorDeDocumentoARecibir($nota['pdf'], 'pdf');
            if (!$pdfVerificado['success']) {
                $resultados[] = ['success' => false, 'message' => "Error en PDF de plantilla #{$nota['idPlantilla']}: " . $pdfVerificado['message']];
                break;
            }

            $xmlVerificado = $Ctrl_Documentos->verificadorDeDocumentoARecibir($nota['xml'], 'xml');
            if (!$xmlVerificado['success']) {
                $resultados[] = ['success' => false, 'message' => "Error en XML de plantilla #{$nota['idPlantilla']}: " . $xmlVerificado['message']];
                break;
            }

            // 2.2.- Leer el XML
            $dataNotaCredXML = $Ctrl_CFDIs->leerCfdiXML($xmlVerificado['data']['tmp_name'], 'Egreso');
            if (!$dataNotaCredXML['success']) {
                $resultados[] = ['success' => false, 'message' => "Error al leer XML de plantilla #{$nota['idPlantilla']}: " . $dataNotaCredXML['message']];
                break;
            }

            // 2.3.- Validar la NC
            $notaValidada = $Ctrl_ProcesaNotasCredito->verificaNuevaNotaCredito(
                $dataNotaCredXML,
                $noProveedor,
                $idCompra,
                $nota['idNotaCredito'],
                $isAdmin
            );

            if (!$notaValidada['success']) {
                $resultados[] = ['success' => false, 'message' => "Error de validación en NC de plantilla #{$nota['idPlantilla']}: " . $notaValidada['message']];
                break;
            }

            // 2.4.- Registrar la NC
            $datosParaRegistrar = $notaValidada['data'];
            $datosParaRegistrar['ruta_temporal_pdf'] = $pdfVerificado['data']['tmp_name'];
            $datosParaRegistrar['ruta_temporal_xml'] = $xmlVerificado['data']['tmp_name'];
            $datosParaRegistrar['idCompra'] = $idCompra;

            $notaRegistrada = $Ctrl_ProcesaNotasCredito->registraNuevaNotaCredito($datosParaRegistrar);

            if (!$notaRegistrada['success']) {
                $resultados[] = ['success' => false, 'message' => "Error al registrar NC de plantilla #{$nota['idPlantilla']}: " . $notaRegistrada['message']];
                break;
            }

            $resultados[] = ['success' => true, 'message' => "Nota de Crédito #{$nota['idPlantilla']} registrada con éxito."];
        }

        // 3. Evaluar resultados
        $todosExitosos = true;
        $mensajes = [];
        foreach ($resultados as $res) {
            $mensajes[] = $res['message'];
            if (!$res['success']) {
                $todosExitosos = false;
            }
        }

        return [
            'success' => $todosExitosos,
            'message' => implode('<br>', $mensajes)
        ];
    }

    /**
     * Hace rollback de una factura registrada eliminándola de la BD y sus archivos
     * 
     * @param int $idCompra ID de la compra a eliminar
     * @param array $facturaRegistrada Datos de la factura registrada
     * @return void
     */
    private function rollbackFacturaRegistrada($idCompra, $facturaRegistrada)
    {
        if ($this->debug == 1) {
            echo "<br>--- Iniciando rollback de factura con idCompra: $idCompra ---<br>";
        }

        try {
            // Iniciar transacción para el rollback
            BD_Connect::beginTransaction();

            // Eliminar registros relacionados en orden inverso a como se crearon
            // 1. Eliminar impuestos
            $db = new BD_Connect();
            $sql = "DELETE FROM cfdi_facturasImpuestos WHERE idCompra = :idCompra";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCompra' => $idCompra]);

            // 2. Eliminar factura
            $sql = "DELETE FROM cfdi_facturas WHERE idCompra = :idCompra";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCompra' => $idCompra]);

            // 3. Eliminar compra
            $sql = "DELETE FROM compras WHERE id = :idCompra";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCompra' => $idCompra]);

            BD_Connect::commit();

            // Eliminar archivos físicos - obtener URLs desde la BD antes de eliminar
            $Ctrl_Documentos = new DocumentosController();
            $db = new BD_Connect();
            $sql = "SELECT urlPDF, urlXML FROM cfdi_facturas WHERE idCompra = :idCompra LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCompra' => $idCompra]);
            $facturaData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($facturaData) {
                if (!empty($facturaData['urlPDF'])) {
                    $Ctrl_Documentos->eliminaDocumento($facturaData['urlPDF'], 'FACT');
                }
                if (!empty($facturaData['urlXML'])) {
                    $Ctrl_Documentos->eliminaDocumento($facturaData['urlXML'], 'FACT');
                }
            }

            if ($this->debug == 1) {
                echo "<br>--- Rollback completado exitosamente ---<br>";
            }
        } catch (\Exception $e) {
            BD_Connect::rollBack();
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Globals/Controllers/SubirFacturaController.php -> Error en rollback de factura: " . $e->getMessage(), 3, LOG_FILE_BD);
            if ($this->debug == 1) {
                echo "<br>--- Error en rollback: " . $e->getMessage() . " ---<br>";
            }
        }
    }
}
