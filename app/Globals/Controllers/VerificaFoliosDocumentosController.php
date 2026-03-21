<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\DatosCompra\OrdenCompra_Mdl;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Models\DatosCompra\Anticipos_Mdl;
use App\Models\DatosCompra\NotasCredito_Mdl;
use App\Models\DatosCompra\ComprobantesPago_Mdl;

class VerificaFoliosDocumentosController extends Controller
{
    protected $debug = 0; // Debug validación OC/HES/folios

    public function __construct()
    {
        // Constructor can be empty or used for common setup
    }

    public function verificaOrdenCompraFactura($ordenCompra, $noProveedor)
    {
        // Verifica si una OC puede recibir facturas (HES pendientes y NC pendientes)
        if (empty($ordenCompra) || empty($noProveedor)) {
            echo json_encode(['success' => false, 'message' => 'La orden de compra y el proveedor son obligatorios.']);
            return;
        }

        if ($this->debug == 1) {
            echo '<br><br>=== INICIO verificaOrdenCompraFactura ===<br>';
            echo "Orden Compra: $ordenCompra<br>";
            echo "No Proveedor: $noProveedor<br>";
        }

        // 1. Verificar HES pendientes
        $MDL_ordenCompra = new OrdenCompra_Mdl();
        $validOrdenCompra = $MDL_ordenCompra->verificaOrdenCompra($ordenCompra, $noProveedor);

        if (!$validOrdenCompra['success']) {
            echo json_encode(['success' => false, 'message' => $validOrdenCompra['message']]);
            return;
        }

        $cantHES = $validOrdenCompra['data']['cantHES'] ?? 0;
        $messageHES = $cantHES > 0 
            ? "La Orden de Compra tiene HES pendientes por cargar" 
            : "No hay HES pendientes para esta Orden de Compra";

        if ($this->debug == 1) {
            echo "Cantidad HES: $cantHES<br>";
            echo "Message HES: $messageHES<br>";
        }

        // 2. Verificar Notas de Crédito pendientes
        $filtros['folioCompra'] = $ordenCompra;
        $MDL_notasCredito = new NotasCredito_Mdl();
        $verificaNC = $MDL_notasCredito->verificaNotaCreditoDeOrdenCompra($filtros);

        if ($this->debug == 1) {
            echo '<br>Resultado de verificaNotaCreditoDeOrdenCompra:<br>';
            var_dump($verificaNC);
        }

        $cantNC = 0;
        $messageNC = "La Orden de Compra registrada no tiene Notas de Credito Pendientes";
        $NC = [];

        if ($verificaNC['success']) {
            $cantNC = $verificaNC['cantAnticipos'] ?? 0;
            if ($cantNC > 0) {
                $messageNC = "La Orden de Compra tiene Notas de Credito Pendientes";
                // Incluir las políticas de NC para mostrar el formulario
                $NC = $verificaNC['data'] ?? [];
            } else {
                $messageNC = "La Orden de Compra registrada no tiene Notas de Credito Pendientes";
            }
        } else {
            // Si falla la verificación, registrar el error pero continuar
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Globals/Controllers/VerificaFoliosDocumentosController.php -> Error al verificar notas de crédito en verificaOrdenCompraFactura: " . ($verificaNC['message'] ?? 'Error desconocido'), 3, LOG_FILE_BD);
        }

        if ($this->debug == 1) {
            echo "Cantidad NC: $cantNC<br>";
            echo "Message NC: $messageNC<br>";
            if ($cantNC > 0) {
                echo "Políticas NC encontradas: " . count($NC) . "<br>";
            }
        }

        $response = [
            'success' => true,
            'cantHES' => $cantHES,
            'cantNC' => $cantNC,
            'messageHES' => $messageHES,
            'messageNC' => $messageNC
        ];

        // Agregar el array NC solo si hay NC pendientes
        if ($cantNC > 0 && !empty($NC)) {
            $response['NC'] = $NC;
        }

        if ($this->debug == 1) {
            echo '<br>RESPUESTA verificaOrdenCompraFactura:<br>';
            var_dump($response);
            echo '<br>=== FIN verificaOrdenCompraFactura ===<br><br>';
        }

        echo json_encode($response);
    }

    public function verificaOrdenCompraNotaCredito($ordenCompra, $noProveedor)
    {
        // Verifica si hay notas de crédito pendientes para cargar nota de crédito independiente
        if (empty($ordenCompra) || empty($noProveedor)) {
            echo json_encode(['success' => false, 'message' => 'La orden de compra y el proveedor son obligatorios.']);
            return;
        }

        if ($this->debug == 1) {
            echo '<br><br>=== INICIO verificaOrdenCompraNotaCredito ===<br>';
            echo "Orden Compra: $ordenCompra<br>";
            echo "No Proveedor: $noProveedor<br>";
        }

        // Verificar Notas de Crédito pendientes
        $filtros['folioCompra'] = $ordenCompra;
        $MDL_notasCredito = new NotasCredito_Mdl();
        $verificaNC = $MDL_notasCredito->verificaNotaCreditoDeOrdenCompra($filtros);

        if ($this->debug == 1) {
            echo '<br>Resultado de verificaNotaCreditoDeOrdenCompra:<br>';
            var_dump($verificaNC);
        }

        $cantNC = 0;
        $messageNC = "La Orden de Compra registrada no tiene Notas de Credito Pendientes";
        $NC = [];

        if ($verificaNC['success']) {
            $cantNC = $verificaNC['cantAnticipos'] ?? 0;
            if ($cantNC > 0) {
                $messageNC = "Hay Notas de Credito que se deben recibir";
                $NC = $verificaNC['data'] ?? [];
            } else {
                $messageNC = "La Orden de Compra registrada no tiene Notas de Credito Pendientes";
            }
        } else {
            // Si falla la verificación, registrar el error
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Globals/Controllers/VerificaFoliosDocumentosController.php -> Error al verificar notas de crédito en verificaOrdenCompraNotaCredito: " . ($verificaNC['message'] ?? 'Error desconocido'), 3, LOG_FILE_BD);
        }

        if ($this->debug == 1) {
            echo "Cantidad NC: $cantNC<br>";
            echo "Message NC: $messageNC<br>";
        }

        $response = [
            'success' => true,
            'cantNC' => $cantNC,
            'messageNC' => $messageNC,
            'NC' => $NC
        ];

        if ($this->debug == 1) {
            echo '<br>RESPUESTA verificaOrdenCompraNotaCredito:<br>';
            var_dump($response);
            echo '<br>=== FIN verificaOrdenCompraNotaCredito ===<br><br>';
        }

        echo json_encode($response);
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

    public function VerificaComplementosPendientesPorProveedor($noProveedor)
    {
        if (empty($noProveedor)) {
            echo json_encode(['success' => false, 'message' => 'El número de proveedor es obligatorio.']);
            return;
        }

        $filtros = [
            'idProveedor' => $noProveedor
        ];

        $MDL_comprobantesPago = new ComprobantesPago_Mdl();
        $resultado = $MDL_comprobantesPago->obtenerComplementosPendientes($filtros);

        if ($resultado['success']) {
            $cantData = $resultado['cantRes'];
            $message = $cantData > 0 
                ? "Se encontraron $cantData factura(s) con complementos de pago pendientes." 
                : "No detectamos Complementos de Pago Pendientes de este Proveedor.";
            
            echo json_encode([
                'success' => true,
                'cantData' => $cantData,
                'message' => $message
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $resultado['message'] ?? 'Error al verificar complementos de pago pendientes.'
            ]);
        }
    }
}
