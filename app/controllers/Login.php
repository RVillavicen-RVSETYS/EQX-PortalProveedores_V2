<?php

namespace App\Controllers;

use App\Models\Login_Mdl;

class Login
{
    protected $debug = 0; // Variable para habilitar la depuración

    public function index()
    {
        // Cargar la vista de login
        require_once '../app/views/login/index.php';
    }

    public function authenticate()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\login.</h2>";
        }
        // Obtener usuario y contraseña desde el formulario
        $username = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->debug == 1) {
            echo "<h2>Antes de invocar al modelo.</h2>";
        }
        $loginModel = new Login_Mdl();
        if ($this->debug == 1) {
            echo "<h2>Modelo instanciado correctamente.</h2>";
        }

        //Validamos la autenticación del usuario que existe y que pueda acceder
        $authResult = $loginModel->verificarProveedor($username, $password);

        if ($authResult['success']) {
            // Usuario autenticado correctamente
            if ($this->debug == 1) {
                echo '<br>Autenticado correctamente: ' . $authResult['data']['nombre'] . '. <br>';
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
                $_SESSION['EQXnombreUser'] = $authResult['data']['nombre'];
                $_SESSION['EQXnombreUserCto'] = substr($authResult['data']['nombre'], 0, 20);
                $_SESSION['EQXgenero'] = 'Masculino';
                $_SESSION['EQXnoProveedor'] = $authResult['data']['id'];
                $_SESSION['EQXrfc'] = $authResult['data']['rfc'];
                $_SESSION['EQXcorreo'] = $authResult['data']['correo'];
                $_SESSION['EQXpais'] = $authResult['data']['pais'];
                $_SESSION['EQXmoneda'] = $authResult['data']['moneda'];
                $_SESSION['EQXidioma'] = $authResult['data']['idioma'];
                $_SESSION['EQXAdmin'] = false;               

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
                            'redirect' => '/'.$urlAcceso // Redirige al área de inicio o dashboard
                        ]);
                    } else {
                        header('Location: /'.$urlAcceso); // Redirigir si la autenticación es exitosa
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
                        header('Location: ' . URL_BASE_PROYECT); // Redirige al formulario
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
                    header('Location: ' . URL_BASE_PROYECT); // Redirige al formulario
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
