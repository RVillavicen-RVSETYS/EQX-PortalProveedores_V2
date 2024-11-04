<?php
// Incluir archivos necesarios para la gestión de errores y sesiones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/constantes.php'; // Ajusta la ruta si es necesario

// Verificar si hay mensajes de error almacenados en la sesión
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Limpiar el mensaje después de mostrarlo
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="/css/style.css"> <!-- Ajusta la ruta a tu CSS -->
</head>

<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <h1><?php echo htmlspecialchars($error_message); ?></h1>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login/authenticate" method="post">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
    <div id="loginMessage" style="background-color: red;">dfg</div>
</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#loginForm--DISABLED").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '/login/authenticate',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#loginMessage').html(response);
                    if (response.success) {
                        window.location.href = response.redirect; // Redirige en caso de éxito
                    } else {
                        $('#loginMessage').html(response.message); // Muestra el mensaje de error
                    }
                },
                error: function() {
                    $('#loginMessage').html('Error en el inicio de sesión.Consulta a tu administrador');
                }
            });
        });
    });
</script>