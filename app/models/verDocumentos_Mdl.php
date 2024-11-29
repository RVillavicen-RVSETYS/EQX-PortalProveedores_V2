<?php

namespace App\Models;

class verDocumentos_Mdl
{
    private $basePath;
    private static $debug = 0;

    public function __construct()
    {
        // Ruta base donde están los documentos
        $this->basePath = realpath(__DIR__ . '/../../Documentos');
    }

    /**
     * Retorna un PDF desde la carpeta Documentos.
     *
     * @param string $rutaRelativa Ruta relativa al archivo PDF (ejemplo: "Facturas/137/2024-11/93130_WE_E002791.pdf").
     * @return void
     */
    public function obtenerPDF($rutaRelativa)
    {
        $this->retornarArchivo($rutaRelativa, 'pdf');
    }

    /**
     * Retorna un XML desde la carpeta Documentos.
     *
     * @param string $rutaRelativa Ruta relativa al archivo XML (ejemplo: "Facturas/137/2024-11/93130_WE_E002791.xml").
     * @return void
     */
    public function obtenerXML($rutaRelativa)
    {
        $this->retornarArchivo($rutaRelativa, 'xml');
    }

    /**
     * Retorna un archivo desde la carpeta Documentos con las cabeceras correspondientes.
     *
     * @param string $rutaRelativa Ruta relativa al archivo.
     * @param string $tipoMime Tipo MIME esperado (por ejemplo, 'application/pdf' o 'application/xml').
     * @return void
     */
    private function retornarArchivo($rutaRelativa, $fileExtension)
    {
        // Sanitizar la ruta para evitar accesos indebidos
        $rutaSanitizada = str_replace(['..', './', '../'], '', $rutaRelativa);
        if (self::$debug) {
            echo 'Ruta Sanitizada:' . $rutaSanitizada . PHP_EOL;
        }

        $rutaAbsoluta = $this->basePath . DIRECTORY_SEPARATOR . $rutaSanitizada;
        if (self::$debug) {
            echo 'Ruta Absoluta:' . $rutaAbsoluta . PHP_EOL;
        }

        // Verificar si el archivo existe
        if (!file_exists($rutaAbsoluta)) {
            http_response_code(404);
            echo 'Error: El archivo solicitado no existe.' . PHP_EOL;
            if (self::$debug) {
                echo 'Ruta probada: ' . $rutaAbsoluta . PHP_EOL;
            }
            exit;
        }

        // Obtener la extensión real del archivo y normalizarla a minúsculas
        $extensionReal = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
        $fileExtension = strtolower($fileExtension); // Normalizar la extensión esperada a minúsculas

        if ($extensionReal !== $fileExtension) {
            http_response_code(400); // Bad Request
            echo 'Error: La extensión del archivo no coincide.' . PHP_EOL;
            if (self::$debug) {
                echo 'Extensión esperada: ' . $fileExtension . ', Extensión actual: ' . $extensionReal . PHP_EOL;
            }
            exit;
        }

        // Tipos MIME aceptados
        $tiposAceptados = [
            'pdf' => 'application/pdf',  // PDF
            'xml' => ['application/xml', 'text/xml'], // XML
            'jpeg' => 'image/jpeg',      // Imágenes JPEG
            'png' => 'image/png',        // Imágenes PNG
        ];

        // Validar el tipo MIME del archivo
        $mimeActual = mime_content_type($rutaAbsoluta);
        if (
            !isset($tiposAceptados[$fileExtension]) ||
            (is_array($tiposAceptados[$fileExtension]) && !in_array($mimeActual, $tiposAceptados[$fileExtension])) ||
            (!is_array($tiposAceptados[$fileExtension]) && $mimeActual !== $tiposAceptados[$fileExtension])
        ) {
            http_response_code(400); // Bad Request
            echo 'Error: El tipo MIME del archivo no es válido.' . PHP_EOL;
            if (self::$debug) {
                echo 'Tipos MIME aceptados para .' . $fileExtension . ': ' . (is_array($tiposAceptados[$fileExtension]) ? implode(', ', $tiposAceptados[$fileExtension]) : $tiposAceptados[$fileExtension]) . PHP_EOL;
                echo 'Tipo MIME actual: ' . $mimeActual . PHP_EOL;
            }
            exit;
        }

        // Todo está OK, devolver el archivo
        if (self::$debug) {
            echo 'Todo está OK, el documento existe, la extensión coincide, y el tipo MIME es válido.' . PHP_EOL;
        } else {
            // Enviar cabeceras para la respuesta
            header('Content-Type: ' . $mimeActual);
            header('Content-Disposition: inline; filename="' . basename($rutaAbsoluta) . '"');
            header('Content-Length: ' . filesize($rutaAbsoluta));

            // Leer y devolver el archivo
            readfile($rutaAbsoluta);
        }
        exit;
    }
}
