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

    public function notificarAcuses(array $datos)
    {
        // 1. Validaciones básicas
        if (empty($datos['folioOC']) || empty($datos['notasCredito']) || !is_array($datos['notasCredito'])) {
            return [
                'success' => false,
                'message' => 'Faltan datos para la notificación API (folioOC o notasCredito).'
            ];
        }

        // 2. Validar estructura interna de notasCredito
        foreach ($datos['notasCredito'] as $nc) {
            if (empty($nc['idNC_Silme'])) {
                return [
                    'success' => false,
                    'message' => 'Estructura inválida en notasCredito.'
                ];
            }
        }

        // 3. Prepara Curl
        $url = 'http://localhost/silmeagropvt/funciones/APIProveedores/recibeNotasCredito.php';
        $payload = json_encode($datos);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . API_KEY,
            'Content-Length: ' . strlen($payload)
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $payload,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_TIMEOUT         => 20,
            CURLOPT_SSL_VERIFYPEER  => false, // SOLO si usas certificados propios
            CURLOPT_SSL_VERIFYHOST  => false
        ]);

        // 4. Ejecuta Curl
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'message' => 'Error CURL: ' . $error
            ];
        }

        curl_close($ch);

        // 5. Procesa respuesta
        $responseData = json_decode($response, true);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Error en respuesta de SILME',
                'httpCode' => $httpCode,
                'response' => $responseData ?? $response
            ];
        }

        if (empty($responseData['success'])) {
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'SILME respondió con error',
                'response' => $responseData
            ];
        }

        return [
            'success' => true,
            'message' => 'Notas de crédito notificadas correctamente a SILME',
            'response' => $responseData
        ];
    }
}
