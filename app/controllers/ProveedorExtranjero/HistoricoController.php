<?php

namespace App\Controllers\ProveedorExtranjero;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\Compras\Compras_Mdl;
use App\Models\Configuraciones\ConfiguracionGral_Mdl;
use DateTime;

class HistoricoController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\InicioController.php.</h2>";
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

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorExtranjero\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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

                $datosIniciales = $this->datosIniciales();
                if ($datosIniciales['success']) {
                    $data['datosIniciales'] = $datosIniciales;

                    // Cargar la vista correspondiente
                    $this->view('ProveedorExtranjero/Historico/index', $data);
                } else {
                    $timestamp = date("Y-m-d H:i:s");
                    error_log("[$timestamp] app\controllers\ProveedorExtranjero\InicioController ->Error al obtener los datos iniciales: " . PHP_EOL, 3, LOG_FILE);
                    echo 'No pudimos traer los datos iniciales:' . $datosIniciales['message'];
                    exit(0);
                }
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\ProveedorExtranjero\InicioController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorExtranjero\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    private function datosIniciales()
    {
        $response = [
            'success' => false,
            'message' => 'No se pudieron obtener los datos iniciales.'
        ];

        $noProveedor = $_SESSION['EQXnoProveedor'];
        $MDL_proveedores = new Proveedores_Mdl();
        $complementosPendientes = $MDL_proveedores->complementosPagoPendientesPorProveedor($noProveedor);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de Complemento de Pago Pendientes: ' . PHP_EOL;
            var_dump($complementosPendientes);
        }

        if ($complementosPendientes['success']) {    
            $MDL_configuracionGral = new ConfiguracionGral_Mdl();
            $configuracionGral = $MDL_configuracionGral->obtenerConfiguracionGral();

            if ($configuracionGral['success']) {

                $MDL_compras = new Compras_Mdl();
                $comprasPorProveedor = $MDL_compras->cantComprasPorProveedor($noProveedor);
                if ($this->debug == 1) {
                    echo '<br><br>Resultado de Compras por Proveedor: ' . PHP_EOL;
                    var_dump($comprasPorProveedor);
                }

                if ($comprasPorProveedor['success']) {
                    $oldData = (empty($complementosPendientes['oldData'])) ? date('Y-m-d H:i:s') :$complementosPendientes['oldData'];
                    $response = [
                        'success' => true,
                        'cantComplementos' => $complementosPendientes['cantData'],
                        'oldComplementos' => $oldData,
                        'maxComplementosPendientes' => $configuracionGral['data']['maxComplementosPendientes'],
                        'cantCompras' => $comprasPorProveedor['data']['cantCompras']
                    ];
                } else {
                    $errorMessage = $comprasPorProveedor['message'];
                    $response = [
                        'message' => $errorMessage
                    ];
                }

            } else {
                $errorMessage = $configuracionGral['message'];
                $response = [
                    'message' => $errorMessage
                ];
            }
            
        } else {
            $errorMessage = $complementosPendientes['message'];
            $response = [
                'message' => $errorMessage
            ];
        }

        return $response;
    }

    public function detalladoDeCompra()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $noProveedor = $_SESSION['EQXnoProveedor'];
        $acuse = (empty($_POST['acuse'])) ? '' : $_POST['acuse'];

        $MDL_compras = new Compras_Mdl();
        $dataCompra = $MDL_compras->dataCompraPorAcuse($noProveedor, $acuse);
        if ($this->debug == 1) {
            echo 'Resultado de dataCompraPorAcuse: ' . PHP_EOL;
            var_dump($dataCompra);
        }

        $data['noProveedor'] = $noProveedor;
        $data['acuse'] =  $acuse;
        $data['dataCompra'] =  $dataCompra;

        if ($this->debug == 1) {
            echo 'Variables enviadas:' . PHP_EOL;
            var_dump($data);
            echo '<br><br>';
        }

        // Cargar la vista correspondiente
        $this->view('ProveedorExtranjero/VistasCompartidas/detalladoDeCompra', $data);
    }

    public function HistorialFacturasRecibidas()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        $noProveedor = $_SESSION['EQXnoProveedor'];
        if ($this->debug == 1) {
            echo 'Valores por POST: ' . PHP_EOL;
            var_dump($_POST);
        }

        $fechaInicial = (empty($_POST['fechaInicial'])) ? '' : DateTime::createFromFormat('d/m/Y', $_POST['fechaInicial'])->format('Y-m-d') . ' 00:00:00';
        $fechaFinal = (empty($_POST['fechaFinal'])) ? '' : DateTime::createFromFormat('d/m/Y', $_POST['fechaFinal'])->format('Y-m-d') . ' 23:59:59';
        $estatusFactura = (empty($_POST['estatusFactura'])) ? '' : $_POST['estatusFactura'];
        $estatusComplemento = (empty($_POST['estatusComplemento'])) ? '' : $_POST['estatusComplemento'];
        $fechas = $fechaInicial . ',' . $fechaFinal;

        $filtros = [
            'idProveedor' => $noProveedor,
            'estatusFactura' => $estatusFactura ?: null,
            'estatusComplemento' => $estatusComplemento,
            'entreFechas' => $fechas
        ];

        $MDL_compras = new Compras_Mdl();
        $listaCompras = $MDL_compras->listaComprasFacturadas($filtros, 0, 'DESC');

        $data['listaCompras'] =  $listaCompras['data'];

        // Cargar la vista correspondiente
        $this->view('ProveedorExtranjero/Historico/tablaFacturasRecibidas', $data);
    }
}
