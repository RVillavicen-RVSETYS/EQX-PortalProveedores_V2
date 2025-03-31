<?php

namespace App\Controllers\ProveedorNacional;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Configuraciones\CierrePortal_Mdl;
use App\Models\Notificaciones\NotificaProveedores_Mdl;
use App\Models\Compras\Compras_Mdl;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Models\DatosCompra\Configuraciones_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;

use App\Globals\Controllers\DocumentosController;
use App\Globals\Controllers\FacturasNacionalesController;
use App\Globals\Controllers\CfdisController;

class InicioController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\ProveedorNacional\InicioController.php.</h2>";
        }
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        $this->checkSession();
    }

    public function index()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $MDL_menuModel = new Menu_Mdl();
        $resultIdArea = $MDL_menuModel->obtenerIdAreaPorLink($areaLink);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $MDL_menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $MDL_menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        $MDL_cierrePortal = new CierrePortal_Mdl();
        $bloqueoCargaFactura = $MDL_cierrePortal->verificaCierreDePortal($_SESSION['EQXnoProveedor']);

        $MDL_notificaProveedor = new NotificaProveedores_Mdl();
        $notificaciones = $MDL_notificaProveedor->NotificacionesProveedor($_SESSION['EQXpais']);

        $MDL_proveedores = new Proveedores_Mdl();
        $datosProveedor = $MDL_proveedores->obtenerDatosProveedor($_SESSION['EQXnoProveedor']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                // Enviar datos a la Vista
                $data['menuData'] =  $menuData;
                $data['areaData'] =  $areaData;
                $data['areaLink'] =  $areaLink;
                $data['bloqueoCargaFactura'] =  $bloqueoCargaFactura;
                $data['notificaciones'] =  $notificaciones;
                $data['datosProveedor'] =  $datosProveedor['data'];

                // Cargar la vista correspondiente
                $this->view('ProveedorNacional/Inicio/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }
    public function verDocumento()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        //Obtener Parametros
        $params = func_get_args();

        if ($this->debug == 1) {
            echo "Parámetros recibidos:<br>";
            echo "<pre>";
            print_r($params);
            echo "</pre>";
        }

        $tipoDocumeto = $params[0];
        $rutaDocumento = $params[1];

        $Ctrl_Documentos = new DocumentosController();
        return $Ctrl_Documentos->mostrarDocumento($rutaDocumento, $tipoDocumeto);
    }

    public function datosIniciales()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        $noProveedor = $_SESSION['EQXnoProveedor'];
        $MDL_proveedores = new Proveedores_Mdl();
        $complementosPendientes = $MDL_proveedores->complementosPagoPendientesPorProveedor($noProveedor);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de Complemento de Pago Pendientes: ' . PHP_EOL;
            var_dump($complementosPendientes);
        }

        if ($complementosPendientes['success']) {
            $Message = 'El proveedor tiene complementos Pendientes.';
            echo json_encode([
                'success' => true,
                'cantComplementos' => $complementosPendientes['cantData'],
                'message' => $Message
            ]);
        } else {
            $errorMessage = $complementosPendientes['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function tablaUltimas50Facturas()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        $noProveedor = $_SESSION['EQXnoProveedor'];
        $MDL_compras = new Compras_Mdl();
        $filtros = [
            'idProveedor' => $noProveedor
        ];
        $listaCompras = $MDL_compras->listaComprasFacturadas($filtros, 50, 'DESC');
        if ($this->debug == 1) {
            echo '<br><br>Resultado de listaComprasFacturadas: ' . PHP_EOL;
            var_dump($listaCompras);
        }

        if ($listaCompras['success']) {
            $data['listaCompras'] =  $listaCompras['data'];
        } else {
            echo '
            <div class="alert alert-warning alert-rounded"> 
                <i class="ti-user"></i> '.$listaCompras['message'].'.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>';
        }

        // Cargar la vista correspondiente
        $this->view('ProveedorNacional/Inicio/listaCompras', $data);
    }

    public function detalladoDeCompra()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $noProveedor = $_SESSION['EQXnoProveedor'];
        $acuse = (empty($_POST['acuse'])) ? '' : $_POST['acuse'];

        $MDL_compras = new Compras_Mdl();
        $dataCompra = $MDL_compras->dataCompraPorAcuse($noProveedor, $acuse);

        $data['noProveedor'] = $noProveedor;
        $data['acuse'] =  $acuse;
        $data['dataCompra'] =  $dataCompra;

        if ($this->debug == 1) {
            echo 'Variables enviadas:' . PHP_EOL;
            var_dump($data);
            echo '<br><br>';
        }

        // Cargar la vista correspondiente
        $this->view('ProveedorNacional/VistasCompartidas/detalladoDeCompra', $data);
    }

    public function validaOrdenCompra()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de ordenCompra: $ordenCompra<br>";
            echo "<br>Contenido de noProveedor: $noProveedor<br>";
        }

        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);
        $MDL_anticipos = new Anticipos_Mdl();
        $verificaDebeAnticipo = $MDL_anticipos->verificaAnticipoDeOrdenCompra($ordenCompra);

        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];

            // Verifica si debe Anticipos la OC
            if ($verificaDebeAnticipo['success']) {
                if ($verificaDebeAnticipo['cantAnticipos'] > 0) {
                    $solicitaNotaCredito = '
                        <div class="form-group">
                            <label>Nota de Credito</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-file-pdf"></i> PDF</span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="notaCredPDF" name="notaCredPDF" required>
                                    <label class="custom-file-label" for="notaCredPDF">Elegir PDF de Nota de Credito..</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-file-code"></i> XML</span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="notaCredXML" name="notaCredXML" required>
                                    <label class="custom-file-label" for="notaCredXML">Elegir XML de Nota de Credito..</label>
                                </div>
                            </div>
                        </div>';
                    echo json_encode([
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => true,
                        'solicitaNotaCredito' => $solicitaNotaCredito,
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => false
                    ]);
                }
            } else {
                $errorMessage = $verificaDebeAnticipo['message'];
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'anticipo' => false
                ]);
            }
        } else {
            $errorMessage = $validOrdenCompra['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function validaHojaEntrada()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $hojaEntrada = $_POST['listaHES'] ?? '';

        //$this->debug = 1;

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de ordenCompra: $ordenCompra<br>";
            echo "<br>Contenido de hojaEntrada: $hojaEntrada<br>";
        }

        if (empty($ordenCompra) || empty($hojaEntrada)) {
            if (empty($ordenCompra)) {
                $Message = 'Registra primero una Orden de Compra.';
                echo json_encode([
                    'success' => false,
                    'message' => $Message
                ]);
            } else {
                $Message = 'No se recibio una Hoja de Entrada.';
                echo json_encode([
                    'success' => false,
                    'message' => $Message
                ]);
            }
        } else {
            $MDL_hojaEntrada = new HojaEntrada_Mdl();
            $validHES = $MDL_hojaEntrada->verificaHojaEntrada($ordenCompra, $hojaEntrada);

            if ($validHES['success']) {
                $Message = $validHES['cantHES'] . ' Hes validas.';
                echo json_encode([
                    'success' => true,
                    'message' => $Message
                ]);
            } else {
                $Message = $validHES['message'];
                echo json_encode([
                    'success' => false,
                    'message' => $Message
                ]);
            }
        }
    }

    public function validaAnticipo()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $anticipo = $_POST['anticipo'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de Anticipo: $anticipo<br>";
            echo "<br>Contenido de noProveedor: $noProveedor<br>";
        }

        if (empty($anticipo)) {
            $Message = 'Ingresa un Codigo de Anticipo.';
            echo json_encode([
                'success' => false,
                'message' => $Message
            ]);
            exit(0);
        }

        $MDL_anticipos = new Anticipos_Mdl();
        $verificaAnticipo = $MDL_anticipos->verificaAnticipo($anticipo, $noProveedor);

        if ($verificaAnticipo['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'OK',
                'anticipo' => false
            ]);
        } else {
            $errorMessage = $verificaAnticipo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function registraNuevaFactura()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        if ($this->debug == 1) {
            echo '<br>----SESSION<br>';
            print_r($_SESSION);
            echo '<br>----POST<br>';
            print_r($_POST);
            echo '<br>----Files<br>';
            print_r($_FILES);
        }

        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $listaHES = $_POST['listaHES'] ?? '';
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
            $verificaDebeAnticipo = $MDL_anticipos->verificaAnticipoDeOrdenCompra($ordenCompra);
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

                        //4.1.1- Revisamos el PDF la Nota de Credito
                        $notaCredPDF = $Ctrl_Documentos->verificadorDeDocumentoARecibir($_FILES['notaCredPDF'], 'pdf');
                        if ($this->debug == 1) {
                            echo '<br><br>Resultado de verificadorDeDocumentoARecibir NotaCreditoPDF: ' . PHP_EOL;
                            var_dump($notaCredPDF);
                        }
                        if ($notaCredPDF['success']) {
                            $doctos['NotaCreditoPDF'] = $notaCredPDF['data'];

                            //4.1.2- Revisamos el XML la Nota de Credito
                            $notaCredXML = $Ctrl_Documentos->verificadorDeDocumentoARecibir($_FILES['notaCredXML'], 'xml');
                            if ($this->debug == 1) {
                                echo '<br><br>Resultado de verificadorDeDocumentoARecibir NotaCreditoXML: ' . PHP_EOL;
                                var_dump($notaCredXML);
                            }
                            if ($notaCredXML['success']) {
                                $doctos['NotaCreditoXML'] = $notaCredXML['data'];
                            }
                        } else {
                            echo 'Horror: El PDF de la Nota de Credito tiene problemas: ' . $notaCredPDF['message'];
                        }

                        echo 'Deberiamos comenzar a Exigir recibir una nota de credito.';
                        echo 'Validar la relación de la factura con la factura del Anticipo.';
                        echo 'Validar los Documentos y cargarlos a la variable $Docto.';
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
                            $facturaProcesada = $Ctrl_ProcesaFacturas->verificaNuevaFacturaIngresos($reqNotaCredito, $ordenCompra, $validHES['hesOK'], $doctos);
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
