<?php

namespace App\Models\DatosCFDIs;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class CatalogosCFDIs_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase HojaEntrada_Mdl.</h2>";
        }

        $this->db = new BD_Connect();
    }

    public function obtenerVersionesCFDI()
    {
        try {
            // Consulta para obtener las versiones activas
            $sql = "SELECT version, fechaLimite, funcionActiva
                FROM versionescfdi
                WHERE estatus = 1";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, [], 'Obtener versiones activas de CFDI');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $versiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Versiones activas obtenidas:<br>';
                var_dump($versiones);
            }

            // Formatear el resultado en un arreglo asociativo por versión
            $resultado = [];
            foreach ($versiones as $version) {
                $resultado[$version['version']] = [
                    'fechaLimite' => $version['fechaLimite'],
                    'funcionActiva' => $version['funcionActiva']
                ];
            }

            return $resultado;
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] Error al obtener versiones de CFDI: " . $e->getMessage(), 3, LOG_FILE);
            if (self::$debug) {
                echo "Error al obtener versiones de CFDI: " . $e->getMessage();
            }
            return [];
        }
    }
}
