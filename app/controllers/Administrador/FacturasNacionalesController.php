<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\Compras\Compras_Mdl;
use App\Globals\Controllers\DocumentosController;
use App\Models\Facturas\Nacionales_Mdl;
use App\Models\DatosCompra\NotasCredito_Mdl;

class FacturasNacionalesController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\FacturasNacionalesController.php.</h2>";
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
        $resultMonedas = $proveedoresModel->obtenerMonedasProveedores();
        $resultProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\FacturasNacionalesController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $data['listaMonedas'] = $resultMonedas;
                $data['listaProveedores'] = $resultProveedores;

                // Cargar la vista correspondiente
                $this->view('Administrador/FacturasNacionales/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\FacturasNacionalesController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\FacturasNacionalesController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listaAprobacionesNa()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechasPago'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        }

        if (!empty($_POST['tipoMoneda'])) {
            $filtros['tipoMoneda'] = $_POST['tipoMoneda'];
        }

        $filtros['nacional'] = '1';

        $filtros['estatusFactura'] = '1';

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

        $data['noProveedor'] = $noProveedor;
        $data['acuse'] =  $acuse;
        $data['dataCompra'] =  $dataCompra;
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

    public function rechazarFactura()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $acuse = $_POST['acuse'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de POST: ";
            var_dump($_POST);
            echo "<br>Acuse: $acuse<br>";
            echo "<br>Motivo: $motivo<br>";
        }

        if (empty($acuse)) {
            $response = [
                'success' => false,
                'message' => 'No se recibio acuse de la factura.'
            ];
            echo json_encode($response);
            exit(0);
        }

        if (empty($motivo)) {
            $response = [
                'success' => false,
                'message' => 'No se recibio el motivo para rechazar la factura.'
            ];
            echo json_encode($response);
            exit(0);
        }

        $campos = [
            'estatus' => 3,
            'comentRegresa' => $motivo
        ];
        $filtros = [
            'id' => $acuse
        ];

        $MDL_compras = new Compras_Mdl();
        $resultActualizaFactura = $MDL_compras->actualizarDataCompras($campos, $filtros);

        if ($this->debug == 1) {
            echo '<br><br>Resultado de Actualizar Datos de Factura: ' . PHP_EOL;
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
                'message' => 'Error al regresar factura al proveedor.'
            ];
            echo json_encode($response);
            exit(0);
        }
    }

    public function aceptarFactura()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $acuse = $_POST['acuse'] ?? '';
        
        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de POST: ";
            var_dump($_POST);
            echo "<br>Acuse: $acuse<br>";
        }

        if (empty($acuse)) {
            $response = [
                'success' => false,
                'message' => 'No se recibio acuse de la factura.'
            ];
            echo json_encode($response);
            exit(0);
        }

        $campos = [
            'estatus' => 2
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
            'fechaProbablePago' => $nuevaFecha,
            'estatus' => 2
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

    public function actualizarEstatusNotaCredito()
    {
        $idNC = $_POST['idNC'] ?? '';
        $uuidNC = $_POST['uuidNC'] ?? '';
        $estatus = $_POST['estatus'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de POST: ";
            var_dump($_POST);
            echo "<br>idNC: $idNC<br>";
            echo "<br>uuidNC: $uuidNC<br>";
            echo "<br>estatus: $estatus<br>";
        }

        if (empty($idNC) || empty($estatus)) {
            $response = [
                'success' => false,
                'message' => 'No se recibieron los datos necesarios para actualizar la nota de crédito.'
            ];
            echo json_encode($response);
            exit(0);
        }

        // Validar que el estatus sea válido (0, 1, 2, o 3)
        if (!in_array($estatus, ['0', '1', '2', '3'])) {
            $response = [
                'success' => false,
                'message' => 'El estatus proporcionado no es válido.'
            ];
            echo json_encode($response);
            exit(0);
        }

        try {
            // Usar el modelo siguiendo el estándar del proyecto
            $MDL_NotasCredito = new NotasCredito_Mdl();
            
            // Preparar campos según el estatus
            $campos = ['estatus' => $estatus];
            $idUser = $_SESSION['EQXident'] ?? 0;
            
            // Si es aceptada (estatus 2), agregar campos de validación
            if ($estatus == '2') {
                $campos['idUserValida'] = $idUser;
                $campos['fechaValida'] = date('Y-m-d H:i:s');
                // Limpiar campos de rechazo si existían
                $campos['idUserRechaza'] = null;
                $campos['fechaRechaza'] = null;
                $campos['motivoRechazo'] = null;
            }
            
            // Si es rechazada (estatus 3), agregar campos de rechazo
            if ($estatus == '3') {
                $motivoRechazo = $_POST['motivoRechazo'] ?? '';
                if (empty($motivoRechazo)) {
                    $response = [
                        'success' => false,
                        'message' => 'El motivo del rechazo es obligatorio.'
                    ];
                    echo json_encode($response);
                    exit(0);
                }
                $campos['idUserRechaza'] = $idUser;
                $campos['fechaRechaza'] = date('Y-m-d H:i:s');
                $campos['motivoRechazo'] = $motivoRechazo;
                // Limpiar campos de validación si existían
                $campos['idUserValida'] = null;
                $campos['fechaValida'] = null;
            }
            
            // Actualizar usando el método genérico del modelo
            $resultado = $MDL_NotasCredito->actualizarNotaCredito($campos, ['id' => $idNC]);

            if ($resultado['success']) {
                $mensajesEstatus = [
                    '0' => 'Nota de crédito marcada como cancelada correctamente.',
                    '1' => 'Nota de crédito marcada como pendiente correctamente.',
                    '2' => 'Nota de crédito aceptada correctamente.',
                    '3' => 'Nota de crédito rechazada correctamente.'
                ];

                $response = [
                    'success' => true,
                    'message' => $mensajesEstatus[$estatus] ?? 'Estatus actualizado correctamente.'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => $resultado['message'] ?? 'Error al actualizar el estatus de la nota de crédito.'
                ];
            }

            echo json_encode($response);
            exit(0);

        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Controllers/Administrador/FacturasNacionalesController.php ->Error al actualizar estatus de nota de crédito: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE);
            
            $response = [
                'success' => false,
                'message' => 'Error al actualizar el estatus de la nota de crédito.'
            ];
            echo json_encode($response);
            exit(0);
        }
    }
}
