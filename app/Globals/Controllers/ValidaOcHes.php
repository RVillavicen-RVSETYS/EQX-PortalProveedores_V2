<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\Compras\Compras_Mdl;

class ValidaOcHes extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            echo "<h2>Ya estamos dentro de App\Globals\Controllers\ValidaOcHes.php.</h2>";
        }
    }

    public function validaOcHes($ordenCompra, $noProveedor)
    {
        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);
        $MDL_anticipos = new Anticipos_Mdl();
        $filtros['folioCompra'] = $ordenCompra;
        $verificaDebeAnticipo = $MDL_anticipos->verificaAnticipoDeOrdenCompra($filtros);

        if ($validOrdenCompra['success']) {
            $Message = $validOrdenCompra['data']['cantHES'];

            // Verifica si debe Anticipos la OC
            if ($verificaDebeAnticipo['success']) {
                if ($verificaDebeAnticipo['cantAnticipos'] > 0) {
                    return [
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => true,
                        'NC' => $verificaDebeAnticipo['data']
                    ];
                } else {
                    return [
                        'success' => true,
                        'message' => $Message,
                        'anticipo' => false
                    ];
                }
            } else {
                $errorMessage = $verificaDebeAnticipo['message'];
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'anticipo' => false
                ];
            }
        } else {
            $errorMessage = $validOrdenCompra['message'];
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }
    }

    public function obtenerFacturasPorOC()
    {
        header('Content-Type: application/json');

        // Intentar obtener 'ordenCompra' desde $_POST (para application/x-www-form-urlencoded)
        $ordenCompra = $_POST['ordenCompra'] ?? null;
        $idsNotaCredito = $_POST['idsNotaCredito'] ?? [];

        // Si no está en $_POST, intentar leer el cuerpo de la petición (para application/json)
        if ($ordenCompra === null) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            $ordenCompra = $json_data['ordenCompra'] ?? null;
            $idsNotaCredito = $json_data['idsNotaCredito'] ?? [];
        }
        
        if ($ordenCompra !== null && !empty($ordenCompra)) {
            $comprasMdl = new Compras_Mdl();
            $response = $comprasMdl->buscarFacturasPorOC($ordenCompra, $idsNotaCredito);
        } else {
            $response = ['success' => false, 'message' => 'No se proporcionó una orden de compra en la petición.'];
        }

        echo json_encode($response);
        exit;
    }
}
?>