<?php

namespace App\Globals\Controllers;

use Core\Controller;

class DocumentosController extends Controller
{
    protected $debug = 0;
    private $basePath;
    private $basePathTemp;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de globals\\controllers\\DocumentosController.php.</h2>";
        }

        // Configuración de rutas base
        $this->basePath = realpath(__DIR__ . '/../../../Documentos');
        $this->basePathTemp = realpath(__DIR__ . '/../../../Documentos/TEMP');

        // Validar rutas base
        if (empty($this->basePath) || !is_dir($this->basePath)) {
            $errorMsg = 'La ruta base de documentos no es válida o no existe: ' . __DIR__ . '/../../../Documentos';
            $this->logErrorAndExit($errorMsg);
        }

        if (empty($this->basePathTemp) || !is_dir($this->basePathTemp)) {
            $errorMsg = 'La ruta base temporal de documentos no es válida o no existe: ' . __DIR__ . '/../../../Documentos/TEMP';
            $this->logErrorAndExit($errorMsg);
        }

        if ($this->debug == 1) {
            echo "Ruta base configurada: {$this->basePath}<br>";
            echo "Ruta base temporal configurada: {$this->basePathTemp}<br>";
        }
    }

    public function almacenaCFDI($tmpName, $tipoDocto, $idProveedor, $identDocto, $empresa, $extension)
    {
        $response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];
        $this->debug = 0;

        if ($this->debug == 1) {
            echo "<br>Temporal Name Recibido: {$tmpName}<br>";
            echo "Empresa: {$empresa}<br>";
            echo "Tipo de Documento: {$tipoDocto}<br>";
            echo "Extensión de Documento: {$extension}<br>";
            echo "Ident de Documento y de User: {$identDocto} -- {$idProveedor}<br>";
            echo "Valor de basePathTemp: {$this->basePathTemp}<br>";
        }



        // Validar que la ruta base existe
        if (empty($this->basePath) || !is_dir($this->basePath)) {
            $response['message'] = 'La ruta base temporal no está configurada correctamente.';
            if ($this->debug == 1) {
                echo "Ruta basePath inválida o inexistente: {$this->basePath}<br>";
            }
            return $response;
        }

        // Validar que todos los datos requeridos estén presentes
        if (empty($tmpName) || empty($tipoDocto) || empty($idProveedor) || empty($identDocto) || empty($empresa) || empty($extension)) {
            $response['message'] = 'Todos los parámetros son obligatorios: tmp_name, tipoDocto, idProveedor, identDocto, empresa, extension.';
            return $response;
        }

        // Mapeo de tipos de documento a nombres de carpetas
        $tipoDoctoMap = [
            'FACT' => 'Facturas',
            'COMPPAG' => 'ComplementosPagos',
            'ANTIC' => 'Anticipos',
            'COMP' => 'Compensaciones',
            'NOTACRED' => 'NotasCreditos'
        ];

        // Validar si el tipo de documento está en el mapeo
        if (!isset($tipoDoctoMap[$tipoDocto])) {
            $response['message'] = 'El Tipo de Documento no está Definido, Notifica a tu Administrador.';
            return $response;
        }

        // Obtener el nombre del tipo de documento
        $tipoDoctoNombre = $tipoDoctoMap[$tipoDocto];

        // Validar que el archivo temporal exista
        if (!file_exists($tmpName)) {
            $response['message'] = 'El archivo temporal especificado no existe.';
            return $response;
        }

        // Preparar la ruta base
        $currentYear = date('Y'); // Año actual
        $currentYearMonth = date('Y-m'); // Mes actual
        $destinationDir = $this->basePath . DIRECTORY_SEPARATOR . $empresa . DIRECTORY_SEPARATOR . $tipoDoctoNombre . DIRECTORY_SEPARATOR . $currentYear . DIRECTORY_SEPARATOR . $idProveedor . DIRECTORY_SEPARATOR . $currentYearMonth;

        if ($this->debug == 1) {
            echo "Directorio de Destino: {$destinationDir}<br>";
        }

        // Crear directorio si no existe
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0755, true)) {
                $response['message'] = 'No se pudo crear el directorio temporal: ' . $destinationDir;
                return $response;
            }
        }

        // Generar el nombre único del archivo usando tipoDocto directamente
        $dateTime = date('YmdHis'); // Timestamp único
        $fileName = "{$idProveedor}_{$tipoDocto}_{$identDocto}_{$dateTime}.{$extension}";

        if ($this->debug == 1) {
            echo "Nombre del Archivo: {$fileName}<br>";
        }

        // Ruta absoluta y relativa
        $destinationPath = $destinationDir . DIRECTORY_SEPARATOR . $fileName; // Ruta absoluta
        $relativePath = str_replace(realpath(__DIR__ . '/../../../'), '', $destinationPath); // Ruta relativa basada en __DIR__

        if ($this->debug == 1) {
            echo "Ruta Relativa: {$relativePath}<br>";
            echo "Ruta Destino final: {$destinationPath}<br>";
        }

        // Mover el archivo temporal a la ubicación final
        if (!move_uploaded_file($tmpName, $destinationPath)) {
            $response['message'] = 'No se pudo mover el archivo temporal a la ubicación final.';
            return $response;
        }

        // Preparar la respuesta
        $response['success'] = true;
        $response['message'] = 'El archivo se almacenó temporalmente con éxito.';
        $response['data'] = [
            'absolutePath' => $destinationPath,
            'relativePath' => $relativePath,
            'fileName' => $fileName,
            'directory' => $destinationDir,
            'idProveedor' => $idProveedor,
            'tipoDocto' => $tipoDocto,
            'identDocto' => $identDocto
        ];

        return $response;
    }

    /**
     * Maneja errores críticos al inicializar la clase, deteniendo la ejecución con un mensaje claro.
     */
    private function logErrorAndExit($message)
    {
        $timestamp = date("Y-m-d H:i:s");
        error_log("[$timestamp] $message" . PHP_EOL, 3, LOG_FILE);

        if ($this->debug == 1) {
            echo "<strong>Error crítico:</strong> $message<br>";
        }
        exit($message); // Detener la ejecución con un mensaje claro
    }

    /**
     * Retorna un archivo desde la carpeta Documentos con las cabeceras correspondientes.
     */
    public function mostrarDocumento($rutaRelativa, $fileExtension)
    {
        $rutaRelativa = base64_decode($rutaRelativa);
        
        if ($this->debug) {
            echo '<br> Ruta Relativa Codificada: ' . $rutaRelativa . PHP_EOL;
            echo '<br> Extensión del Archivo: ' . $fileExtension . PHP_EOL;
        }

        // Tipos MIME aceptados
        $tiposAceptados = [
            'pdf' => 'application/pdf',  // PDF
            'xml' => ['application/xml', 'text/xml'], // XML
            'jpeg' => 'image/jpeg',      // Imágenes JPEG
            'png' => 'image/png',        // Imágenes PNG
        ];

        // Validar si la extensión está permitida
        $fileExtension = strtolower($fileExtension); // Normalizar la extensión esperada a minúsculas
        if (!array_key_exists($fileExtension, $tiposAceptados)) {
            http_response_code(400); // Bad Request
            echo '<br> Error: La extensión no está permitida.' . PHP_EOL;
            if ($this->debug) {
                echo '<br> Extensión recibida: ' . $fileExtension . PHP_EOL;
                echo '<br> Extensiones permitidas: ' . implode(', ', array_keys($tiposAceptados)) . PHP_EOL;
            }

            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app\globals\controllers\DocumentosController ->(mostrarDocumento)Se solicito extensión no permitida: $fileExtension" . PHP_EOL, 3, LOG_FILE);
            exit;
        }

        // Sanitizar la ruta para evitar accesos indebidos
        $rutaSanitizada = str_replace(['..', './', '../'], '', $rutaRelativa);
        $rutaSanitizada = str_replace("\Documentos\\", "", $rutaSanitizada);
        if ($this->debug) {
            echo '<br> Ruta Sanitizada:' . $rutaSanitizada . PHP_EOL;
        }

        $rutaAbsoluta = $this->basePath . DIRECTORY_SEPARATOR . $rutaSanitizada;
        if ($this->debug) {
            echo '<br> Ruta Absoluta:' . $rutaAbsoluta . PHP_EOL;
        }
        
        // Verificar si el archivo existe
        if (!file_exists($rutaAbsoluta)) {
            http_response_code(404);
            echo '<br> <span style="color: white;">Error: El archivo solicitado no existe.</span>' . PHP_EOL;
            if ($this->debug) {
                echo '<br> Ruta probada: ' . $rutaAbsoluta . PHP_EOL;
            }
            exit;
        }

        // Obtener la extensión real del archivo y normalizarla a minúsculas
        $extensionReal = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));

        if ($extensionReal !== $fileExtension) {
            http_response_code(400); // Bad Request
            echo '<br> Error: La extensión del archivo no coincide.' . PHP_EOL;
            if ($this->debug) {
                echo '<br> Extensión esperada: ' . $fileExtension . ', Extensión actual: ' . $extensionReal . PHP_EOL;
            }
            exit;
        }

        // Validar el tipo MIME del archivo
        $mimeActual = mime_content_type($rutaAbsoluta);
        if ((is_array($tiposAceptados[$fileExtension]) && !in_array($mimeActual, $tiposAceptados[$fileExtension])) ||
            (!is_array($tiposAceptados[$fileExtension]) && $mimeActual !== $tiposAceptados[$fileExtension])
        ) {
            http_response_code(400); // Bad Request
            echo '<br> Error: El tipo MIME del archivo no es válido.' . PHP_EOL;
            if ($this->debug) {
                echo '<br> Tipos MIME aceptados para .' . $fileExtension . ': ' . (is_array($tiposAceptados[$fileExtension]) ? implode(', ', $tiposAceptados[$fileExtension]) : $tiposAceptados[$fileExtension]) . PHP_EOL;
                echo '<br> Tipo MIME actual: ' . $mimeActual . PHP_EOL;
            }
            exit;
        }

        // Todo está OK, devolver el archivo
        if ($this->debug) {
            echo '<br><br> Todo está OK, el documento existe, la extensión coincide, y el tipo MIME es válido.' . PHP_EOL;
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

    /**
     * Validaciones de un documento PDF o XML antes de subirlo. -NO USAR PARA IMAGENES
     */
    function verificadorDeDocumentoARecibir($fileToValidate, $expectedExtension)
    {
        // Tipos MIME aceptados
        $tiposAceptados = [
            'pdf' => 'application/pdf',  // PDF
            'xml' => ['application/xml', 'text/xml'] // XML
        ];

        // Respuesta inicial
        $response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];

        // Normalizar extensión esperada
        $expectedExtension = strtolower($expectedExtension);

        // Validar si la extensión esperada está permitida
        if (!array_key_exists($expectedExtension, $tiposAceptados)) {
            $response['message'] = "La extensión '$expectedExtension' no está permitida.";
            return $response;
        }

        // Validar que $_FILE no tenga errores
        if (!isset($fileToValidate) || $fileToValidate['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Error al cargar el archivo.';
            if (isset($fileToValidate['error'])) {
                $response['message'] .= ' Código de error: ' . $fileToValidate['error'];
            }
            return $response;
        }

        // Validar que el archivo tenga un tamaño mínimo
        $minimumFileSize = 50; // Tamaño mínimo en bytes
        if ($fileToValidate['size'] < $minimumFileSize) {
            $response['message'] = 'El archivo parece estar dañado o es demasiado pequeño.';
            return $response;
        }

        // Validar que la extensión del archivo coincida con la esperada
        $actualExtension = strtolower(pathinfo($fileToValidate['name'], PATHINFO_EXTENSION));
        if ($actualExtension !== $expectedExtension) {
            $response['message'] = "La extensión del archivo no coincide. Se esperaba '$expectedExtension', pero se recibió '$actualExtension'.";
            return $response;
        }

        // Validar el tipo MIME del archivo
        $actualMimeType = mime_content_type($fileToValidate['tmp_name']);
        $expectedMimeType = $tiposAceptados[$expectedExtension];
        if ((is_array($expectedMimeType) && !in_array($actualMimeType, $expectedMimeType)) ||
            (!is_array($expectedMimeType) && $actualMimeType !== $expectedMimeType)
        ) {
            $response['message'] = "El tipo MIME del archivo no es válido. Se esperaba '$expectedMimeType', pero se recibió '$actualMimeType'.";
            return $response;
        }

        // Validar que el archivo sea legible
        if (!is_readable($fileToValidate['tmp_name'])) {
            $response['message'] = 'El archivo no es legible.';
            return $response;
        }

        // Si todo está bien, preparar la respuesta
        $response['success'] = true;
        $response['message'] = 'El archivo es válido.';
        $response['data'] = [
            'name' => $fileToValidate['name'],
            'type' => $fileToValidate['type'],
            'tmp_name' => $fileToValidate['tmp_name'],
            'error' => $fileToValidate['error'],
            'size' => $fileToValidate['size'],
            'extension' => $actualExtension
        ];

        return $response;
    }

}
