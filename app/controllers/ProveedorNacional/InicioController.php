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
use App\Globals\Controllers\SubirFacturaController;
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
                <i class="ti-user"></i> ' . $listaCompras['message'] . '.
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
        $filtros['folioCompra'] = $ordenCompra;
        $verificaDebeAnticipo = $MDL_anticipos->verificaAnticipoDeOrdenCompra($filtros);

        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];

            // Verifica si debe Anticipos la OC
            if ($verificaDebeAnticipo['success']) {
                if ($verificaDebeAnticipo['cantAnticipos'] > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => true,
                        'NC' => $verificaDebeAnticipo['data']
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

    public function cargaFormNotaCredito()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        //$noProveedor = $_SESSION['EQXnoProveedor'];
        //$acuse = (empty($_POST['acuse'])) ? '' : $_POST['acuse'];

        //$MDL_compras = new Compras_Mdl();
        //$dataCompra = $MDL_compras->dataCompraPorAcuse($noProveedor, $acuse);

        //$data['noProveedor'] = $noProveedor;
        //$data['acuse'] =  $acuse;
        //$data['dataCompra'] =  $dataCompra;

        if ($this->debug == 1) {
            echo 'Variables enviadas:' . PHP_EOL;
            var_dump($data);
            echo '<br><br>';
        }

        // Cargar la vista correspondiente
        $this->view('VistasCompartidas/NotasCredito_Form', $data);
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

        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';

        // HES separado por comas
        $hes_raw = $_POST['listaHES'] ?? '';
        // Si viene como texto con saltos de línea, normalizamos
        $hes = implode(',', array_filter(array_map('trim', preg_split('/[\r\n]+/', $hes_raw))));

        
        // NotasCredito
        $notasCreditoPost = $_POST['notaCredito'] ?? [];           // Array multidimensional: notaCredito[id] = array de notas
        $archivosNotas = $_FILES['notaCreditoArchivo'] ?? [];     // Archivos: notaCreditoArchivo[name|tmp_name][id][pdf|xml]

        // Construcción del arreglo NotasCredito
        $notasCredito = [];

        foreach ($notasCreditoPost as $id => $notasArray) {
            // Unir notas en string separado por comas
            $identNotasCred = implode(',', $notasArray);

            // Obtener archivos PDF y XML para este bloque
            $pdfArray = [];
            $xmlArray = [];

            if (isset($archivosNotas['name'][$id]['pdf']) && is_array($archivosNotas['name'][$id]['pdf'])) {
                // Si varios archivos PDF (por ejemplo)
                foreach ($archivosNotas['name'][$id]['pdf'] as $idx => $namePdf) {
                    $pdfArray[] = [
                        'name' => $namePdf,
                        'tmp_name' => $archivosNotas['tmp_name'][$id]['pdf'][$idx] ?? null,
                        // Otros datos como tipo, error, tamaño si quieres
                    ];
                }
            } else {
                // Un solo archivo PDF
                $pdfArray[] = [
                    'name' => $archivosNotas['name'][$id]['pdf'] ?? '',
                    'tmp_name' => $archivosNotas['tmp_name'][$id]['pdf'] ?? null,
                ];
            }

            if (isset($archivosNotas['name'][$id]['xml']) && is_array($archivosNotas['name'][$id]['xml'])) {
                foreach ($archivosNotas['name'][$id]['xml'] as $idx => $nameXml) {
                    $xmlArray[] = [
                        'name' => $nameXml,
                        'tmp_name' => $archivosNotas['tmp_name'][$id]['xml'][$idx] ?? null,
                    ];
                }
            } else {
                $xmlArray[] = [
                    'name' => $archivosNotas['name'][$id]['xml'] ?? '',
                    'tmp_name' => $archivosNotas['tmp_name'][$id]['xml'] ?? null,
                ];
            }

            // Aquí asignas con índice explícito $id (puede ser 0,1,2...)
            $notasCredito[$id] = [
                'identNotasCred' => $identNotasCred,
                'documentos' => [
                    'PDF' => $pdfArray,
                    'XML' => $xmlArray,
                ],
            ];
        }

        // Archivos Factura
        $facturaPDF = [];
        $facturaXML = [];

        if (isset($_FILES['facturaPDF'])) {
            $facturaPDF[] = [
                'name' => $_FILES['facturaPDF']['name'],
                'tmp_name' => $_FILES['facturaPDF']['tmp_name'],
            ];
        }

        if (isset($_FILES['facturaXML'])) {
            $facturaXML[] = [
                'name' => $_FILES['facturaXML']['name'],
                'tmp_name' => $_FILES['facturaXML']['tmp_name'],
            ];
        }

        // Construcción arreglo final
        $valores = [
            'ordenCompra' => $ordenCompra,
            'noProveedor' => $noProveedor,
            'listaHES' => $hes,
            'NotasCredito' => $notasCredito,
            'Factura' => [
                'FactPDF' => $facturaPDF,
                'FactXML' => $facturaXML,
            ],
            'isAdmin' => false,
        ];

        $Ctrl_SubirFacturas = new SubirFacturaController();
        $cargaFactura = $Ctrl_SubirFacturas->cargarFacturas($valores);
    }
}
