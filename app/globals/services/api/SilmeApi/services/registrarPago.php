<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$debug = 0;

/* Recibimos Credenciales */
$ApiKey = (isset($_REQUEST['ApiKey']) && !empty($_REQUEST['ApiKey'])) ? $_REQUEST['ApiKey'] : '';
$SecretKey = (isset($_REQUEST['SecretKey']) && !empty($_REQUEST['SecretKey'])) ? $_REQUEST['SecretKey'] : '';

/* Verificamos Existan Credenciales */
if ($ApiKey == '' or $SecretKey == '') {
    http_response_code(HTTP_BAD_REQUEST);
    $response = array(
        'code' => HTTP_BAD_REQUEST,
        'status' => 'error',
        'message' => 'No se recibieron credenciales.'
    );
    echo json_encode($response);
} else {
    if ($debug == 1) {
        http_response_code(HTTP_GOOD_REQUEST);
        $response = array(
            'code' => HTTP_GOOD_REQUEST,
            'status' => 'success',
            'message' => 'Se recibieron credenciales.'
        );
        echo json_encode($response);
    }
}

/* Verificar Que Las Credenciales Sean Las Mismas */
if (password_verify($ApiKey, API_KEY) and password_verify($SecretKey, SECRET_KEY)) {
    if ($debug == 1) {
        http_response_code(HTTP_GOOD_REQUEST);
        $response = array(
            'code' => HTTP_GOOD_REQUEST,
            'status' => 'success',
            'message' => 'Las credenciales son correctas.'
        );
        echo json_encode($response);
    }
} else {
    http_response_code(HTTP_BAD_REQUEST);
    $response = array(
        'code' => HTTP_BAD_REQUEST,
        'status' => 'error',
        'message' => 'Las credenciales son incorrectas.'
    );
    echo json_encode($response);
}
