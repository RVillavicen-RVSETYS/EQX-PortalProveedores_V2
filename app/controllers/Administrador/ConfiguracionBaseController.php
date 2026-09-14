<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Sat\Sat_Mdl;
use App\Models\Empresas\Empresas_Mdl;
use App\Models\Configuraciones\ConfiguracionGral_Mdl;

class ConfiguracionBaseController extends Controller
{

    protected $debug = 0;

    public function __construct()
    {

        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\ConfiguracionBaseController.php.</h2>";
        }
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

        // Instanciar modelos
        $satModel = new Sat_Mdl();
        $empresaModel = new Empresas_Mdl();
        $reglasModel = new ConfiguracionGral_Mdl();

        // Obtener datos de los modelos
        $filtros = ['estatus' => 1];
        $filtrosReglas = ['estatus' => 1];

        $resultTiposMoneda = $satModel->dataTiposMoneda($filtros);
        $resultEmpresas = $empresaModel->listaEmpresas(estatus: 1);
        $resultReglas = $reglasModel->dataDiferenciaMontos($filtrosReglas);


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
                $data['reglas'] = $resultReglas;


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

    public function guardarConfiguracion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $idEmpresa     = (int)($_POST['idEmpresa'] ?? 0);
        $idMoneda      = $_POST['idMoneda'] ?? '';
        $tipoRegla     = (int)($_POST['tipoRegla'] ?? 0);
        $montoTol      = $_POST['montoTolerancia'] ?? 0;
        $porcentajeTol = $_POST['porcentajeTolerancia'] ?? 0;

        if ($idEmpresa === 0 || empty($idMoneda) || $tipoRegla === 0) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        $mdlConfig = new ConfiguracionGral_Mdl();

        $existe = $mdlConfig->verificarReglaExistente($idEmpresa, $idMoneda);

        if ($existe['success'] && !empty($existe['data'])) {

            $mdlConfig->desactivarReglasPorEmpresaMoneda($idEmpresa, $idMoneda);
        }

        $campos = [
            'idEmpresa'     => $idEmpresa,
            'tipoMoneda'    => $idMoneda,
            'tipoRegla'     => $tipoRegla,
            'montoSup'      => ($tipoRegla === 1 || $tipoRegla === 3) ? $montoTol : 0,
            'montoInf'      => ($tipoRegla === 1 || $tipoRegla === 3) ? $montoTol : 0,
            'porcentajeSup' => ($tipoRegla === 2 || $tipoRegla === 3) ? $porcentajeTol : 0,
            'porcentajeInf' => ($tipoRegla === 2 || $tipoRegla === 3) ? $porcentajeTol : 0,
            'estatus'       => '1',
            'idUserReg'     => $_SESSION['EQXident'] ?? 0
        ];

        $resultado = $mdlConfig->registrarDiferenciaMontos($campos);

        header('Content-Type: application/json');
        echo json_encode($resultado);
    }

    public function guardarConfiguracionGral()
    {

        $diasPago = [];
        if (isset($_POST['diasPago'])) {
            if (is_array($_POST['diasPago'])) {
                $diasPago = array_filter($_POST['diasPago'], function ($value) {
                    return trim($value) !== '';
                });
            } else {
                $diasPago = [trim($_POST['diasPago'])];
            }
        }

        $data = [
            'idEmpresa' => isset($_POST['idEmpresa']) ? intval($_POST['idEmpresa']) : null,
            'maxComplementosPendientes' => isset($_POST['maxComplementosPendientes']) ? intval($_POST['maxComplementosPendientes']) : null,
            'diasPago' => implode(',', $diasPago)
        ];

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>";
        }

        if (!$data['idEmpresa'] || !$data['maxComplementosPendientes'] || $data['diasPago'] === '') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
            exit;
        }

        $configuracionGralModel = new ConfiguracionGral_Mdl();


        // Verificar si ya existe configuración para esta empresa usando el método flexible
        $resultExiste = $configuracionGralModel->listarConfiguracionGral(['idEmpresa' => $data['idEmpresa']], 1);

        if ($resultExiste['success'] && !empty($resultExiste['data'])) {
            // Si existe, actualizar
            $result = $configuracionGralModel->actualizarConfiguracionGral($data);
        } else {
            // Si no existe, insertar
            $result = $configuracionGralModel->registrarConfiguracionGral($data);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function obtenerConfiguracionEmpresa()
    {

        $idEmpresa = isset($_POST['idEmpresa']) ? intval($_POST['idEmpresa']) : null;

        if (!$idEmpresa) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID de empresa no válido.']);
            exit;
        }
        $configuracionGralModel = new ConfiguracionGral_Mdl();
        $result = $configuracionGralModel->listarConfiguracionGral(['idEmpresa' => $idEmpresa], 1);

        // Para mantener compatibilidad, devolver solo el primer registro si existe
        if ($result['success'] && !empty($result['data'])) {
            $response = ['success' => true, 'data' => $result['data'][0]];
        } else {
            $response = ['success' => false, 'message' => 'No se encontró configuración para esta empresa.'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /* =============================================================
       Metodo para GUARDAR el/los correos en la nueva tabla de la BD
       ============================================================= */
    public function guardarConfiguracionCorreo()
    {
        $idEmpresa = $_SESSION['EQXidEmpresa'] ?? 0;
        $correos = $_POST['correoRechazoFactura'] ?? '';

        // Validaciones básicas
        if (!$idEmpresa) {
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión para identificar su empresa.']);
            return;
        }

        if (empty($correos)) {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar al menos un correo.']);
            return;
        }

        $mdl = new ConfiguracionGral_Mdl();
        $datos = [
            'idEmpresa' => $idEmpresa,
            'correoRechazoFactura' => $correos
        ];

        // --- LÓGICA DE VALIDACIÓN EN EL CONTROLADOR ---

        // 1. Verificamos si ya existe el registro usando el método dinámico
        $check = $mdl->dataCorreosNotificacion(['idEmpresa' => $idEmpresa], 1);

        if ($check['success'] && !empty($check['data'])) {
            // Ya existe -> Mandamos a Actualizar
            $datos['idUserModifica'] = $_SESSION['EQXident'] ?? 0;
            $resultado = $mdl->actualizarCorreosNotificacion($datos);
        } else {
            // No existe -> Mandamos a Registrar por primera vez
            $datos['idUserReg'] = $_SESSION['EQXident'] ?? 0;
            $resultado = $mdl->registrarCorreosNotificacion($datos);
        }

        echo json_encode($resultado);
    }

    /* =============================================================
       Metodo para CONSULTAR el/los correos en la nueva tabla de la BD
       ============================================================= */
    public function obtenerConfiguracionCorreo()
    {
        $idEmpresa = $_SESSION['EQXidEmpresa'] ?? 0;
        $mdl = new ConfiguracionGral_Mdl();

        $resultado = $mdl->dataCorreosNotificacion(['idEmpresa' => $idEmpresa], 1);

        // Adaptamos la respuesta para que el JS reciba directamente el string de correos
        $correosStr = '';
        if ($resultado['success'] && !empty($resultado['data'])) {
            $correosStr = $resultado['data'][0]['correoRechazoFactura'];
        }

        echo json_encode(['success' => true, 'data' => $correosStr]);
    }
}
