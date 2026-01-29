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

        header('Content-Type: application/json');

        // 1. Solo permitir POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                "success" => false,
                "message" => "Método no permitido"
            ]);
            exit;
        }

        // 2. Validar API Key
        $headers = array_change_key_case(getallheaders(), CASE_UPPER);

        if (!isset($headers['X-API-KEY']) || $headers['X-API-KEY'] !== API_KEY) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Error De Autenticación"
            ]);
            exit;
        }

        // 3. Leer JSON recibido
        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "JSON inválido"
            ]);
            exit;
        }

        // 4️. Validar estructura principal
        if (empty($data['pagos']) || !is_array($data['pagos'])) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Estructura de datos inválida'
            ]);
            return;
        }

        // 5. Validar cada pago
        foreach ($data['pagos'] as $index => $pago) {
            if (
                empty($pago['IdPagoDet']) ||
                !isset($pago['MontoPagado']) ||
                empty($pago['FechaPago'])
            ) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => "Pago inválido en posición {$index}"
                ]);
                return;
            }
        }

        // 6. Mandar al modelo
        $registrarPagoModel = new RegistrarPago_Mdl();
        $resultado = $registrarPagoModel->insertaPagos($data['pagos']);

        // 7. Respuesta estándar
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Pagos recibidos correctamente',
            'procesados' => count($data['pagos']),
            'response' => $resultado
        ]);
    }
}
