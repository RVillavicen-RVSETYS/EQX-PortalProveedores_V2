<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Globals\Controllers\DocumentosController;
use App\Globals\Controllers\FacturasNacionalesController;
use App\Models\Proveedores\Proveedores_Mdl;

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
                                    if ($this->debug == 1) {
                                        echo '<br><h1>Factura Registrada correctamente</h1>Mensaje:' . $facturaRegistrada['message'] . '<br>Debug:' . $facturaRegistrada['debug'] . '<br>';
                                    } else {
                                        echo json_encode([
                                            'success' => true,
                                            'message' => $facturaRegistrada['message'],
                                            'debug' => $facturaRegistrada['debug']
                                        ]);
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
}
