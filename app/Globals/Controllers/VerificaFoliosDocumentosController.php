<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\NotasCredito_Mdl;

class VerificaFoliosDocumentosController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        // Constructor can be empty or used for common setup
    }

    public function validaOrdenCompra($ordenCompra, $noProveedor, $contexto = 'proveedor')
    {
        if (empty($ordenCompra) || empty($noProveedor)) {
            echo json_encode(['success' => false, 'message' => 'La orden de compra y el proveedor son obligatorios.']);
            return;
        }

        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);

        if (!$validOrdenCompra['success']) {
            echo json_encode(['success' => false, 'message' => $validOrdenCompra['message']]);
            return;
        }

        $Message = $validOrdenCompra['data']['cantHES'];
        $filtros['folioCompra'] = $ordenCompra;
        $response = [
            'success' => true,
            'message' => $Message,
            'anticipo' => false // Default value
        ];

        // Context-specific check
        if ($contexto === 'admin') {
            $MDL_notasCredito = new NotasCredito_Mdl();
            $verificaSecundaria = $MDL_notasCredito->verificaNotaCreditoDeOrdenCompra($filtros);
            if ($verificaSecundaria['success'] && $verificaSecundaria['cantAnticipos'] > 0) {
                 $response['anticipo'] = true;
                 $response['NC'] = $verificaSecundaria['data'];
            }
        } else { // 'proveedor' context
            $MDL_anticipos = new Anticipos_Mdl();
            $verificaSecundaria = $MDL_anticipos->verificaAnticipoDeOrdenCompra($filtros);
             if ($verificaSecundaria['success'] && $verificaSecundaria['cantAnticipos'] > 0) {
                 $response['anticipo'] = true;
                 $response['NC'] = $verificaSecundaria['data'];
            }
        }
        
        // If the secondary check failed, it should be reported, otherwise the success response is sent.
        if (isset($verificaSecundaria) && !$verificaSecundaria['success']) {
             echo json_encode([
                'success' => false,
                'message' => $verificaSecundaria['message'],
                'anticipo' => false
            ]);
        } else {
            echo json_encode($response);
        }
    }

    public function validaHojaEntrada($ordenCompra, $hojaEntrada)
    {
        if (empty($ordenCompra) || empty($hojaEntrada)) {
            $Message = empty($ordenCompra) ? 'Registra primero una Orden de Compra.' : 'No se recibio una Hoja de Entrada.';
            echo json_encode(['success' => false, 'message' => $Message]);
            return;
        }

        $MDL_hojaEntrada = new HojaEntrada_Mdl();
        $validHES = $MDL_hojaEntrada->verificaHojaEntrada($ordenCompra, $hojaEntrada);

        if ($validHES['success']) {
            $Message = $validHES['cantHES'] . ' Hes validas.';
            echo json_encode(['success' => true, 'message' => $Message]);
        } else {
            $Message = $validHES['message'];
            echo json_encode(['success' => false, 'message' => $Message]);
        }
    }

    public function validaAnticipo($anticipo, $noProveedor)
    {
        if (empty($anticipo)) {
            echo json_encode(['success' => false, 'message' => 'Ingresa un Codigo de Anticipo.']);
            return;
        }

        $MDL_anticipos = new Anticipos_Mdl();
        $verificaAnticipo = $MDL_anticipos->verificaAnticipo($anticipo, $noProveedor);

        if ($verificaAnticipo['success']) {
            echo json_encode(['success' => true, 'message' => 'OK', 'anticipo' => false]);
        } else {
            echo json_encode(['success' => false, 'message' => $verificaAnticipo['message']]);
        }
    }
}
