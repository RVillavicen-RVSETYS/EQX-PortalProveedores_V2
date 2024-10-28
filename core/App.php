<?php

namespace Core;

class App
{
    protected $controller = 'Login';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        // Procesar la URL
        $url = $this->parseUrl();

        var_dump($url); // Imprime la URL procesada
        
        // Verificar si el controlador solicitado existe
        if ($url && file_exists("../app/controllers/" . ucfirst($url[0]) . ".php")) {
            echo '<br> Si existe el controlador es:'."../app/controllers/" . ucfirst($url[0]) . ".php";
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
            echo '<br> thisController='.$this->controller;
        } else {
            // Redirige a la página de error 404 si el controlador no existe
            echo 'Aqui ya valio no existe';
            require_once "../app/views/errors/404.php";
            exit();
        }

        // Instancia el controlador
        require_once "../app/controllers/" . $this->controller . ".php";
        # $this->controller = new $this->controller;
        $this->controller = new ("App\Controllers\\" . $this->controller);

        // Verifica si el método solicitado existe en el controlador
        if (isset($url[1])) {
            echo '<br> Si existe el metodo';
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                echo '<br> No existe el metodo';
                // Redirige a la página de error 404 si el método no existe
                require_once "../app/views/errors/404.php";
                exit();
            }
        }

        // Procesar los parámetros restantes
        $this->params = $url ? array_values($url) : [];

        // Llamar al método del controlador con los parámetros
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl()
{
    // Ajuste para entorno local en subcarpeta
    if (strpos($_SERVER['REQUEST_URI'], '/EQX-PortalProveedores_V2/public/') !== false) {
        // Remover la parte '/EQX-PortalProveedores_V2/public/' para obtener solo la ruta relativa
        $url = str_replace('/EQX-PortalProveedores_V2/public/', '', $_SERVER['REQUEST_URI']);
        return explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
    }

    // En entorno de producción o cuando no está en subcarpeta
    if (isset($_GET['url'])) {
        return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
    }
    return [];
}

    // Función para descomponer la URL en partes
    private function parseUrlANt()
    {
        // Si estás en entorno local y usando la carpeta public
        if (strpos($_SERVER['REQUEST_URI'], '/public/') !== false) {

            // Extrae la parte de la URL que sigue a /public/
            $url = str_replace('/public/', '', $_SERVER['REQUEST_URI']);
            return explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        }

        // Para producción o cuando no está en la carpeta public
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
    private function parseUrlProductivo()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
