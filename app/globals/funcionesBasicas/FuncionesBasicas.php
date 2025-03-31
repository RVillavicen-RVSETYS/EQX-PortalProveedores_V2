<?php

namespace App\Globals\FuncionesBasicas;

use Core\Controller;

class FuncionesBasicas extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de globals\controller\funcionesBasicas\FuncionesBasicasController.php.</h2>";
        }
    }

    public function convertirCompromisoPago($valor)
    {
        $response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];

        if (empty($valor)) {
            $response['data']['cantidad'] = '';
            $response['data']['tiempo'] = '';
            $response['success'] = true;
        } else {
            if (strpos($valor, 'DAY-') !== false) {
                $cantidad = str_replace('DAY-', '', $valor);
                $response['data']['cantidad'] = (int)$cantidad;
                $response['data']['tiempo'] = $cantidad > 1 ? 'Días' : 'Día';
                $response['success'] = true;
            } elseif (strpos($valor, 'MONTH-') !== false) {
                $cantidad = str_replace('MONTH-', '', $valor);
                $response['data']['cantidad'] = (int)$cantidad;
                $response['data']['tiempo'] = $cantidad > 1 ? 'Meses' : 'Mes';
                $response['success'] = true;
            } elseif (strpos($valor, 'WEEK-') !== false) {
                $cantidad = (int)str_replace('WEEK-', '', $valor);
                $response['data']['cantidad'] = $cantidad;
                $response['data']['tiempo'] = $cantidad > 1 ? 'Semanas' : 'Semana';
                $response['success'] = true;
            } elseif (strpos($valor, 'YEAR-') !== false) {
                $cantidad = (int)str_replace('YEAR-', '', $valor);
                $response['data']['cantidad'] = $cantidad;
                $response['data']['tiempo'] = $cantidad > 1 ? 'Años' : 'Año';
                $response['success'] = true;
            } else {
                $response['message'] = 'Formato de valor no reconocido';
                $response['success'] = false;
            }
        }

        return $response;
    }

    public function calcularFechaDePago($fechaVencimiento) {}
}
