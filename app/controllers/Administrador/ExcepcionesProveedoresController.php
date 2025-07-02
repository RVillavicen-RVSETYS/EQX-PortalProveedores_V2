<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Configuraciones\ExcepcionesProveedores_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;

class ExcepcionesProveedoresController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\ExcepcionesProveedoresController.php.</h2>";
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
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/ExcepcionesProveedores/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaIgnoraDesc()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $tabla = "conf_provIgnoraDescuento";
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->obtenerIgnoraDesc();
        $obetenerProveedores = $excepcionesModel->getProveedores($tabla);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['listaIgnoraDesc'] =  $resultExcepciones;
                $data['listaProveedores'] = $obetenerProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ExcepcionesProveedores/ignorarDescuento', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaExentos()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $tabla = "conf_provExentoAnoFisc";
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->obtenerExentos();
        $obetenerProveedores = $excepcionesModel->getProveedores($tabla);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['exentosAnioFiscal'] =  $resultExcepciones;
                $data['listaProveedores'] = $obetenerProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ExcepcionesProveedores/anioFiscal', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaFechaEmision()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $tabla = "conf_provExentoFechaEmision";
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->obtenerFechaEmision();
        $obetenerProveedores = $excepcionesModel->getProveedores($tabla);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['exentosFechaEmision'] =  $resultExcepciones;
                $data['listaProveedores'] = $obetenerProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ExcepcionesProveedores/fechaEmision', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaCfdiDistinto()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $tabla = "conf_provUsoCfdiDistinto";
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->obtenerUsoCfdi();
        $catUsoCfdi = $excepcionesModel->obtenerCatUsoCfdi();
        $obetenerProveedores = $excepcionesModel->getProveedores($tabla);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['exentosCfdiDistinto'] =  $resultExcepciones;
                $data['catUsoCfdi'] =  $catUsoCfdi;
                $data['listaProveedores'] = $obetenerProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ExcepcionesProveedores/usoCfdi', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaBloqueoDiferencias()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $tabla = "conf_provBloqDiferencias";
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->obtenerBloqueoDiferencias();
        $obetenerProveedores = $excepcionesModel->getProveedores($tabla);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['bloqueoDeDiferencias'] =  $resultExcepciones;
                $data['listaProveedores'] = $obetenerProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/ExcepcionesProveedores/bloqueoDiferencias', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function cfdisPorProveedor()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $tabla = "conf_provCfdisPermitidos";
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->obtenerCfdisPermitidos();
        $cfdisPermitidos = $excepcionesModel->cfdisPermitidosGeneral();

        $proveedoresModel = new Proveedores_Mdl();
        $obetenerProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['cfdisPermitidosProv'] =  $resultExcepciones;
                $data['listaProveedores'] = $obetenerProveedores;
                $data['cfdisPermitidosGeneral'] = $cfdisPermitidos;

                // Cargar la vista correspondiente
                $this->view('Administrador/ExcepcionesProveedores/cfdisProveedor', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function cambiarEstatus()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $estatus = $_POST['estatus'] ?? '';
        $identificador = $_POST['ident'] ?? '';
        $tabla = $_POST['tabla'] ?? '';
        $idProveedor = $_POST['idProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de Estatus: $estatus <br>";
            echo "<br>Contenido de Identificador: $identificador <br>";
            echo "<br>Contenido de Tabla: $tabla <br>";
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
        }

        $nuevoEstatus = ($estatus == 1) ? 0 : 1;
        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->cambiarEstatus($tabla, $identificador, $nuevoEstatus, $idProveedor);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function eliminar()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        $identificador = $_POST['ident'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de Identificador: $identificador <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->eliminarReg($identificador);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function eliminarCfdiPermitido()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        $identificador = $_POST['ident'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de Identificador: $identificador <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->eliminarCfdiPermitido($identificador);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function agregarProveedorIG()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
            echo "<br>Contenido de Motivo: $motivo <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->registraProveedorIG($idProveedor, $motivo);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function agregarProveedorEAF()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
            echo "<br>Contenido de Motivo: $motivo <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->registraProveedorEAF($idProveedor);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function agregarProveedorEFE()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->registraProveedorEFE($idProveedor);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function agregarProveedorUC()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $idUsoCfdi = $_POST['idUsoCfdi'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
            echo "<br>Contenido de idUsoCfdi: $idUsoCfdi <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->registraProveedorUC($idProveedor, $idUsoCfdi);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function agregarProveedorBD()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedor'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
            echo "<br>Contenido de motivo: $motivo <br>";
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->registraProveedorBD($idProveedor, $motivo);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function agregarProveedorBUC()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $idProveedor = $_POST['idProveedorBUC'] ?? '';
        $listaCfdis = $_POST['idUsoCfdiPermitido'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
            echo "<br>Contenido de listaCfdis: <br>";
            var_dump($listaCfdis);
        }

        $excepcionesModel = new ExcepcionesProveedores_Mdl();
        $resultExcepciones = $excepcionesModel->registraCfdisPorProveedor($idProveedor, $listaCfdis);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['data'];
            echo json_encode([
                'success' => true,
                'message' => $Message
            ]);
        } else {
            $errorMessage = $resultExcepciones['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }
}
