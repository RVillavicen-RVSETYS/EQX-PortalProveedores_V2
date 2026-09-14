<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Excepciones\IgnoraDescuento_Mdl;
use App\Models\Proveedores\Excepciones\ExentoAnoFisc_Mdl;
use App\Models\Proveedores\Excepciones\ExentoFechaEmision_Mdl;
use App\Models\Proveedores\Excepciones\UsoCfdiDistinto_Mdl;
use App\Models\Proveedores\Excepciones\BloqDiferencias_Mdl;
use App\Models\Proveedores\Excepciones\ExcepcionesProveedores_Mdl;
use App\Models\Proveedores\Excepciones\PermitirPueSiempre_Mdl;
use App\Models\Proveedores\Excepciones\PoliticasComerciales_Mdl;
use App\Models\Proveedores\Excepciones\IgnorarFechaPago_Mdl;
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
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $ignoraDescuentoModel = new IgnoraDescuento_Mdl();
        $resultExcepciones = $ignoraDescuentoModel->obtenerIgnoraDesc();
        $obetenerProveedores = $ignoraDescuentoModel->getProveedores();

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
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $exentoAnoFiscModel = new ExentoAnoFisc_Mdl();
        $resultExcepciones = $exentoAnoFiscModel->obtenerExentos();
        $obetenerProveedores = $exentoAnoFiscModel->getProveedores();

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
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $exentoFechaEmisionModel = new ExentoFechaEmision_Mdl();
        $resultExcepciones = $exentoFechaEmisionModel->obtenerFechaEmision();
        $obetenerProveedores = $exentoFechaEmisionModel->getProveedores();

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
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $usoCfdiDistintoModel = new UsoCfdiDistinto_Mdl();
        $resultExcepciones = $usoCfdiDistintoModel->obtenerUsoCfdi();
        $catUsoCfdi = $usoCfdiDistintoModel->obtenerCatUsoCfdi();
        $obetenerProveedores = $usoCfdiDistintoModel->getProveedores();

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
        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $bloqDiferenciasModel = new BloqDiferencias_Mdl();
        $resultExcepciones = $bloqDiferenciasModel->obtenerBloqueoDiferencias();
        $obetenerProveedores = $bloqDiferenciasModel->getProveedores();

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

    public function listaPoliticasComerciales()
    {
        $data = [];
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts);

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $politicasModel = new PoliticasComerciales_Mdl();
        $resultActivos = $politicasModel->obtenerProveedoresConPoliticaActiva();
        $listaDisponibles = $politicasModel->getProveedoresDisponibles();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController -> listaPoliticasComerciales: " . $resultIdArea['message'], 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        if ($menuData['success'] && $areaData['success']) {
            $data['menuData'] = $menuData;
            $data['areaData'] = $areaData;
            $data['areaLink'] = $areaLink;
            $data['proveedoresPoliticaActiva'] = $resultActivos;
            $data['listaProveedores'] = $listaDisponibles;
            $this->view('Administrador/ExcepcionesProveedores/politicasComerciales', $data);
        } else {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController -> listaPoliticasComerciales: error menu o areas", 3, LOG_FILE);
            echo 'Problemas al cargar menú o áreas.';
            exit(0);
        }
    }

    public function listaPermitirPueSiempre()
    {
        $data = [];
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts);

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $permitePueModel = new PermitirPueSiempre_Mdl();
        $resultExcepciones = $permitePueModel->obtenerLista();
        $obtenerProveedores = $permitePueModel->getProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                $data['menuData'] = $menuData;
                $data['areaData'] = $areaData;
                $data['areaLink'] = $areaLink;
                $data['listaPermitirPueSiempre'] = $resultExcepciones;
                $data['listaProveedores'] = $obtenerProveedores;
                $this->view('Administrador/ExcepcionesProveedores/permitirPueSiempre', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaAnulacionValidacionFechaPagoProveedor()
    {
        $data = [];
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts);

        $menuModel = new Menu_Mdl();
        $resultIdArea = $menuModel->obtenerIdAreaPorLink($areaLink);

        $ignorafechaPago = new IgnorarFechaPago_Mdl();
        $resultActivos = $ignorafechaPago->obtenerProveedoresConFechaPagoIgnorada();
        $listaDisponibles = $ignorafechaPago->getProveedoresDisponibles();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController -> listaPoliticasComerciales: " . $resultIdArea['message'], 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        if ($menuData['success'] && $areaData['success']) {
            $data['menuData'] = $menuData;
            $data['areaData'] = $areaData;
            $data['areaLink'] = $areaLink;
            $data['proveedoresFechaPagoIgnorada'] = $resultActivos;
            $data['listaProveedores'] = $listaDisponibles;
            $this->view('Administrador/ExcepcionesProveedores/fechaPagoProveedor', $data);
        } else {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] app\\controllers\\Administrador\\ExcepcionesProveedoresController -> listaAnulacionValidacionFechaPagoProveedor: error menu o areas", 3, LOG_FILE);
            echo 'Problemas al cargar menú o áreas.';
            exit(0);
        }
    }

    public function cfdisPorProveedor()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
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

        // Preparar campos y filtros siguiendo el patrón de consumo
        $campos = [
            'estatus' => $nuevoEstatus
        ];

        // Tabla 6 (Permitir PUE siempre): al deshabilitar (estatus 0) el motivo es obligatorio
        if ($tabla === '6' && $nuevoEstatus == 0) {
            $motivoCancela = trim($_POST['motivoCancela'] ?? '');
            if ($motivoCancela === '') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'El motivo de deshabilitación es obligatorio.'
                ]);
                return;
            }
            $campos['motivoCancela'] = $motivoCancela;
        }

        $filtros = [
            'id' => $identificador
        ];

        // Seleccionar el modelo según la tabla
        switch ($tabla) {
            case '1':
                $model = new IgnoraDescuento_Mdl();
                $resultExcepciones = $model->actualizarIgnoraDescuento($campos, $filtros);
                break;
            case '2':
                $model = new ExentoAnoFisc_Mdl();
                $resultExcepciones = $model->actualizarExentoAnoFisc($campos, $filtros);
                break;
            case '3':
                $model = new ExentoFechaEmision_Mdl();
                $resultExcepciones = $model->actualizarExentoFechaEmision($campos, $filtros);
                break;
            case '4':
                $model = new UsoCfdiDistinto_Mdl();
                $resultExcepciones = $model->actualizarUsoCfdiDistinto($campos, $filtros);
                break;
            case '5':
                $model = new BloqDiferencias_Mdl();
                $resultExcepciones = $model->actualizarBloqDiferencias($campos, $filtros);
                break;
            case '6':
                $model = new PermitirPueSiempre_Mdl();
                $resultExcepciones = $model->actualizarPermitirPueSiempre($campos, $filtros);
                break;
            default:
                $resultExcepciones = ['success' => false, 'message' => 'Tabla no válida.'];
                break;
        }

        header('Content-Type: application/json; charset=utf-8');
        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

        $bloqDiferenciasModel = new BloqDiferencias_Mdl();
        $resultExcepciones = $bloqDiferenciasModel->eliminarReg($identificador);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

        $ignoraDescuentoModel = new IgnoraDescuento_Mdl();

        // Preparar campos siguiendo el patrón de consumo
        $campos = [
            'idProveedor' => $idProveedor,
            'motivo' => $motivo,
            'estatus' => 1,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $ignoraDescuentoModel->registraIgnoraDescuento($campos);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de IdProveedor: $idProveedor <br>";
        }

        $exentoAnoFiscModel = new ExentoAnoFisc_Mdl();

        // Preparar campos siguiendo el patrón de consumo
        $campos = [
            'idProveedor' => $idProveedor,
            'estatus' => 1,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $exentoAnoFiscModel->registraExentoAnoFisc($campos);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

        $exentoFechaEmisionModel = new ExentoFechaEmision_Mdl();

        // Preparar campos siguiendo el patrón de consumo
        $campos = [
            'idProveedor' => $idProveedor,
            'estatus' => 1,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $exentoFechaEmisionModel->registraExentoFechaEmision($campos);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

        $usoCfdiDistintoModel = new UsoCfdiDistinto_Mdl();

        // Preparar campos siguiendo el patrón de consumo
        $campos = [
            'idProveedor' => $idProveedor,
            'usoCFDI' => $idUsoCfdi,
            'estatus' => 1,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $usoCfdiDistintoModel->registraUsoCfdiDistinto($campos);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

        $bloqDiferenciasModel = new BloqDiferencias_Mdl();

        // Preparar campos siguiendo el patrón de consumo
        $campos = [
            'idProveedor' => $idProveedor,
            'motivo' => $motivo,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $bloqDiferenciasModel->registraBloqDiferencias($campos);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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

    public function agregarProveedorPoliticasComerciales()
    {
        $idProveedor = (int) ($_POST['idProveedor'] ?? 0);
        $motivo = $_POST['motivo'] ?? '';
        $idUser = $_SESSION['EQXident'] ?? 0;

        $model = new PoliticasComerciales_Mdl();
        $result = $model->activarDescontarPromociones($idProveedor, $motivo, $idUser);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'] ?? ($result['success'] ? 'OK' : 'Error'),
        ]);
    }

    public function eliminarProveedorPoliticasComerciales()
    {
        $idProveedor = (int) ($_POST['idProveedor'] ?? 0);
        $idUser = $_SESSION['EQXident'] ?? 0;

        $model = new PoliticasComerciales_Mdl();
        $result = $model->desactivarDescontarPromociones($idProveedor, $idUser);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'] ?? ($result['success'] ? 'OK' : 'Error'),
        ]);
    }

    public function eliminarProveedorFechaPagoIgnorada()
    {
        $idProveedor = (int) ($_POST['idProveedor'] ?? 0);
        $idUser = $_SESSION['EQXident'] ?? 0;

        $model = new IgnorarFechaPago_Mdl();
        $result = $model->eliminarIgnoraFechaPago($idProveedor, $idUser);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'] ?? ($result['success'] ? 'OK' : 'Error'),
        ]);
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

    public function agregarProveedorPUE()
    {
        $idProveedor = $_POST['idProveedor'] ?? '';
        $fechaExpiracion = $_POST['fechaExpiracion'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        $permitePueModel = new PermitirPueSiempre_Mdl();
        $campos = [
            'idProveedor' => (int) $idProveedor,
            'fechaExpiracion' => $fechaExpiracion,
            'motivo' => trim($motivo),
            'estatus' => 1,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $permitePueModel->registraPermitirPueSiempre($campos);

        if ($resultExcepciones['success']) {
            echo json_encode([
                'success' => true,
                'message' => $resultExcepciones['message']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $resultExcepciones['message']
            ]);
        }
    }

    public function agregarProveedorIFP()
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

        $ignoraFechaPagoModel = new IgnorarFechaPago_Mdl();

        // Preparar campos siguiendo el patrón de consumo
        $campos = [
            'idProveedor' => $idProveedor,
            'motivo' => $motivo,
            'idUserReg' => $_SESSION['EQXident'] ?? 0
        ];

        $resultExcepciones = $ignoraFechaPagoModel->registraIgnoraFechaPago($campos);

        if ($resultExcepciones['success']) {
            $Message = $resultExcepciones['message'];
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
