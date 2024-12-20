<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Configuraciones\Alertas_Mdl;

class AlertasController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\AlertasController.php.</h2>";
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

                // Cargar la vista correspondiente
                $this->view('Administrador/Alertas/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\AlertasController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\AlertasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaAlertas()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $alertasModel = new Alertas_Mdl();
        $resultAlertas = $alertasModel->obtenerListaAlertas();

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
                $data['listaAlertas'] =  $resultAlertas;

                // Cargar la vista correspondiente
                $this->view('Administrador/Alertas/listaAlertas', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\AlertasController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\AlertasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function nuevaAlerta()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $tipoMensaje = $_POST['tipoMensaje'] ?? '';
        $tipoProveedor = $_POST['tipoProveedor'] ?? '';
        $tipoPeriodo = $_POST['tipoPeriodo'] ?? '';
        $fechaInicio = $_POST['fechaInicio'] ?? '';
        $fechaFin = $_POST['fechaFin'] ?? '';


        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de titulo: $titulo <br>";
            echo "<br>Contenido de descripcion: $descripcion <br>";
            echo "<br>Contenido de tipoMensaje: $tipoMensaje <br>";
            echo "<br>Contenido de tipoProveedor: $tipoProveedor <br>";
            echo "<br>Contenido de tipoPeriodo: $tipoPeriodo <br>";
            echo "<br>Contenido de fechaInicio: $fechaInicio <br>";
            echo "<br>Contenido de fechaFin: $fechaFin <br>";
        }

        $alertasModel = new Alertas_Mdl();
        $resultAlertas = $alertasModel->nuevasAlertas($titulo, $descripcion, $tipoMensaje, $tipoProveedor, $tipoPeriodo, $fechaInicio, $fechaFin);

        if ($resultAlertas['success']) {
            $Message = $resultAlertas['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultAlertas['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function cambiarEstatus()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idNotificacion = $_POST['idNotificacion'] ?? '';
        $estatus = $_POST['estatus'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdNotificacion: $idNotificacion <br>";
            echo "<br>Contenido de Estatus: $estatus <br>";
        }
        $nuevoEstatus = ($estatus == 1) ? 0 : 1;
        $alertasModel = new Alertas_Mdl();
        $resultAlertas = $alertasModel->cambiaEstatus($idNotificacion, $nuevoEstatus);

        if ($resultAlertas['success']) {
            $Message = $resultAlertas['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultAlertas['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function cargarDatos()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idNotificacion = $_POST['idNotificacion'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdNotificacion: $idNotificacion <br>";
        }

        $alertasModel = new Alertas_Mdl();
        $resultAlertas = $alertasModel->cargaDatos($idNotificacion);

        if ($resultAlertas['success']) {
            $Message = $resultAlertas['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultAlertas['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function editaAlerta()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idNotificacion = $_POST['editIdNotificacion'] ?? '';
        $titulo = $_POST['editTitulo'] ?? '';
        $descripcion = $_POST['editDescripcion'] ?? '';
        $tipoMensaje = $_POST['editTipoMensaje'] ?? '';
        $tipoProveedor = $_POST['editTipoProveedor'] ?? '';
        $tipoPeriodo = $_POST['editTipoPeriodo'] ?? '';
        $fechaInicio = $_POST['editFechaInicio'] ?? '';
        $fechaFin = $_POST['editFechaFin'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de titulo: $titulo <br>";
            echo "<br>Contenido de descripcion: $descripcion <br>";
            echo "<br>Contenido de tipoMensaje: $tipoMensaje <br>";
            echo "<br>Contenido de tipoProveedor: $tipoProveedor <br>";
            echo "<br>Contenido de tipoPeriodo: $tipoPeriodo <br>";
            echo "<br>Contenido de fechaInicio: $fechaInicio <br>";
            echo "<br>Contenido de fechaFin: $fechaFin <br>";
        }

        $alertasModel = new Alertas_Mdl();
        $resultAlertas = $alertasModel->editarAlertas($idNotificacion,$titulo, $descripcion, $tipoMensaje, $tipoProveedor, $tipoPeriodo, $fechaInicio, $fechaFin);

        if ($resultAlertas['success']) {
            $Message = $resultAlertas['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultAlertas['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }
}
