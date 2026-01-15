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
use App\Globals\Controllers\VerificaFoliosDocumentosController;
use App\Globals\Controllers\CargaFacturasGlobalController;
use App\Globals\Controllers\ValidaOcHes;

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
        $noProveedor = $_POST['noProveedor'] ?? '';
        $globalController = new CargaFacturasGlobalController();
        $globalController->registraNuevaFactura($_POST, $_FILES, $noProveedor, true);
    }

    public function verificaOrdenCompraFactura()
    {
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_POST['noProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->verificaOrdenCompraFactura($ordenCompra, $noProveedor);
    }

    public function verificaOrdenCompraNotaCredito()
    {
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_POST['noProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->verificaOrdenCompraNotaCredito($ordenCompra, $noProveedor);
    }

    public function validaHojaEntrada()
    {
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $hojaEntrada = $_POST['listaHES'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->validaHojaEntrada($ordenCompra, $hojaEntrada);
    }

    public function validaAnticipo()
    {
        $anticipo = $_POST['anticipo'] ?? '';
        $noProveedor = $_POST['noProveedor'] ?? ''; // Asumimos que el noProveedor vendrá en el POST para consistencia
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->validaAnticipo($anticipo, $noProveedor);
    }

    public function validaCodigoAnticipo()
    {
        $anticipo = $_POST['anticipo'] ?? '';
        $noProveedor = $_POST['noProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->validaAnticipo($anticipo, $noProveedor);
    }

    public function cargaFormNotaCredito()
    {
        $globalController = new CargaFacturasGlobalController();
        $globalController->cargaFormNotaCredito();
    }

    public function registraNuevaNotaCredito()
    {
        // El 'true' al final indica que la llamada proviene de un administrador.
        $globalController = new CargaFacturasGlobalController();
        $globalController->registraNuevaNotaCredito($_POST, $_FILES, true);
    }

    public function obtenerFacturasPorOC()
    {
        $globalController = new ValidaOcHes();
        $globalController->obtenerFacturasPorOC();
    }

    public function VerificaSiDebeComplementosPago()
    {
        $noProveedor = $_POST['noProveedor'] ?? '';
        $globalController = new VerificaFoliosDocumentosController();
        $globalController->VerificaComplementosPendientesPorProveedor($noProveedor);
    }

    public function registraNuevoComplementoPago()
    {
        // El 'true' al final indica que la llamada proviene de un administrador.
        $globalController = new CargaFacturasGlobalController();
        $globalController->registraNuevoComplementoPago($_POST, $_FILES, true);
    }
}
