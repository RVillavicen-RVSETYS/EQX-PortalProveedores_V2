<?php

namespace App\Globals\Controllers\FuncionesBasicas;

use Core\Controller;

class FuncionesBasicasController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de globals\controller\funcionesBasicas\FuncionesBasicasController.php.</h2>";
        }
    }

    public function convertirCompromisoPago($tipoPago, $cantidad)
    {

        $response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];


        if (!empty($tipoPago) && !empty($cantidad)) {
            switch ($tipoPago) {
                case 'DAY':
                    $tipoPago = "Día(s)";
                    break;
                case 'WEEK':
                    $tipoPago = "Semana(s)";
                    break;
                case 'MONTH':
                    $tipoPago = "Mes(es)";
                    break;
                case 'YEAR':
                    $tipoPago = "Año(s)";
                    break;
                default:
                    $tipoPago = "Día(s)";
                    break;
            }

            $cantidad = (empty($cantidad) or $cantidad == '') ? 0 : $cantidad;

            $response['success'] = true;
            $response['message'] = 'Compromiso De Pago Convertido Correctamente';
            $response['data'] = $cantidad . ' - ' . $tipoPago;

            return $response;
        } else {
            $response['success'] = false;
            $response['message'] = 'Error Al Convertir Compromiso De Pago';
            $response['data'] = 'Sin Compromiso De Pago';

            return $response;
        }
    }

    public function calcularFechaDePago($fechaVencimiento) {}
}
