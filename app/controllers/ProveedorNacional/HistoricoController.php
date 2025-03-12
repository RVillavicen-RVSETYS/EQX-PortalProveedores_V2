<?php

namespace App\Controllers\ProveedorNacional;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;

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
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                    $this->view('ProveedorNacional/Historico/index', $data);
                } else {
                    $timestamp = date("Y-m-d H:i:s");
                    error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al obtener los datos iniciales: " . PHP_EOL, 3, LOG_FILE);
                    echo 'No pudimos traer los datos iniciales:' . $datosIniciales['message'];
                    exit(0);
                }
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
            $Message = 'El proveedor tiene complementos Pendientes.';
            $response = [
                'success' => true,
                'cantComplementos' => $complementosPendientes['cantData'],
                'oldComplementos' => $complementosPendientes['oldData'],
                'message' => $Message
            ];
        } else {
            $errorMessage = $complementosPendientes['message'];
            $response = [
                'message' => $errorMessage
            ];
        }

        return $response;
    }
}
