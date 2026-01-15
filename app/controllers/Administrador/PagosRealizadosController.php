<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\PagosProveedores\Pagos_Mdl;

class PagosRealizadosController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\PagosRealizadosController.php.</h2>";
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
            error_log("[$timestamp] app\controllers\Administrador\PagosRealizadosController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/PagosRealizados/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\PagosRealizadosController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\PagosRealizadosController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listarPagosRealizados()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $filtros = [];
        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor1'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor2'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechasPago1'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        } else {
            $filtros['entreFechasPago1'] = date('Y-m-1') . ',' . date('Y-m-t');
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechasPago2'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        } else {
            $filtros['entreFechasPago2'] = date('Y-m-1') . ',' . date('Y-m-t');
        }

        $MDL_pagos = new Pagos_Mdl();
        $listaPagos = $MDL_pagos->listarPagosRealizados($filtros, 0, 'ASC');

        if ($this->debug == 1) {
            echo '<br><br>Resultado de listaPagosRealizados: ' . PHP_EOL;
            var_dump($listaPagos);
        }

        if ($listaPagos['success']) {
            if ($listaPagos['cantRes'] > 0) {
                $data['listaPagos'] =  $listaPagos['data'];
            } else {
                echo '
            <div class="alert alert-info alert-rounded"> 
                <i class="ti-user"></i> No Hubo Resultados.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>';
            }
        } else {
            echo '
            <div class="alert alert-warning alert-rounded"> 
                <i class="ti-user"></i> ' . $listaPagos['message'] . '.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>';
        }

        // Cargar la vista correspondiente
        $this->view('Administrador/PagosRealizados/listaPagos', $data);
    }
}
