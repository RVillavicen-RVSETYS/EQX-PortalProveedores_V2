<?php

namespace App\Models\DatosCFDIs;

use PDO;
use BD_Connect;
use App\Globals\Controllers\DocumentosController;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class RegistroCFDIsv33_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la RegistroCFDIsv33_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
    }

}
