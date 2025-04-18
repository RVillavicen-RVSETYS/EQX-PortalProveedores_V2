<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\DatosCFDIs\CatalogosCFDIs_Mdl;

class CfdisController extends Controller
{
    protected $debug = 0;

    public function leerCfdiXML($xmlPath, $tipoCfdi)
    {
        //$this->debug = 1;
        //Tipos de CFDI: Ingreso, Egreso, Traslado, Nomina, Pago, RecepcionPagos o Retenciones
        // Definir el arreglo de tipos de CFDI
        $tiposCfdi = [
            'Ingreso' => 'I',
            'Egreso' => 'E',
            'Traslado' => 'T',
            'Nomina' => 'N',
            'Pago' => 'P'
        ];

        // Validar que el tipo de comprobante que vamos a recibir este en el arreglo aceptado
        if (!isset($tiposCfdi[$tipoCfdi])) {
            return ['success' => false, 'message' => "El tipo de CFDI '$tipoCfdi' no es válido."];
        }

        // Validar que los parámetros sean correctos
        if (empty($xmlPath) || empty($tipoCfdi)) {
            return ['success' => false, 'message' => 'El archivo XML y el tipo de CFDI son obligatorios.'];
        }

        // 1. Extraer la versión del CFDI desde el XML (cabecera)
        $dataXML = $this->extraerCabeceraDesdeXML($xmlPath);
        if ($this->debug == 1) {
            echo '<br><br>Datos de cabecera del XML: ' . PHP_EOL;
            var_dump($dataXML);
            echo '**************************';
        }
        if ($dataXML['success'] == false) {
            return ['success' => false, 'message' => 'No pudimos leer la cabecera del XML: '.$dataXML['message']];
        }

        $versionCFDI = $dataXML['data']['Comprobante']['Version'];
        if (empty($versionCFDI)) {
            return ['success' => false, 'message' => 'No se pudo determinar la versión del CFDI desde el archivo XML.'];
        }
        
        $tipoComprobanteXML = $dataXML['data']['Comprobante']['TipoDeComprobante'] ?? '';
        if ($tipoComprobanteXML !== $tiposCfdi[$tipoCfdi]) {
            return [
            'success' => false,
            'message' => "El tipo de comprobante en el XML ('$tipoComprobanteXML') no coincide con el tipo de CFDI esperado ('$tiposCfdi[$tipoCfdi]')."
            ];
        }

        // 2. Consultar la tabla 'versionescfdi' para obtener configuraciones
        $CatalogCfdi = new CatalogosCFDIs_Mdl();
        $versiones = $CatalogCfdi->obtenerVersionesCFDI();
        if (!isset($versiones[$versionCFDI])) {
            return ['success' => false, 'message' => "La versión $versionCFDI no está permitida."];
        }

        $configuracion = $versiones[$versionCFDI];
        $fechaLimite = $configuracion['fechaLimite'];
        $funcionActiva = $configuracion['funcionActiva'];
        $claseFuncion = 'cfdis'.$funcionActiva;

        // 3. Validar la fecha límite del CFDI
        $fechaTimbrado = $dataXML['data']['Comprobante']['Fecha'];
        $fechaTimbradoNormalizada = date('Y-m-d', strtotime($fechaTimbrado));
        if (strtotime($fechaTimbradoNormalizada) > strtotime($fechaLimite)) {
            return [
                'success' => false,
                'message' => "La versión $versionCFDI ya no es válida después de $fechaLimite."
            ];
        }

        // 4. Cargar el archivo y clase de la versión correspondiente
        $archivoVersion = __DIR__ . '\Cfdis\\'.$claseFuncion.'.php';
        if ($this->debug == 1) {
            echo '<br><br>URL de la Clase que leera el Archivo: ' . $archivoVersion .'<br>';
        }
        if (!file_exists($archivoVersion)) {
            return [
                'success' => false, 
                'message' => "El archivo para la versión $claseFuncion no existe.",
                'debug' => 'Archivo: ' . $archivoVersion
            ];
        }

        require_once $archivoVersion;
        if (!class_exists($claseFuncion)) {
            return ['success' => false, 'message' => "La clase $claseFuncion no está definida en el archivo correspondiente."];
        }

        // 5. Instanciar la clase y ejecutar el método correspondiente
        $cfdiVersion = new $claseFuncion();
        $metodoLectura = "leerCfdi_" . ucfirst(strtolower($tipoCfdi)); // Ejemplo: leerCfdi_Ingreso
        if (!method_exists($cfdiVersion, $metodoLectura)) {
            return ['success' => false, 'message' => "El método $metodoLectura no está definido en la clase $claseFuncion."];
        }

        // Ejecutar el método y retornar el resultado
        return $cfdiVersion->$metodoLectura($xmlPath, $funcionActiva);
    }

    public function extraerCabeceraDesdeXML($xmlPath)
    {
        $response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];

        // Verificar que la ruta del archivo no esté vacía
        if (empty($xmlPath)) {
            $response['message'] = 'La ruta del archivo XML no puede estar vacía.';
            return $response;
        }

        // Verificar que el archivo exista y sea legible
        if (!file_exists($xmlPath) || !is_readable($xmlPath)) {
            $response['message'] = 'El archivo XML no existe o no es legible: ' . $xmlPath;
            return $response;
        }

        try {
            // Cargar el contenido del archivo XML
            $xmlContent = simplexml_load_file($xmlPath);
            if ($xmlContent === false) {
                $response['message'] = 'No se pudo cargar el archivo XML. Verifica que el formato sea válido.';
                return $response;
            }

            // Convertir el XML a JSON y luego a arreglo para facilitar la manipulación
            $xmlArray = json_decode(json_encode($xmlContent), true);

            // Procesar la información del CFDI
            $data = [];

            // Datos generales del comprobante
            $comprobante = $xmlArray['@attributes'] ?? [];
            $data['Comprobante'] = $comprobante;

            // Resultados finales
            $response['success'] = true;
            $response['message'] = 'XML leído correctamente.';
            $response['data'] = $data;
            
        } catch (\Exception $e) {
            // Manejo de errores
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/CFDI_Mdl.php ->Error al leer CFDI XML: " . $e->getMessage(), 3, LOG_FILE_BD);
            $response['message'] = 'Error al leer el archivo XML: ' . $e->getMessage();

            if (self::$debug) {
                echo "Error al leer el archivo XML: " . $e->getMessage();
            }
        }

        return $response;
    }
}
