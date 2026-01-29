# Guía de Integración: API para Notificación de Notas de Crédito

## Introducción

Este documento describe el proceso de integración para notificar a un sistema externo (API) sobre el registro de nuevas Notas de Crédito (NC) asociadas a una factura.

La lógica se implementó en `SubirFacturaController.php`, extendiendo la funcionalidad existente que procesa las facturas y sus notas de crédito.

## Archivo Modificado

- `app/globals/controllers/SubirFacturaController.php`

## Contexto de la Implementación

Dentro de `SubirFacturaController`, la función `procesarNotasCreditoConFactura` es la encargada de validar y registrar las notas de crédito. Una vez que todas las notas de crédito se han registrado exitosamente en la base de datos, se debe notificar a una API externa.

## Cambios Realizados

### 1. Importación del Controlador de la API

Para poder utilizar el controlador que se comunica con la API, se añadió la siguiente línea al inicio del archivo:

```php
use App\Globals\Services\Api\SilmeApi\NotificarNotaCreditoController;
```

### 2. Llamada a la API

Dentro de la función `procesarNotasCreditoConFactura`, después de que todas las notas de crédito han sido procesadas y registradas, se implementó la llamada a la API.

El siguiente bloque de código fue añadido para realizar esta tarea:

```php
// 4. Integración con API Externa (Solo si todo fue exitoso)
if ($todosExitosos && !empty($idsNotasRegistradas)) {
    $datosApi = [
        'idCompra'   => $idCompra,       // ID de la Factura Padre
        'idsAcuseNC' => $idsNotasRegistradas // Array de IDs de las NC registradas (cfdi_notasCreditos)
    ];

    // Llamar a la API para notificar
    $apiController = new NotificarNotaCreditoController();
    $apiResponse = $apiController->notificarAcuses($datosApi);

    if ($this->debug == 1) {
        echo "<br>--- Respuesta de la API de Notificación: ---<br>";
        print_r($apiResponse);
    }
    
    // Si la API falla, solo se registra en el log. El proceso principal se considera exitoso.
    if (!$apiResponse['success']) {
        $timestamp = date("Y-m-d H:i:s");
        // Usamos LOG_FILE_EXCEPCIONES que ya existe en constantes.php para registrar errores de API
        $logMessage = "[$timestamp] app/Globals/Controllers/SubirFacturaController.php -> Error al notificar NC a API externa para idCompra $idCompra: " . $apiResponse['message'] . PHP_EOL;
        error_log($logMessage, 3, LOG_FILE_EXCEPCIONES); 
    }
}
```

#### Explicación del Código:

1.  **Verificación de Éxito:** La llamada a la API solo se realiza si todas las notas de crédito (`$todosExitosos`) se registraron correctamente y si hay IDs de notas de crédito para notificar (`!empty($idsNotasRegistradas)`).
2.  **Preparación de Datos:** Se crea un array `$datosApi` que contiene:
    *   `idCompra`: El ID de la factura a la que están asociadas las notas de crédito.
    *   `idsAcuseNC`: Un array con los IDs de todas las notas de crédito registradas.
3.  **Instancia y Llamada:**
    *   Se crea una nueva instancia de `NotificarNotaCreditoController`.
    *   Se invoca al método `notificarAcuses()`, pasándole los datos preparados.
4.  **Manejo de Errores:** La respuesta de la API se verifica. Si la notificación falla (`!$apiResponse['success']`), el error se registra en el archivo de log definido por `LOG_FILE_EXCEPCIONES`. **Importante:** Este fallo no detiene el flujo ni revierte la operación de guardado de la factura y notas de crédito. El proceso se considera exitoso de cara al usuario, y el error de notificación queda registrado para revisión interna.

## Código de Referencia

### Controlador de la API

Este es el controlador que encapsula la lógica para comunicarse con la API externa.

`app/globals/Services/Api/SilmeApi/NotificarNotaCreditoController.php`
```php
<?php

namespace App\Globals\Services\Api\SilmeApi;

use Core\Controller;

class NotificarNotaCreditoController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Iniciando NotificarNotaCreditoController...</h2>";
        }
    }

    public function notificarAcuses($datos)
    {
        // 1. Validaciones básicas
        if (empty($datos['idCompra']) || empty($datos['idsAcuseNC'])) {
            return ['success' => false, 'message' => 'Faltan datos para la notificación API (idCompra o idsAcuseNC).'];
        }

        // 2. Preparar Payload
        $payload = json_encode($datos);
        
        // 3. Configurar Credenciales y Endpoint
        $apiKey = defined('API_KEY') ? API_KEY : '';
        $secretKey = defined('SECRET_KEY') ? SECRET_KEY : '';
        
        $urlEndpoint = 'https://localhost/silmeagropvt/funciones/APIProveedores/recibeNotaCredito.php'; // Reemplazar con la URL real de la API externa

        if (empty($apiKey) || empty($secretKey)) {
             // Si faltan credenciales, retornamos error (o logueamos warning si es opcional)
             $msg = "Error de configuración: API_KEY o SECRET_KEY no definidas.";
             if($this->debug) echo $msg;
             return ['success' => false, 'message' => $msg];
        }

        // 4. Generar Headers de Seguridad
        // Generamos la firma HMAC SHA256 usando el payload y el timestamp
        $timestamp = (string)time();
        $signature = hash_hmac('sha256', $payload . $timestamp, $secretKey);

        $headers = [
            'Content-Type: application/json',
            'X-API-KEY: ' . $apiKey,
            'X-TIMESTAMP: ' . $timestamp,
            'X-SIGNATURE: ' . $signature
        ];

        // 5. Enviar Petición cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout de 15 segundos

        if ($this->debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);

        // 6. Procesar Respuesta
        if ($response === false) {
            $msg = "Error de conexión con API Externa: $curlError";
            if($this->debug) echo "<br>cURL Error: " . $msg;
            return ['success' => false, 'message' => $msg];
        }

        // Consideramos éxito los códigos 200-299
        if ($httpCode >= 200 && $httpCode < 300) {
            $decodedResponse = json_decode($response, true);
            return [
                'success' => true, 
                'message' => 'Notificación enviada con éxito.',
                'api_response' => $decodedResponse
            ];
        } else {
            // Manejo de errores devueltos por la API externa
            $msg = "La API externa rechazó la solicitud. HTTP Code: $httpCode. Respuesta: $response";
            if($this->debug) echo "<br>" . $msg;
            return ['success' => false, 'message' => "La API externa retornó un error ($httpCode)."];
        }
    }
}
```
