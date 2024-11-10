<?php

namespace App\Controllers;

use App\Models\LoginAdmin_Mdl;

class LoginAdmin
{
    protected $debug = 0; // Variable para habilitar la depuración

    public function index()
    {
        // Cargar la vista de login
        require_once '../../app/views/login/indexAdmin.php';
    }

    public function authenticate()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\LoginAdmin.</h2>";
        }
        // Obtener usuario y contraseña desde el formulario
        $username = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->debug == 1) {
            echo "<h2>Antes de invocar al modelo.</h2>";
        }
        $loginModel = new LoginAdmin_Mdl();
        if ($this->debug == 1) {
            echo "<h2>Modelo instanciado correctamente.</h2>";
        }

        //Validamos la autenticación del usuario que existe y que pueda acceder
        $authResult = $loginModel->verificarAdministrador($username, $password);

        if ($authResult['success']) {
            // Usuario autenticado correctamente
            if ($this->debug == 1) {
                echo '<br>Autenticado correctamente: ' . $authResult['data']['empleado_nombreCorto'] . '. <br>';
            }

            $verificaArea = $loginModel->obtenerPrimerArea($authResult['data']['idNivel']);

            if ($verificaArea['success']) {
                // Si se encontro un Area a la que tiene acceso preparamos la ruta
                $urlAcceso = $verificaArea['data']['linkArea'].'/'. $verificaArea['data']['linkMenu'];

                // Genera las variables de session.
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['EQXident'] = $authResult['data']['id'];
                $_SESSION['EQXidNivel'] = $authResult['data']['idNivel'];
                $_SESSION['EQXnombreNivel'] = $authResult['data']['nivel_nombre'];
                $_SESSION['EQXnombreUser'] = trim($authResult['data']['empleado_nombre']).' '.trim($authResult['data']['apellidoPat']);
                $_SESSION['EQXnombreUserCto'] = $authResult['data']['empleado_nombreCorto'];
                $_SESSION['EQXgenero'] = 'Masculino';
                $_SESSION['EQXidSuc'] = $authResult['data']['idSucursal'];
                $_SESSION['EQXnombreSuc'] = $authResult['data']['suc_nombre'];
                $_SESSION['EQXidEmpresa'] = $authResult['data']['emp_id'];
                $_SESSION['EQXpyme'] = $authResult['data']['emp_nombreCorto'];
                $_SESSION['EQXnombreSucCto'] = $authResult['data']['suc_nombreCorto'];
                $_SESSION['EQXnoEmpleado'] = $authResult['data']['idEmpleado'];
                $_SESSION['EQXidioma'] = $authResult['data']['idioma'];
                $_SESSION['EQXAdmin'] = true;

                if ($this->debug == 1) {
                    echo '<br><br>Variables de Session cargadas:<br>';
                    var_dump($_SESSION);
                    echo '<br><br>';
                    echo '<br>Se encontro Autorización a: ' . $verificaArea['data']['nombre'] . '. <br>Sera dirigido a:'.$urlAcceso;
                }

                if ($this->debug == 0) {
                             
                    // Responde con JSON si es una solicitud AJAX
                    if ($this->isAjaxRequest()) {
                        echo json_encode([
                            'success' => true,
                            'redirect' => '/'.$urlAcceso // Redirige al área de inicio si la autenticación es exitosa
                        ]);
                    } else {
                        header('Location: /'.$urlAcceso); // Redirige al área de inicio si la autenticación es exitosa
                        exit();
                    }
                }

            } else {
                $errorMessage = $authResult['message'] ?? 'No tienes acceso a ningun Area. Notifica al administrador.';
                if ($this->debug == 1) {
                    echo '<br>Error: ' . $errorMessage . ' <br>****  BLOQUEO y redirección a: index.php';
                } else {
                    if ($this->isAjaxRequest()) {
                        echo json_encode([
                            'success' => false,
                            'message' => $errorMessage
                        ]);
                    } else {
                        $_SESSION['error_message'] = $errorMessage; // Guarda el mensaje de error en la sesión
                        header('Location: ' . URL_BASE_PROYECT.'/AdminFilos'); // Redirige al formulario
                        exit();
                    }
                }
            }
    
            
        } else {
            // Autenticación fallida, manejar mensaje de error específico
            $errorMessage = $authResult['message'] ?? 'Acceso Denegado sin detalles.';
    
            if ($this->debug == 1) {
                echo '<br>Error: ' . $errorMessage . ' <br>****  BLOQUEO y redirección a: index.php';
            } else {
                if ($this->isAjaxRequest()) {
                    echo json_encode([
                        'success' => false,
                        'message' => $errorMessage
                    ]);
                } else {
                    $_SESSION['error_message'] = $errorMessage; // Guarda el mensaje de error en la sesión
                    header('Location: ' . URL_BASE_PROYECT.'/AdminFilos'); // Redirige al formulario
                    exit();
                }
            }
        }
    }

    private function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

}
