<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\Compras\Compras_Mdl;
use App\Globals\Controllers\DocumentosController;
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

        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $filtros = [];
        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechasRecepcion'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        }else {
            $filtros['entreFechasRecepcion'] = date('Y-m-1') . ',' . date('Y-m-t');
        }

        if (!empty($_POST['nacionalidad'])) {
            if ($_POST['nacionalidad'] == 'MX') {
                $filtros['nacional'] = '1';
            } else {
                $filtros['nacional'] = '0';
            }
        }

        if (!empty($_POST['estatusFactura'])) {
            $filtros['estatusFactura'] = $_POST['estatusFactura'];
        }

        $MDL_compras = new Compras_Mdl();

        $listaCompras = $MDL_compras->listaComprasFacturadas($filtros, 0, 'DESC');
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
        $this->view('Administrador/HistorialFacturas/facturasRecibidas', $data);
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

    public function detalladoDeCompra()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $noProveedor = (empty($_POST['idProveedor'])) ? '' : $_POST['idProveedor'];
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
        $this->view('Administrador/HistorialFacturas/detalladoDeCompra', $data);
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
}
