<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores_Mdl;
use App\Models\Configuraciones\BloqueoProveedores_Mdl;

class BloqueoProveedoresController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\BloqueoProveedoresController.php.</h2>";
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
            error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/BloqueoProveedores/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaProveedor()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->obtenerProveedoresBloqueados();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/BloqueoProveedores/listaProveedores', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function periodoBloqueo()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $bloqueoModel = new BloqueoProveedores_Mdl();
        $resultPeriodo = $bloqueoModel->obtenerPeriodoBloqueo();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['periodo'] = $resultPeriodo;

                // Cargar la vista correspondiente
                $this->view('Administrador/BloqueoProveedores/periodoBloqueo', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\BloqueoProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function editaBloqueo()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['proveedorId'] ?? '';
        $bloque = $_POST['bloque'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
            echo "<br>Contenido de Bloque: $bloque <br>";
        }

        $bloqueoModel = new BloqueoProveedores_Mdl();

        $verificaBloqueo = $bloqueoModel->verificarBloqueo($idProveedor);

        if ($verificaBloqueo['data']['IdProveedor'] != $verificaBloqueo['data']['IdProvBloqueo']) {
            $resultBloqueo = $bloqueoModel->insertarBloqueo($idProveedor, $verificaBloqueo['data']['Proveedor'], 1, $bloque);
        } else {
            $nuevoEstatus = ($verificaBloqueo['data']['EstatusBloqueo'] == 1) ? 0 : 1;
            $resultBloqueo = $bloqueoModel->actualizarBloqueo($verificaBloqueo['data']['IdBloqueo'], $idProveedor, $bloque, $nuevoEstatus);
        }

        if ($resultBloqueo['success']) {
            $Message = $resultBloqueo['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultBloqueo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function registraCierreAnual()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $fechaInicio = $_POST['fechaInicio'] ?? '';
        $fechaFin = $_POST['fechaFin'] ?? '';
        $msjEsp = $_POST['msjEsp'] ?? '';
        $msjIng = $_POST['msjIng'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de FechaInicio: $fechaInicio <br>";
            echo "<br>Contenido de FechaFin: $fechaFin <br>";
            echo "<br>Contenido de MsjEsp: $msjEsp <br>";
            echo "<br>Contenido de MsjIng: $msjIng <br>";
        }

        $bloqueoModel = new BloqueoProveedores_Mdl();

        $resultBloqueo = $bloqueoModel->insertarCierreAnual($fechaInicio, $fechaFin, $msjEsp, $msjIng);


        if ($resultBloqueo['success']) {
            $Message = $resultBloqueo['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultBloqueo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function todosFacturan()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
        }

        $bloqueoModel = new BloqueoProveedores_Mdl();

        $resultBloqueo = $bloqueoModel->actualizarTodosFacturan();

        if ($resultBloqueo['success']) {
            $Message = $resultBloqueo['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultBloqueo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function nadieFactura()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
        }

        $bloqueoModel = new BloqueoProveedores_Mdl();

        $resultBloqueo = $bloqueoModel->actualizarNadieFactura();

        if ($resultBloqueo['success']) {
            $Message = $resultBloqueo['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultBloqueo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function actualizarLista()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
        }

        $bloqueoModel = new BloqueoProveedores_Mdl();

        $resultBloqueo = $bloqueoModel->actualizarListaProv();

        if ($resultBloqueo['success']) {
            $Message = $resultBloqueo['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultBloqueo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }
}
