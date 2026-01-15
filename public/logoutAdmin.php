<?php
require_once '../config/constantes.php';

session_start();

session_destroy();

header('Location: '.URL_BASE_PROYECT.'/AdminPanel');


?>