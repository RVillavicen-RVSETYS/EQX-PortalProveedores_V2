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
use App\Globals\Controllers\VerificaFoliosDocumentosController;
use App\Globals\Controllers\CargaFacturasGlobalController;
use App\Globals\Controllers\ValidaOcHes;

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

    public function verificaOrdenCompraFactura()
    {
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->verificaOrdenCompraFactura($ordenCompra, $noProveedor);
    }


    public function cargaFormNotaCredito()
    {
        $globalController = new CargaFacturasGlobalController();
        $globalController->cargaFormNotaCredito();
    }

    public function validaHojaEntrada()
    {
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $hojaEntrada = $_POST['listaHES'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->validaHojaEntrada($ordenCompra, $hojaEntrada);
    }

    public function verificaOrdenCompraNotaCredito()
    {
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->verificaOrdenCompraNotaCredito($ordenCompra, $noProveedor);
    }

    public function validaAnticipo()
    {
        $anticipo = $_POST['anticipo'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->validaAnticipo($anticipo, $noProveedor);
    }

    /**
     * Alias para la vista: el JS llama a Inicio/validaCodigoAnticipo (misma lógica que validaAnticipo).
     */
    public function validaCodigoAnticipo()
    {
        $this->validaAnticipo();
    }

    public function registraNuevaFactura()
    {
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';
        $globalController = new CargaFacturasGlobalController();
        $globalController->registraNuevaFactura($_POST, $_FILES, $noProveedor, false);
    }

    public function obtenerFacturasPorOC()
    {
        $globalController = new ValidaOcHes();
        $globalController->obtenerFacturasPorOC();
    }

    public function VerificaSiDebeComplementosPago()
    {
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->VerificaComplementosPendientesPorProveedor($noProveedor);
    }

    public function registraNuevaNotaCredito()
    {
        $globalController = new CargaFacturasGlobalController();
        $globalController->registraNuevaNotaCredito($_POST, $_FILES, false);
    }

    public function registraNuevoComplementoPago()
    {
        $globalController = new CargaFacturasGlobalController();
        $globalController->registraNuevoComplementoPago($_POST, $_FILES, false);
    }
}
