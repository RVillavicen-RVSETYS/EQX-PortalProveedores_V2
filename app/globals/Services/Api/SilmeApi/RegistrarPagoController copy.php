<?php

namespace App\Globals\Services\Api\SilmeApi;

use Core\Controller;
use App\Globals\Services\Api\SilmeApi\Models\RegistrarPago_Mdl;

class RegistrarPagoController extends Controller
{

    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de services\api\SilmeApi\RegistrarPagoController.php.php.</h2>";
        }
    }

    public function registraPagoMultiple()
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        // Obtener los headers enviados por el cliente
        $headers = getallheaders();
        $apiKey = $headers['X-API-KEY'] ?? '';
        $timestamp = $headers['X-TIMESTAMP'] ?? '';
        $firmaRecibida = $headers['X-SIGNATURE'] ?? '';

        // Validar presencia de headers
        if (empty($apiKey) || empty($timestamp) || empty($firmaRecibida)) {
            http_response_code(HTTP_FORBIDDEN);
            echo json_encode([
                'code' => HTTP_FORBIDDEN,
                'status' => 'error',
                'message' => 'Error De Autenticación.'
            ]);
            exit;
        }

        // Validar API Key
        if ($apiKey !== API_KEY) {
            http_response_code(HTTP_FORBIDDEN);
            echo json_encode([
                'code' => HTTP_FORBIDDEN,
                'status' => 'error',
                'message' => 'API Key no válida.'
            ]);
            exit;
        }

        // Validar que el timestamp sea un número
        if (!ctype_digit($timestamp)) {
            http_response_code(HTTP_FORBIDDEN);
            echo json_encode([
                'code' => HTTP_FORBIDDEN,
                'status' => 'error',
                'message' => 'Timestamp no válido.'
            ]);
            exit;
        }

        // Validar que el timestamp no esté fuera de rango (por ejemplo 5 min)
        if (abs(time() - (int)$timestamp) > API_TIMESTAMP_TOLERANCE) {
            http_response_code(HTTP_FORBIDDEN);
            echo json_encode([
                'code' => HTTP_FORBIDDEN,
                'status' => 'error',
                'message' => 'La solicitud ha expirado.'
            ]);
            exit;
        }

        // Leer cuerpo del request
        $rawPayload = file_get_contents("php://input");

        // Calcular la firma esperada (con el SecretKey conocido)
        $firmaEsperada = hash_hmac('sha256', $rawPayload . $timestamp, SECRET_KEY);

        if (!hash_equals($firmaEsperada, $firmaRecibida)) {
            http_response_code(HTTP_FORBIDDEN);
            echo json_encode([
                'code' => HTTP_FORBIDDEN,
                'status' => 'error',
                'message' => 'Firma no válida.'
            ]);
            exit;
        }

        // Si pasa todo, continuar con la lógica existente...
        $inputData = json_decode($rawPayload, true);

        if (!is_array($inputData)) {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'El formato de entrada no es válido. Debe ser un array de objetos JSON.'
            ]);
            exit;
        }

        foreach ($inputData as $pago) {
            if (!$this->validarPago($pago)) {
                http_response_code(HTTP_BAD_REQUEST);
                echo json_encode([
                    'code' => HTTP_BAD_REQUEST,
                    'status' => 'error',
                    'message' => 'Faltan datos o hay datos inválidos en la petición.'
                ]);
                exit;
            }
        }

        $registrarPagoModel = new RegistrarPago_Mdl();
        $registraPagos = $registrarPagoModel->insertaPagos($inputData);

        if ($registraPagos['success'] == true) {
            http_response_code(HTTP_OK);
            echo json_encode([
                'code' => HTTP_OK,
                'status' => 'success',
                'message' => 'Datos Insertados Correctamente.'
            ]);
        } else {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Error Al Insertar Datos.'
            ]);
        }
    }

    /* Función para validar cada pago */
    private function validarPago($pago)
    {
        if (
            !isset(
                $pago['IdPagoDet'],
                $pago['IdAcuse'],
                $pago['OC'],
                $pago['HES'],
                $pago['MontoPagado'],
                $pago['SaldoInsoluto'],
                $pago['Moneda'],
                $pago['TipoCambio'],
                $pago['FormaPago'],
                $pago['FechaPago']
            )
        ) {
            return false;
        }
        if (!is_numeric($pago['IdPagoDet']) || (int)$pago['IdPagoDet'] <= 0) return false;
        if (!is_numeric($pago['IdAcuse']) || (int)$pago['IdAcuse'] <= 0) return false;
        if (empty($pago['OC']) || !is_string($pago['OC'])) return false;
        if (empty($pago['HES']) || !is_string($pago['HES'])) return false;
        if (!is_numeric($pago['MontoPagado']) || $pago['MontoPagado'] < 0) return false;
        if (!is_numeric($pago['SaldoInsoluto']) || $pago['SaldoInsoluto'] < 0) return false;
        if (empty($pago['Moneda']) || !is_string($pago['Moneda'])) return false;
        if (!is_numeric($pago['TipoCambio']) || $pago['TipoCambio'] < 0) return false;
        if (!is_numeric($pago['FormaPago']) || (int)$pago['FormaPago'] <= 0) return false;

        // Validar formato y validez de fecha
        $fecha = $pago['FechaPago'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) return false;
        $partes = explode('-', $fecha);
        if (!checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) return false;

        return true;
    }
}
