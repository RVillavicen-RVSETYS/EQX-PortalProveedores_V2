<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\DatosCompra\NotasCredito_Mdl;

class NotasCreditoController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\NotasCreditoController.php.</h2>";
        }
        // Verifica la sesión y los permisos de usuario Administrador
        $this->checkSessionAdmin();
    }

    public function index()
    {
        $data = [];

        // Obtener el nombre del namespace para identificar el área (Administrador)
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts);

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $proveedoresModel = new Proveedores_Mdl();
        $resultMonedas = $proveedoresModel->obtenerMonedasProveedores();
        $resultProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\NotasCreditoController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area: ' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                $data['menuData'] = $menuData;
                $data['areaData'] = $areaData;
                $data['areaLink'] = $areaLink;
                $data['listaMonedas'] = $resultMonedas;
                $data['listaProveedores'] = $resultProveedores;

                // Cargar la vista principal
                $this->view('Administrador/NotasCredito/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\NotasCreditoController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso: ' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\NotasCreditoController ->Error al buscar estructura del Menú: " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu: ' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaNotasCredito()
    {
        $data = [];
        $filtros = [];

        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = (int)$_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) && !empty($_POST['fechaFinal'])) {
            $filtros['entreFechas'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        }

        $MDL_notasCredito = new NotasCredito_Mdl();
        $dataNotasCredito = $MDL_notasCredito->obtenerNotasCreditoPendientes($filtros, 0, 'DESC');

        if ($this->debug == 1) {
            echo '<br><br>Resultado de obtenerNotasCreditoPendientes: ' . PHP_EOL;
            var_dump($dataNotasCredito);
        }

        if ($dataNotasCredito['success']) {
            $data['listaNotasCredito'] = $dataNotasCredito['data'];
            $data['cantResult'] = $dataNotasCredito['cantResult'];
        } else {
            $data['listaNotasCredito'] = [];
            $data['cantResult'] = 0;
            $data['errorMessage'] = $dataNotasCredito['message'];
        }

        // Cargar vista parcial de la tabla
        $this->view('Administrador/NotasCredito/tablaNotasCredito', $data);
    }
}
