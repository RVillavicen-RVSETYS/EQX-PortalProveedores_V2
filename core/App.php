<?php

namespace Core;

class App
{
    protected $controller = 'Login'; // Controlador predeterminado
    protected $method = 'index';      // Método predeterminado
    protected $params = [];           // Parámetros de la URL
    protected $debug = 0;             // Variable de depuración, cámbiala a 0 para desactivar logs

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de core\App.</h2>";
        }

        // Procesar la URL
        $url = $this->parseUrl();
        if ($this->debug == 1) {
            echo '<br>Retorno de parseUrl:';
            var_dump($url); // Imprime la URL procesada
            echo '<br>';
        }

        // Si la URL está vacía, establecer el controlador y método predeterminados
        if (empty($url)) {
            if ($this->debug == 1) {
                echo '<br>La funcion parseUrl no detecto solicitud a ningun controlador sera redirigido a Login:<br>';
            }
            $url = [$this->controller, $this->method]; // [Login, index]
        }

        // Verificar si la URL tiene un controlador en una subcarpeta
        $controllerPath = "../app/Controllers/" . ucfirst($url[0]);
        if (is_dir($controllerPath) && isset($url[1])) {
            if ($this->debug == 1) {
                echo "Si encontro la Ruta del controlador: '" . $controllerPath . "'<br>";
            }
            $controllerFile = $controllerPath . '/' . ucfirst($url[1]) . 'Controller.php';
            if (file_exists($controllerFile)) {
                $this->controller = ucfirst($url[0]) . '\\' . ucfirst($url[1]) . 'Controller';
                $controllerClass = str_replace('/', '\\', $this->controller);
                $this->controller = $controllerClass;
                unset($url[0], $url[1]);
                if ($this->debug == 1) {
                    echo "Registramos en this->Controller y vaciamos url0 y url1.<br>";
                }
                if ($this->debug == 1) {
                    echo "Controlador en subcarpeta encontrado: '" . $this->controller . "'<br>";
                }
            } else {
                if ($this->debug == 1) {
                    echo "Controlador no encontrado: $controllerFile <br>Redirigiendo a 404.<br>";
                    exit();
                }
                $this->logAndRedirect404("Controlador en subcarpeta no encontrado", $url[1] ?? 'N/A');
            }
        }
        // Verificar si el controlador solicitado existe en /app/Controllers/
        elseif ($url && file_exists("../app/Controllers/" . ucfirst($url[0]) . ".php")) {
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
            if ($this->debug == 1) {
                echo "Controlador encontrado: '" . $this->controller . "'<br>";
            }
        } else {
            // Si el controlador no existe, redirigir a la página 404
            if ($this->debug == 1) {
                echo "Controlador no encontrado: $this->controller <br>Redirigiendo a 404.<br>";
                exit();
            }
            $this->logAndRedirect404("Controlador como archivo no encontrado", $url[0] ?? 'N/A');
        }

        // Incluir y crear una instancia del controlador
        require_once "../app/Controllers/" . $this->controller . ".php";
        $controllerClass = "App\\Controllers\\" . str_replace('/', '\\', $this->controller);
        $this->controller = new $controllerClass;

        if ($this->debug == 1) {
            echo "Se instancia el controlador: '" . $controllerClass . "'<br>";
        }

        // Verificar si el método solicitado existe en el controlador
        if (isset($url[1])) {
            if ($this->debug == 1) {
                echo "Si existe: '" . $url[1] . "'<br>";
            }
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
                if ($this->debug == 1) {
                    echo "Método encontrado: " . $this->method . "<br>";
                }
            } else {
                // Redirigir a 404 si el método no existe
                if ($this->debug == 1) {
                    echo "Método no encontrado: $this->method <br>, redirigiendo a 404.<br>";
                    exit();
                }
                $this->logAndRedirect404("Metodo no encontrado", $url[1] ?? 'N/A');
            }
        } elseif (isset($url[2])) {
            if ($this->debug == 1) {
                echo "Si existe el Methodo: '" . $url[2] . "'<br>";
            }
            if (method_exists($this->controller, $url[2])) {
                $this->method = $url[2];
                unset($url[2]);
                if ($this->debug == 1) {
                    echo "Método encontrado: " . $this->method . "<br>";
                }
            } else {
                // Redirigir a 404 si el método no existe
                if ($this->debug == 1) {
                    echo "Método no encontrado: $this->method <br>, redirigiendo a 404.<br>";
                    exit();
                }
                $this->logAndRedirect404("Metodo no encontrado", $url[2] ?? 'N/A');
            }
        }

        // Procesar cualquier parámetro adicional en la URL
        $this->params = $url ? array_values($url) : [];

        if ($this->debug == 1) {
            echo 'Retorno de Parametros:';
            var_dump($this->params); // Imprime los parámetros procesados
            echo '<br>';
        }

        // Llamar al método del controlador con los parámetros
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    // Método para descomponer la URL en partes
    private function parseUrl()
    {
        if ($this->debug == 1) {
            if (empty($_GET['url'])) {
                echo "No trae contenido la URL.";
            } else {
                echo "Detalle de URL: " . $_GET['url'];
            }
        }
        // Asumimos que estamos en producción
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }

    private function logAndRedirect404($error, $detail)
    {
        $timestamp = date("Y-m-d H:i:s");
        error_log("[$timestamp] core/App.php ->$error: " . $detail . PHP_EOL, 3, LOG_FILE);
        if ($this->debug == 1) {
            echo "$error: " . $detail . "<br>";
        }
        require_once "../app/Views/errors/404.php";
        exit();
    }
}

use App\Models\Login_Mdl;
use App\Models\LoginAdmin_Mdl;

// Clase base Controller 
class Controller
{
    protected $debug = 0;

    // Método para cargar vistas
    protected function view($view, $data = [])
    {
        // Generar la ruta de la vista
        $viewPath = "../app/Views/" . rtrim($view, '/') . ".php";
        if ($this->debug == 1) {
            echo '<br>Ruta de Vista:' . $viewPath . '<br>';
        }

        if (file_exists($viewPath)) {
            extract($data); // Extraer variables
            require_once $viewPath;
        } else {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] core/App.php ->La vista no existe: " . $view . PHP_EOL, 3, LOG_FILE);
            die("La vista no existe.");
        }
    }

    // Método para verificar la autenticación de sesión y el estatus del usuario
    protected function checkSession()
    {
        // Verificar si el usuario está autenticado y que no esté bloqueado
        if (isset($_SESSION['EQXident'])) {
            // Verificar si el usuario sigue activo
            $loginModel = new Login_Mdl();
            $userStatus = $loginModel->verificarEstatusUsuario($_SESSION['EQXident']);

            if (!$userStatus['active']) {
                // Cerrar sesión y redirigir con un mensaje si el usuario está bloqueado
                if ($this->debug == 1) {
                    echo "Se detecto que tu usuario ya esta Bloqueado, te sacaremos del sistema. **Redirección a Index.<br>";
                    exit();
                } else {
                    session_destroy();
                    $_SESSION['error_message'] = 'El usuario ha sido bloqueado.';
                    header('Location: ' . URL_BASE_PROYECT . '/login');
                    exit();
                }
            }
        } else {
            // Redirigir al login si no existe la sesión
            if ($this->debug == 1) {
                echo "No se detecto un inicio de session por lo que seras redireccionado al Index, para que inicies session.<br>";
                exit();
            } else {
                header('Location: ' . URL_BASE_PROYECT . '/login');
                exit();
            }
        }
    }

    // Método para verificar la autenticación de sesión y el estatus del usuario
    protected function checkSessionAdmin()
    {
        // Verificar si el usuario está autenticado y que no esté bloqueado
        if (isset($_SESSION['EQXident'])) {
            // Verificar si el usuario sigue activo
            $loginAdminModel = new LoginAdmin_Mdl();
            $userStatus = $loginAdminModel->verificarEstatusUsuarioAdmin($_SESSION['EQXident']);

            if (!$userStatus['active']) {
                // Cerrar sesión y redirigir con un mensaje si el usuario está bloqueado
                if ($this->debug == 1) {
                    echo "Se detecto que tu usuario ya esta Bloqueado, te sacaremos del sistema. **Redirección a Index.<br>";
                    exit();
                } else {
                    session_destroy();
                    $_SESSION['error_message'] = 'El usuario ha sido bloqueado.';
                    header('Location: ' . URL_BASE_PROYECT . '/login');
                    exit();
                }
            }
        } else {
            // Redirigir al login si no existe la sesión
            if ($this->debug == 1) {
                echo "No se detecto un inicio de session por lo que seras redireccionado al Index, para que inicies session.<br>";
                exit();
            } else {
                header('Location: ' . URL_BASE_PROYECT . '/login');
                exit();
            }
        }
    }
}
