<?php

class Idioma {
    private $textos = [];

    public function __construct($idioma = 'es_mx') {
        $archivo = __DIR__ . "/idiomas/{$idioma}.php";
        if (file_exists($archivo)) {
            $this->textos = include $archivo;
        } else {
            error_log("Archivo de idioma no encontrado: {$archivo}");
        }
    }

    public function obtenerTexto($clave) {
        return $this->textos[$clave] ?? "Texto no disponible";
    }
}
