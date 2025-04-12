<?php

namespace App\Globals\Services\File\ZipService;

use Core\Controller;
use App\Globals\Controllers\DocumentosController;

class CrearZipController extends Controller
{

    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de services\api\SilmeApi\CrearZipController.php.php.</h2>";
        }
    }

    public function crearZipFacturas(array $rutasArchivos, string $nombreZip = 'archivos.zip')
    {
        ob_start();
        $zip = new \ZipArchive();

        $rutaDirectorio = (__DIR__ . '/../../../../../Documentos/TEMP/zips/');
        // Crear el directorio si no existe
        if (!file_exists($rutaDirectorio)) {
            mkdir($rutaDirectorio, 0777, true);
            echo "Directorio creado: $rutaDirectorio<br>";
        }

        $zipFileName = $rutaDirectorio . $nombreZip;

        if ($zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
            error_log("No se puede abrir <$zipFileName>");
            ob_end_clean();
            exit("No se puede abrir <$zipFileName>\n");
        }

        // Agregamos los archivos
        foreach ($rutasArchivos as $archivo) {
            $ruta = $archivo['ruta'];
            $nombre = $archivo['nombre'];

            if (file_exists($ruta)) {
                $zip->addFile($ruta, $nombre); // Aquí usamos el nombre personalizado
            }
        }

        $zip->close();

        $Ctrl_documentos = new DocumentosController();
        $rutaBuena = 'TEMP/zips/' . $nombreZip;
        $zipFileName = $Ctrl_documentos->generadorDeRutas(base64_encode($rutaBuena), 'zip');

        if (file_exists($zipFileName)) {
            // Forzar la descarga del archivo ZIP
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $nombreZip);
            header('Content-Length: ' . filesize($zipFileName));
            readfile($zipFileName);

            // Eliminar el archivo ZIP después de la descarga
            unlink($zipFileName);
        } else {
            // Enviar un mensaje de error si el archivo ZIP no fue creado
            exit("Error: No se pudo crear el archivo ZIP.");
        }

        // Limpiar cualquier salida pendiente
        ob_end_flush();
    }
}
