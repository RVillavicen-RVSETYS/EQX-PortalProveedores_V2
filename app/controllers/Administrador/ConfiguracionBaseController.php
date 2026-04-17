<?php // Para inicializar código php

namespace App\Controllers\Administrador; // Nombre del espacio de trabajo o directorio

use Core\Controller; // Importa la clase Controller del espacio de nombres Core
use App\Models\Menu_Mdl; // Importa la clase Menu_Mdl del espacio de nombres App\Models\Administrador
use App\Models\Sat\Sat_Mdl; // Importa la clase Sat_Mdl del espacio de nombres App\Models\Sat


class ConfiguracionBaseController extends Controller{ // Declaración de clase con extensión de Controller

    protected $debug = 0;

    public function __construct(){
        
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\ConfiguracionBaseController.php.</h2>";
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

        $satModel = new Sat_Mdl();
        $filtros = ['estatus' => 1];
        $resultTiposMoneda = $satModel->dataTiposMoneda($filtros);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\AlertasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['tiposMoneda'] = $resultTiposMoneda;

                // Cargar la vista correspondiente
                $this->view('Administrador/ConfiguracionBase/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ConfiguracionBaseController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ConfiguracionBaseController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }

        // Contenido de Configuración Base
    }
}