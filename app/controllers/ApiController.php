<?php

namespace App\Controllers;

use Core\Controller;

use App\Globals\Services\Api\SilmeApi\RegistrarPagoController;

class ApiController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de controllers\ApiController.php.</h2>";
        }
    }

    public function registrarPago()
    {
        $registrarPago = new RegistrarPagoController();
        $registrarPago->registraPago();
    }

    public function registrarPagoMultiple(){
        $registrarPago = new RegistrarPagoController();
        $registrarPago->registraPagoMultiple();
    }
}