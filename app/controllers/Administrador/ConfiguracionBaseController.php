<?php // Para inicializar código php

namespace App\Controllers\Administrador; // Nombre del espacio de trabajo o directorio

use Core\Controller; // Importa la clase Controller del espacio de nombres Core
use App\Models\Menu_Mdl; // Importa la clase Menu_Mdl del espacio de nombres App\Models\Administrador
use App\Models\Sat\Sat_Mdl; // Importa la clase Sat_Mdl del espacio de nombres App\Models\Sat
use App\Models\Empresas\Empresas_Mdl; // Importa la clase Empresas_Mdl del espacio de nombres App\Models\Empresas
use App\Models\Configuraciones\ConfiguracionGral_Mdl; // Importa el modelo de configuración


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

        // Para consultar los tipos de moneda al modelo
        // Para consultar las empresas al modelo
        $satModel = new Sat_Mdl();
        $empresaModel = new Empresas_Mdl();

        $filtros = ['estatus' => 1];
        
        $resultTiposMoneda = $satModel->dataTiposMoneda($filtros);
        $resultEmpresas = $empresaModel->listaEmpresas(estatus:1);


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
                $data['empresas'] = $resultEmpresas;

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

    // Procesa el guardado de la configuración de montos vía AJAX.
    public function guardarConfiguracion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $idEmpresa     = (int)($_POST['idEmpresa'] ?? 0);
        $idMoneda      = $_POST['idMoneda'] ?? ''; // Quitar (int) si usas códigos como MXN o USD
        $tipoRegla     = (int)($_POST['tipoRegla'] ?? 0);
        $montoTol      = $_POST['montoTolerancia'] ?? 0;
        $porcentajeTol = $_POST['porcentajeTolerancia'] ?? 0;

        if ($idEmpresa === 0 || empty($idMoneda) || $tipoRegla === 0) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        $mdlConfig = new ConfiguracionGral_Mdl();

        // 1. Lógica de negocio: Caso 1 (Existe -> Desactivar) y Caso 2 (No existe -> Directo)
        $existe = $mdlConfig->verificarReglaExistente($idEmpresa, $idMoneda);
        
        if ($existe['success'] && !empty($existe['data'])) {
            // Caso 1: Si ya existe una regla activa, procedemos a desactivarla(s) 
            // antes de insertar la nueva configuración.
            $mdlConfig->desactivarReglasPorEmpresaMoneda($idEmpresa, $idMoneda);
        }

        // 2. Preparar campos: se asigna el mismo valor a Sup e Inf para simetría.
        $campos = [
            'idEmpresa'     => $idEmpresa,
            'tipoMoneda'    => $idMoneda,
            'tipoRegla'     => $tipoRegla,
            'montoSup'      => ($tipoRegla === 1 || $tipoRegla === 3) ? $montoTol : 0,
            'montoInf'      => ($tipoRegla === 1 || $tipoRegla === 3) ? $montoTol : 0,
            'porcentajeSup' => ($tipoRegla === 2 || $tipoRegla === 3) ? $porcentajeTol : 0,
            'porcentajeInf' => ($tipoRegla === 2 || $tipoRegla === 3) ? $porcentajeTol : 0,
            'estatus'       => '1', // Se registra como activa (string '1' para el modelo)
            'idUserReg'     => $_SESSION['EQXident'] ?? 0
        ];

        // 3. Ejecutar inserción
        $resultado = $mdlConfig->registrarDiferenciaMontos($campos);

        header('Content-Type: application/json');
        echo json_encode($resultado);
    }
}