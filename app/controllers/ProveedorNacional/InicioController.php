<?php
namespace App\Controllers\ProveedorNacional;

use Core\Controller; 

class InicioController extends Controller {
    public function __construct()
    {
        // Llama a checkSession para verificar la sesión y el estatus del usuario
        $this->checkSession();
    }

    public function index() {
        // Lógica para la vista de inicio
        $data = []; // Aquí puedes pasar datos a la vista si es necesario
        
        // Cargar la vista correspondiente
        $this->view('ProveedorNacional/Inicio/index', $data);
    }
}
