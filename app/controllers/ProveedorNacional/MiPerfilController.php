<?php

namespace App\Controllers\ProveedorNacional;

use Core\Controller;
use App\Models\Menu_Mdl;
use App\Models\Notificaciones\NotificaProveedores_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Globals\FuncionesBasicas\FuncionesBasicas;
use DateTime;

class MiPerfilController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\ProveedorNacional\MiPerfil.php.</h2>";
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
            error_log("[$timestamp] app\controllers\ProveedorNacional\MiPerfil ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el id del Area:' . $resultIdArea['message'];
            exit(0);
        }

        $menuData = $MDL_menuModel->obtenerEstructuraMenu($_SESSION['EQXidNivel'], $idArea);
        $areaData = $MDL_menuModel->listarAreasDisponibles($_SESSION['EQXidNivel']);

        $MDL_notificaProveedor = new NotificaProveedores_Mdl();
        $notificaciones = $MDL_notificaProveedor->NotificacionesProveedor($_SESSION['EQXpais']);

        if ($menuData['success']) {
            if ($areaData['success']) {
                // Enviar datos a la Vista
                $data['menuData'] =  $menuData;
                $data['areaData'] =  $areaData;
                $data['areaLink'] =  $areaLink;
                $data['notificaciones'] =  $notificaciones;

                $datosIniciales = $this->datosIniciales();
                if ($datosIniciales['success']) {
                    $data['datosIniciales'] = $datosIniciales;

                    $FNC_funcionesBasicas = new FuncionesBasicas();
                    $tiempoPago = $FNC_funcionesBasicas->convertirCompromisoPago($datosIniciales['datosProveedores']['CPag']);

                    if ($tiempoPago['success']) {
                        $data['datosIniciales']['CompromisoPago']['cantidad'] = $tiempoPago['data']['cantidad'];
                        $data['datosIniciales']['CompromisoPago']['tiempo'] = $tiempoPago['data']['tiempo'];
                    } else {
                        $timestamp = date("Y-m-d H:i:s");
                        error_log("[$timestamp] app\controllers\ProveedorNacional\MiPerfil ->Error al obtener los datos iniciales: " . PHP_EOL, 3, LOG_FILE);
                    }

                    // Cargar la vista correspondiente
                    $this->view('ProveedorNacional/MiPerfil/index', $data);
                } else {
                    $timestamp = date("Y-m-d H:i:s");
                    error_log("[$timestamp] app\controllers\ProveedorNacional\MiPerfil ->Error al obtener los datos iniciales: " . PHP_EOL, 3, LOG_FILE);
                    echo 'No pudimos traer los datos iniciales:' . $datosIniciales['message'];
                    exit(0);
                }
            } else {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app\controllers\Administrador\MiPerfil ->Error al listar las Areas: " . PHP_EOL, 3, LOG_FILE);
                echo 'Problemas con las Areas de Acceso:' . $resultIdArea['message'];
                exit(0);
            }
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\controllers\ProveedorNacional\MiPerfil ->Error al buscar Id del Area (nombre: $areaLink): " . PHP_EOL, 3, LOG_FILE);
            echo 'No pudimos traer el detallado del Menu:' . $resultIdArea['message'];
            exit(0);
        }
    }

    public function ActualizarDatosProveedor()
    {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        $noProveedor = $_SESSION['EQXnoProveedor'] ?? '';       
        $campos = [];
        $filtros = [
            'id' => $noProveedor
        ];

        if ($this->debug == 1) {
            echo "<br>Contenido de data:<br>";
            var_dump($data);
            echo "<br>Contenido de POST: ";
            var_dump($_POST);
            echo "<br>No. Proveedor: $noProveedor<br>";
        }

        if(empty($_POST['correo'])){
            $response = [
                'success' => false,
                'message' => 'El campo de correo no puede estar vacío.'
            ];
            echo json_encode($response);
            exit(0);
        } else {
            $correo = $_POST['correo'];
            $campos['correo'] = $correo;

            if (empty($_POST['password1']) && empty($_POST['password2'])) {
                # Si ambas contraseñas venian vacias no se cambia la contraseña
            } else {
                if ($_POST['password1'] != $_POST['password2']) {
                    $response = [
                        'success' => false,
                        'message' => 'Las contraseñas no coinciden.'
                    ];
                    echo json_encode($response);
                    exit(0);
                }  else {
                    $password = $_POST['password1'];
                    $campos['pass'] = $password;
                    
                }
            }

            $MDL_proveedores = new Proveedores_Mdl();
            $resultActualizaProveedor = $MDL_proveedores->actualizarDatosProveedor($campos, $filtros);

            if ($this->debug == 1) {
                echo '<br><br>Resultado de Actualizar Datos de Proveedores: ' . PHP_EOL;
                var_dump($resultActualizaProveedor);
            }
            
            if ($resultActualizaProveedor['success']) {
                $response = [
                    'success' => true,
                    'message' => $resultActualizaProveedor['filasAfectadas'].' Registro actualizado correctamente.',
                    'correo' => $correo
                ];
                echo json_encode($response);
                exit(0);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se pudieron actualizar los datos.'
                ];
                echo json_encode($response);
                exit(0);
            }
        }
    }

    private function datosIniciales()
    {
        $response = [
            'success' => false,
            'message' => 'No se pudieron obtener los datos iniciales.'
        ];

        $noProveedor = $_SESSION['EQXnoProveedor'];
        $MDL_proveedores = new Proveedores_Mdl();
        $datosProveedores = $MDL_proveedores->obtenerDatosProveedor($noProveedor);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de Datos de Proveedores: ' . PHP_EOL;
            var_dump($datosProveedores);
        }

        if ($datosProveedores['success']) {
            $response = [
                'success' => true,
                'datosProveedores' => $datosProveedores['data'],
            ];
        } else {
            $errorMessage = $datosProveedores['message'];
            $response = [
                'message' => $errorMessage
            ];
        }

        return $response;
    }
}
