<?php
namespace App\Controllers\ProveedorNacional;

use Core\Controller; 
use App\Models\Menu_Mdl;
use App\Models\Configuraciones\CierrePortal_Mdl;
use App\Models\Notificaciones\NotificaProveedores_Mdl;
use App\Models\DatosCompra\OrdenCompra_Mdl;

class InicioController extends Controller {
    protected $debug = 0; 

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\InicioController.php.</h2>";
        }
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        $this->checkSession();
    }

    public function index() {
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
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): ". PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:'.$resultIdArea['message'];
            exit(0);
        }
        
        $menuData = $MDL_menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $MDL_menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        $MDL_cierrePortal = new CierrePortal_Mdl();
        $bloqueoCargaFactura = $MDL_cierrePortal->verificaCierreDePortal($_SESSION['EQXnoProveedor']);

        $MDL_notificaProveedor = new NotificaProveedores_Mdl();
        $notificaciones = $MDL_notificaProveedor->NotificacionesProveedor($_SESSION['EQXpais']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                // Enviar datos a la Vista
                $data['menuData'] =  $menuData;
                $data['areaData'] =  $areaData;
                $data['areaLink'] =  $areaLink;
                $data['bloqueoCargaFactura'] =  $bloqueoCargaFactura;
                $data['notificaciones'] =  $notificaciones;
                
            // Cargar la vista correspondiente
            $this->view('ProveedorNacional/Inicio/index', $data);

        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al listar las Areas: ". PHP_EOL, 3, LOG_FILE);
            echo 'Problemas con las Areas de Acceso:'.$resultIdArea['message'];
            exit(0);
        }

        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): ". PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:'.$resultIdArea['message'];
            exit(0);
        }
        
        
    }

    public function validaOrdenCompra(){
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

        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $validOrdenCompra['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
        

    }
}
