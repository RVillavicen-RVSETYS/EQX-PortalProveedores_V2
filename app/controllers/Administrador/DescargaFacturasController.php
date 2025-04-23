<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\Compras\Compras_Mdl;
use App\Globals\Controllers\DocumentosController;
use App\Globals\Services\File\ZipService\CrearZipController;

class DescargaFacturasController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\DescargaFacturasController.php.</h2>";
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

        $proveedoresModel = new Proveedores_Mdl;
        $resultProveedores = $proveedoresModel->obtenerProveedores();

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\DescargaFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
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
                $this->view('Administrador/DescargaFacturas/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\DescargaFacturasController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\DescargaFacturasController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function listarFacturas()
    {

        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $filtros = [];
        if (!empty($_POST['idProveedor'])) {
            $filtros['idProveedor'] = $_POST['idProveedor'];
        }

        if (!empty($_POST['fechaInicial']) and !empty($_POST['fechaFinal'])) {
            $filtros['entreFechasRecepcion'] = $_POST['fechaInicial'] . ',' . $_POST['fechaFinal'];
        } else {
            $filtros['entreFechasRecepcion'] = date('Y-m-1') . ',' . date('Y-m-t');
        }

        $filtros['estatusFactura'] = '2';

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
        $this->view('Administrador/DescargaFacturas/listaFacturas', $data);
    }

    public function descargarAcuses()
    {
        if (!empty($_REQUEST['acuses'])) {
            $arrayAcuses = $_REQUEST['acuses'];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se recibio acuse de la factura.'
            ];
            echo json_encode($response);
            exit(0);
        }

        $arrayAcuses = explode(',', $arrayAcuses);

        $MDL_compras = new Compras_Mdl();
        $dataCompra = $MDL_compras->dataUrlPorAcuses($arrayAcuses);

        if (empty($dataCompra['data'])) {
            $response = [
                'success' => false,
                'message' => 'No se encontró información de las compras.'
            ];
            echo json_encode($response);
            exit(0);
        }

        if ($this->debug == 1) {
            echo '<br><br>Resultado de dataCompraPorAcuse: ' . PHP_EOL;
            var_dump($dataCompra);
        }

        $Ctrl_documentos = new DocumentosController();
        $rutasArchivos = [];

        foreach ($dataCompra['data'] as $registro) {
            $acuse = $registro['Acuse'];
            $rfc = $registro['RFC'];

            // Factura
            if (!empty($registro['FacUrlPDF'])) {
                $nombre = "{$acuse}_FAC_{$rfc}_{$registro['FacSerie']}.pdf";
                $rutaAbsoluta = $Ctrl_documentos->generadorDeRutas(base64_encode($registro['FacUrlPDF']), 'pdf');
                $rutasArchivos[] = [
                    'ruta' => $rutaAbsoluta,
                    'nombre' => $nombre
                ];
            }

            if (!empty($registro['FacUrlXML'])) {
                $nombre = "{$acuse}_FAC_{$rfc}_{$registro['FacSerie']}.xml";
                $rutaAbsoluta = $Ctrl_documentos->generadorDeRutas(base64_encode($registro['FacUrlXML']), 'xml');
                $rutasArchivos[] = [
                    'ruta' => $rutaAbsoluta,
                    'nombre' => $nombre
                ];
            }

            // Nota de Crédito
            if (!empty($registro['NCredUrlPDF'])) {
                $nombre = "{$acuse}_NCRED_{$rfc}_{$registro['NCredSerie']}.pdf";
                $rutaAbsoluta = $Ctrl_documentos->generadorDeRutas(base64_encode($registro['NCredUrlPDF']), 'pdf');
                $rutasArchivos[] = [
                    'ruta' => $rutaAbsoluta,
                    'nombre' => $nombre
                ];
            }

            if (!empty($registro['NCredUrlXML'])) {
                $nombre = "{$acuse}_NCRED_{$rfc}_{$registro['NCredSerie']}.xml";
                $rutaAbsoluta = $Ctrl_documentos->generadorDeRutas(base64_encode($registro['NCredUrlXML']), 'xml');
                $rutasArchivos[] = [
                    'ruta' => $rutaAbsoluta,
                    'nombre' => $nombre
                ];
            }

            // Complemento de Pago
            if (!empty($registro['CPagUrlPDF'])) {
                $nombre = "{$acuse}_CPAG_{$rfc}_{$registro['CPagSerie']}.pdf";
                $rutaAbsoluta = $Ctrl_documentos->generadorDeRutas(base64_encode($registro['CPagUrlPDF']), 'pdf');
                $rutasArchivos[] = [
                    'ruta' => $rutaAbsoluta,
                    'nombre' => $nombre
                ];
            }

            if (!empty($registro['CPagUrlXML'])) {
                $nombre = "{$acuse}_CPAG_{$rfc}_{$registro['CPagSerie']}.xml";
                $rutaAbsoluta = $Ctrl_documentos->generadorDeRutas(base64_encode($registro['CPagUrlXML']), 'xml');
                $rutasArchivos[] = [
                    'ruta' => $rutaAbsoluta,
                    'nombre' => $nombre
                ];
            }
        }

        if ($this->debug == 1) {
            echo '<br><br>Resultado de rutasArchivos: ' . PHP_EOL;
            var_dump($rutasArchivos);
        }

        $rutaSalida = 'Facturas_' . date('Y-m-d') . '.zip';
        $Ctrl = new CrearZipController();
        $listaCompras = $Ctrl->crearZipFacturas($rutasArchivos, $rutaSalida);
    }
}
