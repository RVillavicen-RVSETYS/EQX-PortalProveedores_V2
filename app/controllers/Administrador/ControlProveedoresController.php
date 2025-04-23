<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Compras\Compras_Mdl;
use App\Globals\Controllers\DocumentosController;
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
        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = $_POST['idProveedor'];
            $data['idProveedor'] = $_POST['idProveedor'];
        }
        
        $filtros['pendientePago'] = '1';

        $MDL_compras = new Compras_Mdl();

        $listaCompras = $MDL_compras->listaComprasFacturadas($filtros, 0, 'DESC');
        if ($this->debug == 1) {
            echo '<br><br>Resultado de listaComprasFacturadas: ' . PHP_EOL;
            var_dump($listaCompras);
        }

        if ($listaCompras['success']) {
            $data['listaCompras'] =  $listaCompras['data'];
        } else {
            echo '
            <div class="alert alert-warning alert-rounded"> 
                <i class="ti-user"></i> ' . $listaCompras['message'] . '.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>';
        }

        // Cargar la vista correspondiente
        $this->view('Administrador/ControlProveedores/facSinFechaPago', $data);
    }

    public function historico()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = $_POST['idProveedor'];
            $data['idProveedor'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechas'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        }

        $MDL_compras = new Compras_Mdl();

        $listaCompras = $MDL_compras->listaComprasFacturadas($filtros, 0, 'DESC');
        if ($this->debug == 1) {
            echo '<br><br>Resultado de listaComprasFacturadas: ' . PHP_EOL;
            var_dump($listaCompras);
        }

        if ($listaCompras['success']) {
            $data['listaCompras'] =  $listaCompras['data'];
        } else {
            echo '
            <div class="alert alert-warning alert-rounded"> 
                <i class="ti-user"></i> ' . $listaCompras['message'] . '.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>';
        }

        // Cargar la vista correspondiente
        $this->view('Administrador/ControlProveedores/historialFacturas', $data);
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

    public function listaHistorico()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechas'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        }


        $MDL_compras = new Compras_Mdl();

        $listaCompras = $MDL_compras->listaComprasFacturadas($filtros, 0, 'DESC');
        if ($this->debug == 1) {
            echo '<br><br>Resultado de listaComprasFacturadas: ' . PHP_EOL;
            var_dump($listaCompras);
        }

        if ($listaCompras['success']) {
            $data['listaCompras'] =  $listaCompras['data'];
        } else {
            echo '
            <div class="alert alert-warning alert-rounded"> 
                <i class="ti-user"></i> ' . $listaCompras['message'] . '.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>';
        }

        // Cargar la vista correspondiente
        $this->view('Administrador/FacturasNacionales/listaAprobacionesNa', $data);
    }

    public function detalladoDeCompra()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $noProveedor = (empty($_POST['idProveedor'])) ? '' : $_POST['idProveedor'];
        $acuse = (empty($_POST['acuse'])) ? '' : $_POST['acuse'];

        $MDL_compras = new Compras_Mdl();
        $dataCompra = $MDL_compras->dataCompraPorAcuse($noProveedor, $acuse);

        $proveedoresModel = new Proveedores_Mdl();
        $resultProveedores = $proveedoresModel->obtenerDatosProveedor($noProveedor);

        $data['noProveedor'] = $noProveedor;
        $data['acuse'] =  $acuse;
        $data['dataCompra'] =  $dataCompra;
        $data['dataProveedor'] =  $resultProveedores['data'];
        $data['puedeAutorizar'] = 1; // Cambiar a 0 si no puede autorizar
        $data['puedeRechazar'] = 1; // Cambiar a 0 si no puede regresar

        if ($this->debug == 1) {
            echo 'Variables enviadas:' . PHP_EOL;
            var_dump($data);
            echo '<br><br>';
        }

        // Cargar la vista correspondiente
        $this->view('Administrador/VistasCompartidas/detalladoDeCompra', $data);
    }

    public function verDocumento()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        //Obtener Parametros
        $params = func_get_args();

        if ($this->debug == 1) {
            echo "Parámetros recibidos:<br>";
            echo "<pre>";
            print_r($params);
            echo "</pre>";
        }

        $tipoDocumeto = $params[0];
        $rutaDocumento = $params[1];

        $Ctrl_Documentos = new DocumentosController();
        return $Ctrl_Documentos->mostrarDocumento($rutaDocumento, $tipoDocumeto);
    }

    public function cambiarFechaPago()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $acuse = $_POST['acuse'] ?? '';
        $nuevaFecha = $_POST['nuevaFecha'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de POST: ";
            var_dump($_POST);
            echo "<br>Acuse: $acuse<br>";
            echo "<br>Nueva Fecha: $nuevaFecha<br>";
        }

        if (empty($acuse)) {
            $response = [
                'success' => false,
                'message' => 'No se recibio acuse de la factura.'
            ];
            echo json_encode($response);
            exit(0);
        }
        if (empty($nuevaFecha)) {
            $response = [
                'success' => false,
                'message' => 'No se recibio la nueva fecha de pago.'
            ];
            echo json_encode($response);
            exit(0);
        }

        $campos = [
            'fechaVence' => $nuevaFecha
        ];
        $filtros = [
            'id' => $acuse
        ];

        $MDL_compras = new Compras_Mdl();
        $resultActualizaFactura = $MDL_compras->actualizarDataCompras($campos, $filtros);

        if ($this->debug == 1) {
            echo '<br><br>Resultado de Actualizar Datos de Facturas: ' . PHP_EOL;
            var_dump($resultActualizaFactura);
        }

        if ($resultActualizaFactura['success']) {
            $response = [
                'success' => true,
                'message' => $resultActualizaFactura['message']
            ];
            echo json_encode($response);
            exit(0);
        } else {
            $response = [
                'success' => false,
                'message' => 'Error al aceptar factura.'
            ];
            echo json_encode($response);
            exit(0);
        }
    }

    public function logout()
    {
        echo 'Shu bye...';
    }
}
