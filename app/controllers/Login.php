<?php
namespace App\Controllers;

use App\Models\Login_Mdl;

class Login {
    public function index() {
        require_once '../app/views/login/index.php'; // Cargar la vista de login
    }

    public function authenticate() {
        // Obtener usuario y contraseña desde el formulario
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $loginModel = new Login_Mdl();
        $isAuthenticated = $loginModel->checkCredentials($username, $password);

        if ($isAuthenticated) {
            echo 'Redirigir a Dashboard';
            header('Location: /dashboard');
            exit();
        } else {
            echo 'Credenciales incorrectas'; // Mensaje temporal, se puede mejorar con una vista
        }
    }
}
