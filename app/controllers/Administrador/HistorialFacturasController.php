<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\Facturas\HistorialFacturas_Mdl;

class HistorialFacturasController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\HistorialFacturasController.php.</h2>";
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

        $proveedoresModel = new Proveedores_Mdl;
        $resultProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\HistorialFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/HistorialFacturas/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\HistorialFacturasController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\HistorialFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listarFacturas()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $fechaInicial = $_POST['fechaInicial'] ?? '';
        $fechaFinal = $_POST['fechaFinal'] ?? '';

        if ($fechaInicial == '' and $fechaFinal == '') {
            $fechaInicial = date('Y-m-01');
            $fechaFinal = date('Y-m-t');
        }

        $filtroFecha = "AND DATE_FORMAT(cp.fechaReg, '%Y-%m-%d') BETWEEN '$fechaInicial' AND '$fechaFinal'";
        $filtroProveedor = (empty($_POST['idProveedor'])) ?  '' : "AND cp.idProveedor = " . $_POST['idProveedor'];
        //$filtroEstatusCont = (empty($_POST['estatusCont'])) ? '' : "AND cp.estatus = " . $_POST['estatusCont'];

        /*$estatusComp = $_POST['estatusComp'] ?? '';
        switch ($estatusComp) {
            case '1':
                $filtroEstatusComp = "AND (fac.idCatMetodoPago == 'PPD' AND  (dtpg.cantComp < '1' OR dtpg.insolutos > '0'))";
                break;

            case '2':
                $filtroEstatusComp = "AND (dtpg.cantComp >= '1' AND  dtpg.insolutos < '0.01')";
                break;

            case '3':
                $filtroEstatusComp = "AND (pvd.pais <> 'MX' OR fac.idCatMetodoPago == 'PUE')";
                break;

            default:
                $filtroEstatusComp = '';
                break;
        }*/


        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $historialModel = new HistorialFacturas_Mdl();
        $resultHistorial = $historialModel->obtenerHistorial($filtroFecha, $filtroProveedor);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\HistorialFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['historialFacturas'] =  $resultHistorial;

                // Cargar la vista correspondiente
                $this->view('Administrador/HistorialFacturas/facturasRecibidas', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\HistorialFacturasController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\HistorialFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function buscarPagos()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $fechaInicial = $_POST['fechaInicialBus'] ?? '';
        $fechaFinal = $_POST['fechaFinalBus'] ?? '';

        //echo 'Id Del Proveedor: ' . $idProveedor . " Nuevo Correo: " . $nuevoCorreo;
        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de fechaInicial: $fechaInicial <br>";
            echo "<br>Contenido de fechaFinal: $fechaFinal <br>";
        }

        $historialModel = new HistorialFacturas_Mdl();
        $resultPagos = $historialModel->buscaPagoSilme($fechaInicial, $fechaFinal);

        if ($resultPagos['success']) {

            $insertarPagos = $historialModel->insertarPagos($resultPagos['data']);

            if ($insertarPagos['success']) {
                $Message = $insertarPagos['data'];
                echo json_encode([
                    'success' => true,
                    'message' => $Message
                ]);
            } else {
                $errorMessage = $insertarPagos['message'];
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }


        }
    }
}
