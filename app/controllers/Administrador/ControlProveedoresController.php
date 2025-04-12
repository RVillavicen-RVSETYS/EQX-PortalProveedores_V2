<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;

class ControlProveedoresController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\ControlProveedoresController.php.</h2>";
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

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/ControlProveedores/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function infoProveedor()
    {

        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['proveedor'] ?? '';

        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['idProveedor'] = $idProveedor;

                // Cargar la vista correspondiente
                $this->view('Administrador/ControlProveedores/infoProveedor', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function datosGenerales()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';

        if ($this->debug == 1) {
            echo '<br>Valores para la carga:';
            echo '<br> * idProveedor:' . $idProveedor;
        } 

        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->obtenerDatosProveedor($idProveedor);
        if ($this->debug == 1) {
            echo '<br><br>Datos de Proveedores: ' . PHP_EOL;
            var_dump($resultProveedores);
        }

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['datosProveedor'] = $resultProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ControlProveedores/datosGenerales', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function recepcionesSinFactura()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';

        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->obtenerRecepcionesSinFactura($idProveedor);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['datosRecepcionesSinFactura'] = $resultProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ControlProveedores/recepcionesSinFactura', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function sinFechaPago()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';

        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        //$proveedoresModel = new Proveedores_Mdl();
        //$resultProveedores = $proveedoresModel->obtenerFacturasSinFechaPago($idProveedor);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                //$data['facturasSinFechaPago'] = $resultProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ControlProveedores/facSinFechaPago', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function historico()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';

        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['idProveedor'] = $idProveedor;

                // Cargar la vista correspondiente
                $this->view('Administrador/ControlProveedores/historialFacturas', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ControlProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function actualizarRFC()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $nuevoRFC = $_POST['nuevoRFC'] ?? '';
        //echo 'Id Del Proveedor: ' . $idProveedor . " Nuevo RFC: " . $nuevoRFC;
        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de idProveedor: $idProveedor <br>";
            echo "<br>Contenido de nuevoRFC: $nuevoRFC <br>";
        }

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->actualizaRFC($idProveedor, $nuevoRFC);

        if ($resultProveedores['success']) {
            $Message = $resultProveedores['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultProveedores['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function actualizarCorreo()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $nuevoCorreo = $_POST['nuevoCorreo'] ?? '';
        //echo 'Id Del Proveedor: ' . $idProveedor . " Nuevo Correo: " . $nuevoCorreo;
        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de idProveedor: $idProveedor <br>";
            echo "<br>Contenido de nuevoCorreo: $nuevoCorreo <br>";
        }

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->actualizaCorreo($idProveedor, $nuevoCorreo);

        if ($resultProveedores['success']) {
            $Message = $resultProveedores['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultProveedores['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function actualizarProveedores()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        //echo 'Id Del Proveedor: ' . $idProveedor . " Nuevo Correo: " . $nuevoCorreo;
        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
        }

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->actualizaProveedoresTemp();

        if ($resultProveedores['success']) {
            $resultProveedores = $proveedoresModel->actualizaProveedores();

            if ($resultProveedores['success']) {
                $Message = $resultProveedores['data'];
                echo json_encode([
                    'success' => true,
                    'message' => $Message
                ]);
            } else {
                $errorMessage = $resultProveedores['message'];
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }
        }
    }

    public function actualizarPassword()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $nuevaPass = $_POST['nuevaPass'] ?? '';

        $passEncript = password_hash($nuevaPass, PASSWORD_DEFAULT);

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de idProveedor: $idProveedor <br>";
            echo "<br>Contenido de nuevoCorreo: $nuevaPass <br>";
        }

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->actualizaPassword($idProveedor, $passEncript);

        if ($resultProveedores['success']) {
            $Message = $resultProveedores['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultProveedores['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function logout()
    {
        echo 'Shu bye...';
    }
}
