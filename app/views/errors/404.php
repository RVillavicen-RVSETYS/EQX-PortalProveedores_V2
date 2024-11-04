<?php

// Detecta si es una solicitud AJAX o si se espera JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Establece la cabecera para indicar que es un error 404 en formato JSON
    header('Content-Type: application/json');
    http_response_code(404);

    // Respuesta JSON con el estado de error 404
    echo json_encode([
        'success' => false,
        'error' => '404 Not Found',
        'message' => 'La ruta solicitada no existe.'
    ]);
    exit;
} else {
    // Si no es AJAX, muestra la página HTML completa de 404
    http_response_code(404);
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error 404</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
            }

            h1 {
                font-size: 50px;
            }

            p {
                font-size: 20px;
                color: #666;
            }
        </style>
    </head>

    <body>
        <h1>Error 404</h1>
        <p>La página que estás buscando no existe.</p>
        <a href="/">Volver al inicio</a>
    </body>

    </html>
<?php
}
