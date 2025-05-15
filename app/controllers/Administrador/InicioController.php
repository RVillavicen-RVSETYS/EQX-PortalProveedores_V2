<?php

namespace App\Controllers\Administrador;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\DatosCFDIs\CFDIs_Mdl;
use App\Models\Compras\ComprasAgrupadas_Mdl;
use App\Models\Proveedores\ProveedoresCompras_Mdl;

class InicioController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\Administrador\InicioController.php.</h2>";
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
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $cfdi_Mdl = new CFDIs_Mdl();
        $filtrosComplemento = [
            'saldoInsoluto' => true
        ];
        $agrupadoComplemento = ['uuidFact'];
        $valoresComplemento = ['minimoInsoluto'];
        $resultCompPago = $cfdi_Mdl->obtenerComplementosDePagoAgrupados($filtrosComplemento, $agrupadoComplemento, $valoresComplemento);
        if ($this->debug == 1) {
            echo '<br>Resultado de Query:';
            var_dump($resultCompPago);
            echo '<br><br>';
        }
        if ($resultCompPago['success']) {
            $data['datosIniciales']['InsolutosPendientes'] = $resultCompPago['cantRes'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar los complementos de pago: " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer los complementos de pago:' . $resultCompPago['message'];
            exit(0);
        }

        $comprasAgrupadas_Mdl = new ComprasAgrupadas_Mdl();
        $filtrosCompras = [
            'estatus' => 2,
            'debeComplemento' => true
        ];
        $agrupadoCompras = ['estatus'];
        $valoresCompras = ['cantCompras'];
        $resultCompPago = $comprasAgrupadas_Mdl->ComprasAgrupadas($filtrosCompras, $agrupadoCompras, $valoresCompras);
        if ($resultCompPago['success']) {
            $data['datosIniciales']['ComplementosPendientes'] = $resultCompPago['data'][0]['cantCompras'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar los Complementos Pendientes: " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer los complementos pendientes:' . $resultCompPago['message'];
            exit(0);
        }

        $filtrosCompras = [
            'estatus' => 2,
            'pendientePorPagar' => true
        ];
        $agrupadoCompras = ['estatus'];
        $valoresCompras = ['cantCompras'];
        $resultCompPago = $comprasAgrupadas_Mdl->ComprasAgrupadas($filtrosCompras, $agrupadoCompras, $valoresCompras);
        if ($resultCompPago['success']) {
            $data['datosIniciales']['PendientesPorPagar'] = $resultCompPago['data'][0]['cantCompras'];
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar los Complementos Pendientes: " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer los complementos pendientes:' . $resultCompPago['message'];
            exit(0);
        }

        $filtrosCompras = [
            'estatus' => 1,
            'pendientePorProcesar' => true
        ];
        $agrupadoCompras = ['estatus'];
        $valoresCompras = ['cantCompras'];
        $resultCompPago = $comprasAgrupadas_Mdl->ComprasAgrupadas($filtrosCompras, $agrupadoCompras, $valoresCompras);
        if ($resultCompPago['success']) {
            $data['datosIniciales']['PendientesPorProcesar'] = ($resultCompPago['cantRes'] > 0) ? $resultCompPago['data'][0]['cantCompras'] : 0;
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar los Complementos Pendientes: " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer los complementos pendientes:' . $resultCompPago['message'];
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
                $this->view('Administrador/Inicio/index', $data);
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function tablaProveedoresSeguimiento()
    {
        // Lógica para la vista de tablaProveedoresSeguimiento
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);

        $this->debug = 0;

        if ($this->debug == 1) {
            echo '<br>Datos recibidos por POST:';
            var_dump($_POST);
            echo '<br>';
        }

        $response = [
            'success' => false,
            'message' => 'No se pudieron obtener los datos iniciales.'
        ];

        if (empty($_POST['tipoSeguimiento'])) {
            if ($this->debug == 1) {
                echo '<br>Nose recibieron datos para el seguimiento.';
            }

            $response['message'] = 'No se recibieron datos para el seguimiento.';
            echo json_encode($response);
            exit(0);
        }

        switch ($_POST["tipoSeguimiento"]) {
            case 'ComplementosMasViejos':
                $filtrosComplemento = [
                    'estatus' => 2,
                    'debeComplemento' => true
                ];
                $agrupadoComplemento = ['idProveedor'];
                $valoresComplemento = ['cantCompras', 'sumaTotalFacturado', 'sumaTotalPagado', 'sumaTotalComplementos', 'minFechaPago', 'datosProveedor'];
                $orden = [
                    'campo' => 'minFechaPago',
                    'tipo' => 'ASC'
                ];
                break;

            case 'MasComplementos':
                $filtrosComplemento = [
                    'estatus' => 2,
                    'debeComplemento' => true
                ];
                $agrupadoComplemento = ['idProveedor'];
                $valoresComplemento = ['cantCompras', 'sumaTotalFacturado', 'sumaTotalPagado', 'sumaTotalComplementos', 'minFechaPago', 'datosProveedor'];
                $orden = [
                    'campo' => 'cantCompras',
                    'tipo' => 'DESC'
                ];
                break;

            case 'InsolutosPendientes':
                $filtrosComplemento = [
                    'estatus' => 2,
                    'insolutoPendiente' => true
                ];
                $agrupadoComplemento = ['idProveedor'];
                $valoresComplemento = ['cantCompras', 'minFechaPago', 'sumaInsolutos', 'datosProveedor'];
                $orden = [
                    'campo' => 'totalInsolutos',
                    'tipo' => 'DESC'
                ];
                break;

            default:
                $response['message'] = 'El tipo de Seguimiento no esta Definido.';
                echo json_encode($response);
                exit(0);
                break;
        }

        $provCompras_Mdl = new ProveedoresCompras_Mdl();
        $resultCompPago = $provCompras_Mdl->obtenerComprasProveedores($filtrosComplemento, $agrupadoComplemento, $valoresComplemento, $orden, 10);
        if ($this->debug == 1) {
            echo '<br>Resultado de Query:';
            var_dump($resultCompPago);
            echo '<br><br>';
        }
        if ($resultCompPago['success']) {
            $data['datosIniciales']['datosTabla'] = $resultCompPago['data'];

            // Cargar la vista correspondiente
            $this->view('Administrador/Inicio/tablaProveedoresSeguimiento', $data);
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\Administrador\InicioController ->Error al buscar los complementos de pago: " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer los complementos de pago:' . $resultCompPago['message'];
            exit(0);
        }
    }

    public function datosGraficoDona()
    {
        // Lógica para la vista de tablaProveedoresSeguimiento
        $data = []; // Aquí puedes pasar datos a la vista si es necesario

        // Obtener el nombre del namespace para identificar el área
        $namespaceParts = explode('\\', __NAMESPACE__);

        $this->debug = 0;

        //var_dump($_POST);
        if ($this->debug == 1) {
            echo '<br>Datos recibidos por POST:'.$_POST['parametro'];
            echo '<br>';
        }

        // Simular que recibimos un parámetro por POST
        $parametro = isset($_POST['parametro']) ? $_POST['parametro'] : null;

        // Validación básica
        if (empty($parametro)) {
            echo json_encode([
                'success' => 0,
                'mensaje' => 'Parámetro no recibido correctamente.'.$parametro,
                'data' => null
            ]);
            exit;
        }

        // Datos simulados por año
        $datos = [
            '2024' => [
                'values' => [
                    ['Pending', 40],
                    ['Failed', 20],
                    ['Success', 40]
                ],
                'labels' => [
                    'show' => true
                ],
                'title' => 'Estado General 2023',
                'legends' => [
                    'hide' => false
                ],
                'colors' => ['#FF9800', '#F44336', '#4CAF50']
            ],
            '2025' => [
                'values' => [
                    ['Pending', 25],
                    ['Failed', 15],
                    ['Success', 60]
                ],
                'labels' => [
                    'show' => false
                ],
                'title' => 'Estado General 2024',
                'legends' => [
                    'hide' => true
                ],
                'colors' => ['#03A9F4', '#E91E63', '#8BC34A'],
            ]
        ];

        // Verificar si hay datos para el parámetro enviado
        if (array_key_exists($parametro, $datos)) {
            echo json_encode([
                'success' => 1,
                'mensaje' => 'Datos cargados correctamente.',
                'data' => $datos[$parametro]
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'mensaje' => 'No se encontraron datos para el parámetro proporcionado.',
                'data' => null
            ]);
        }
    }


    public function logout()
    {
        echo 'Adios...';
    }
}
