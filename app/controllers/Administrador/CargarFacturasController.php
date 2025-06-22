<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Globals\Controllers\DocumentosController;
use App\Globals\Controllers\FacturasNacionalesController;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Globals\Controllers\SubirFacturaController;
use App\Models\DatosCompra\NotasCredito_Mdl;

class CargarFacturasController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\CargarFacturasController.php.</h2>";
        }
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        $this->checkSessionAdmin();
    }

    public function index()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\CargarFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                // Enviar datos a la Vista
                $data['menuData'] =  $menuData;
                $data['areaData'] =  $areaData;
                $data['areaLink'] =  $areaLink;
                $data['listaProveedores'] = $resultProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/CargarFacturas/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\CargarFacturasController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\CargarFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
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

        // Variables básicas
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_POST['noProveedor'] ?? '';

        // HES separado por comas
        $hes_raw = $_POST['listaHES'] ?? '';
        // Si viene como texto con saltos de línea, normalizamos
        $hes = implode(',', array_filter(array_map('trim', preg_split('/[\r\n]+/', $hes_raw))));

        // ExcepcionesAdmin
        $excepcionesAdmin = $_POST['excepcionesAdmin'] ?? []; // asumiendo que es array asociativo

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
            'ExcepcionesAdmin' => $excepcionesAdmin,
            'NotasCredito' => $notasCredito,
            'Factura' => [
                'FactPDF' => $facturaPDF,
                'FactXML' => $facturaXML,
            ],
            'isAdmin' => true,
        ];

        $Ctrl_SubirFacturas = new SubirFacturaController();
        $cargaFactura = $Ctrl_SubirFacturas->cargarFacturas($valores);
    }

    public function validaOrdenCompra()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_POST['noProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de ordenCompra: $ordenCompra<br>";
            echo "<br>Contenido de noProveedor: $noProveedor<br>";
        }

        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);
        $MDL_notasCredito = new NotasCredito_Mdl();
        $filtros['folioCompra'] = $ordenCompra;
        $verificaNotaCredito = $MDL_notasCredito->verificaNotaCreditoDeOrdenCompra($filtros);
        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];

            // Verifica si debe Anticipos la OC
            if ($verificaNotaCredito['success']) {
                if ($verificaNotaCredito['cantAnticipos'] > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => true,
                        'NC' => $verificaNotaCredito['data']
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => false
                    ]);
                }
            } else {
                $errorMessage = $verificaNotaCredito['message'];
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
}
