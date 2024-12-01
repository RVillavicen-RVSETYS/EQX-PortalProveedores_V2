<?php

namespace App\Controllers\ProveedorNacional;

use App\Models\Compras\Compras_Mdl;
use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\verDocumentos_Mdl;
use App\Models\Configuraciones\CierrePortal_Mdl;
use App\Models\Notificaciones\NotificaProveedores_Mdl;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;

class InicioController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\InicioController.php.</h2>";
        }
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        $this->checkSession();
    }

    public function index()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);
        $areaLink = end($namespaceParts); // Obtiene el ultimo parametro del NameSpace

        $MDL_menuModel = new Menu_Mdl();
        $resultIdArea = $MDL_menuModel->obtenerIdAreaPorLink($areaLink);

        if ($resultIdArea['success']) {
            $idArea = $resultIdArea['data'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $MDL_menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $MDL_menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        $MDL_cierrePortal = new CierrePortal_Mdl();
        $bloqueoCargaFactura = $MDL_cierrePortal->verificaCierreDePortal($_SESSION['EQXnoProveedor']);

        $MDL_notificaProveedor = new NotificaProveedores_Mdl();
        $notificaciones = $MDL_notificaProveedor->NotificacionesProveedor($_SESSION['EQXpais']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                // Enviar datos a la Vista
                $data['menuData'] =  $menuData;
                $data['areaData'] =  $areaData;
                $data['areaLink'] =  $areaLink;
                $data['bloqueoCargaFactura'] =  $bloqueoCargaFactura;
                $data['notificaciones'] =  $notificaciones;

                // Cargar la vista correspondiente
                $this->view('ProveedorNacional/Inicio/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorNacional\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function tablaUltimas50Facturas(){
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        $noProveedor = $_SESSION['EQXnoProveedor'];
        $MDL_compras = new Compras_Mdl();
        $listaCompras = $MDL_compras->listaComprasFacturadas($noProveedor, 50);
        
        $data['listaCompras'] =  $listaCompras['data'];
        
        // Cargar la vista correspondiente
        $this->view('ProveedorNacional/Inicio/listaCompras', $data);
    }

    public function detalladoDeCompra(){
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $noProveedor = $_SESSION['EQXnoProveedor'];
        $acuse = (empty($_POST['acuse'])) ? '' : $_POST['acuse'] ;

        $MDL_compras = new Compras_Mdl();
        $dataCompra = $MDL_compras->dataCompraPorAcuse($noProveedor, $acuse);
        
        $data['noProveedor'] = $noProveedor;
        $data['acuse'] =  $acuse;
        $data['dataCompra'] =  $dataCompra;

        if ($this->debug == 1) {
            echo 'Variables enviadas:'.PHP_EOL;
            var_dump($data);
            echo '<br><br>';
        }
        
        // Cargar la vista correspondiente
        $this->view('ProveedorNacional/Inicio/detalladoDeCompra', $data);
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
        #echo base64_encode('Facturas/137/2024-11/93130_WE_E002791.pdf');
        $tipoDocumeto = $params[0];
        $rutaFile = base64_decode($params[1]);
        $documentoModel = new verDocumentos_Mdl();

        switch ($tipoDocumeto) {
            case 'PDF':
                $documentoModel->obtenerPDF($rutaFile);
                break;
            
            case 'XML':
                $documentoModel->obtenerXML($rutaFile);
                break;
            
            default:
                echo 'Tipo de documento no definido'.PHP_EOL;
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\InicioController ->(verDocumento)Tipo de Documento Desconocido: $tipoDocumeto" . PHP_EOL, 3, LOG_FILE);
                break;
        }      
        
    }

    public function validaOrdenCompra()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de ordenCompra: $ordenCompra<br>";
            echo "<br>Contenido de noProveedor: $noProveedor<br>";
        }

        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);
        $MDL_anticipos = new Anticipos_Mdl();
        $verificaDebeAnticipo = $MDL_anticipos->verificaAnticipoDeOrdenCompra($ordenCompra);

        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];

            // Verifica si debe Anticipos la OC
            if ($verificaDebeAnticipo['success']) {
                $solicitaNotaCredito = '
                                                        <div class="form-group">
                                                            <label>Nota de Credito</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf"></i> PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="notaCredPDF" name="notaCredPDF" required>
                                                                    <label class="custom-file-label" for="facturaPDF">Elegir PDF de Nota de Credito..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-code"></i> XML</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="notaCredXML" name="notaCredXML" required>
                                                                    <label class="custom-file-label" for="facturaXML">Elegir XML de Nota de Credito..</label>
                                                                </div>
                                                            </div>
                                                        </div>';
                echo json_encode([
                    'success' => true,
                    'message' => $Message,
                    'anticipo' => true,
                    'solicitaNotaCredito' => $solicitaNotaCredito,
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => $Message,
                    'anticipo' => false
                ]);
            }
        } else {
            $errorMessage = $validOrdenCompra['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }

    public function validaHojaEntrada()
    {
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $ordenCompra = $_POST['ordenCompra'] ?? '';
        $hojaEntrada = $_POST['hojaEntrada'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de ordenCompra: $ordenCompra<br>";
            echo "<br>Contenido de hojaEntrada: $hojaEntrada<br>";
        }

        if (empty($ordenCompra) || empty($hojaEntrada)) {
            if (empty($ordenCompra)) {
                $Message = 'Registra primero una Orden de Compra.';
                echo json_encode([
                    'success' => false,
                    'message' => $Message
                ]);
            } else {
                $Message = 'No se recibio una Hoja de Entrada.';
                echo json_encode([
                    'success' => false,
                    'message' => $Message
                ]);
            }
        } else {
            $MDL_hojaEntrada = new HojaEntrada_Mdl();
            $validHES = $MDL_hojaEntrada->verificaHojaEntrada($ordenCompra, $hojaEntrada);

            if ($validHES['success']) {
                $Message = $validHES['cantHES'] . ' Hes validas.';
                echo json_encode([
                    'success' => true,
                    'message' => $Message
                ]);
            } else {
                $Message = $validHES['message'];
                echo json_encode([
                    'success' => false,
                    'message' => $Message
                ]);
            }
        }
    }

    public function validaAnticipo()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $anticipo = $_POST['anticipo'] ?? '';
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de Anticipo: $anticipo<br>";
            echo "<br>Contenido de noProveedor: $noProveedor<br>";
        }

        if (empty($anticipo)) {
            $Message = 'Ingresa un Codigo de Anticipo.';
            echo json_encode([
                'success' => false,
                'message' => $Message
            ]);
            exit(0);
        }

        $MDL_anticipos = new Anticipos_Mdl();
        $verificaAnticipo = $MDL_anticipos->verificaAnticipo($anticipo, $noProveedor);

        if ($verificaAnticipo['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'OK',
                'anticipo' => false
            ]);
        } else {
            $errorMessage = $verificaAnticipo['message'];
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }
}
