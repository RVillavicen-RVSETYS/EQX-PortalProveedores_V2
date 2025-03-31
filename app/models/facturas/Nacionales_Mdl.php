<?php

namespace App\Models\Facturas;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
require_once '../config/BD_Connect.php';

class Nacionales_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Nacionales_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function obtenerAprobacionesNacionales()
    {
        try {
            $sql = "";

            if (self::$debug) {
                $params = [
                    
                ];
                $this->db->imprimirConsulta($sql, $params, 'Obtiene Los Datos De Un Proveedor.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $dataProveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataProveedor);
                echo '<br><br>';
            }

            if ($dataProveedor) {
                return ['success' => true, 'data' => $dataProveedor]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No Hay Ningún Proveedor Con Ese Nombre.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No Hay Ningún Proveedor Con Ese Nombre..'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Obtener Datos Del Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error Al Obtener Datos Del Proveedor: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas Con El Proveedor, Notifica a tu administrador.'];
        }
    }
}