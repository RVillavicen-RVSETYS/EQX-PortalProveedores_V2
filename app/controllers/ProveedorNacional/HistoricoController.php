<?php
namespace App\Controllers\ProveedorNacional;

use Core\Controller;

class HistoricoController extends Controller {
    public function __construct()
    {
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        $this->checkSession();
    }

    public function index() {
        // Lógica para la vista de histórico
        $data = []; // Datos a pasar a la vista
        
        // Cargar la vista correspondiente
        $this->view('ProveedorNacional/Historico/index', $data);
    }
}