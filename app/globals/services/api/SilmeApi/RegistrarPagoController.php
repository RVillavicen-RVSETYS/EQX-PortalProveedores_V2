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
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de services\api\SilmeApi\RegistrarPagoController/registraPago.php.</h2>";
        }

        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        /* Obtener los headers */
        $headers = getallheaders();
        $ApiKey = $headers['ApiKey'] ?? '';
        $SecretKey = $headers['SecretKey'] ?? '';

        /* Verificar Credenciales */
        if ($ApiKey !== API_KEY || $SecretKey !== SECRET_KEY) {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Las credenciales son incorrectas.'
            ]);
            exit;
        }

        if ($this->debug == 1) {
            http_response_code(HTTP_GOOD_REQUEST);
            echo json_encode([
                'code' => HTTP_GOOD_REQUEST,
                'status' => 'success',
                'message' => 'Las credenciales son correctas.'
            ]);
        }

        /* Recibir y validar JSON */
        $inputData = json_decode(file_get_contents("php://input"), true);

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

        /* Insertar el pago */
        $registrarPagoModel = new RegistrarPago_Mdl();
        $registraPagos = $registrarPagoModel->insertaPagos($inputData);

        if ($registraPagos['success'] == true) {
            http_response_code(HTTP_GOOD_REQUEST);

            echo json_encode([
                'code' => HTTP_GOOD_REQUEST,
                'status' => 'success',
                'message' => 'Datos Insertados Correctamente.'
            ]);
            exit;
        } else {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Error Al Insertar Datos.'
            ]);
            exit;
        }
    }

    /* Función para validar cada pago */
    private function validarPago($pago)
    {
        return isset(
            $pago['IdPago'],
            $pago['IdDetPago'],
            $pago['OrdenCompra'],
            $pago['HojaEntrada'],
            $pago['MontoPago'],
            $pago['SaldoInsoluto'],
            $pago['Moneda'],
            $pago['FormaPago'],
            $pago['FechaPago'],
            $pago['IdAcuse'] // Nuevo campo requerido
        )
            && is_numeric($pago['IdPago'])
            && is_numeric($pago['IdDetPago'])
            && !empty($pago['OrdenCompra'])
            && !empty($pago['HojaEntrada'])
            && is_numeric($pago['MontoPago'])
            && is_numeric($pago['SaldoInsoluto']) // ✅ permite 0
            && !empty($pago['Moneda'])
            && is_numeric($pago['FormaPago'])
            && is_numeric($pago['IdAcuse']) // ✅ validar numérico
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $pago['FechaPago']);
    }


    public function registraPago()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de services\api\SilmeApi\RegistrarPagoController/registraPago.php.</h2>";
        }

        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        /* Obtener los headers */
        $headers = getallheaders();

        $ApiKey = $headers['ApiKey'] ?? '';
        $SecretKey = $headers['SecretKey'] ?? '';

        /* Verificar Credenciales */
        if ($ApiKey !== API_KEY || $SecretKey !== SECRET_KEY) {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Las credenciales son incorrectas.'
            ]);
            exit;
        }

        if ($this->debug == 1) {
            echo json_encode([
                'code' => HTTP_GOOD_REQUEST,
                'status' => 'success',
                'message' => 'Las credenciales son correctas.'
            ]);
        }

        /* Recibir y validar JSON */
        $inputData = json_decode(file_get_contents("php://input"), true);

        if (!is_array($inputData)) {
            http_response_code(400);
            echo json_encode([
                'code' => 400,
                'status' => 'error',
                'message' => 'El formato de datos enviado no es válido.'
            ]);
            exit;
        }

        $campos = [
            'IdPago',
            'IdDetPago',
            'OrdenCompra',
            'HojaEntrada',
            'MontoPago',
            'SaldoInsoluto',
            'Moneda',
            'FormaPago',
            'FechaPago'
        ];

        $datos = [];
        foreach ($campos as $campo) {
            $datos[$campo] = $inputData[$campo] ?? $_POST[$campo] ?? '';
        }

        // Verificar si algún campo está vacío
        if (in_array('', $datos, true)) {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Faltan datos por enviar.'
            ]);
            exit;
        }

        if ($this->debug == 1) {
            echo json_encode([
                'code' => HTTP_GOOD_REQUEST,
                'status' => 'success',
                'message' => 'Datos recibidos correctamente.'
            ]);
        }

        /* Insertar el pago */
        $registrarPagoModel = new RegistrarPago_Mdl();
        $verificarPago = $registrarPagoModel->insertaPago(...array_values($datos));

        /* Responder según el resultado */
        if (isset($verificarPago['success']) && $verificarPago['success'] == true) {
            http_response_code(HTTP_GOOD_REQUEST);
            echo json_encode([
                'code' => HTTP_GOOD_REQUEST,
                'status' => 'success',
                'message' => $verificarPago['data']
            ]);
        } else {
            http_response_code(HTTP_BAD_REQUEST);
            echo json_encode([
                'code' => HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => $verificarPago['message'] ?? 'Error desconocido al registrar el pago.'
            ]);
        }
    }
}
