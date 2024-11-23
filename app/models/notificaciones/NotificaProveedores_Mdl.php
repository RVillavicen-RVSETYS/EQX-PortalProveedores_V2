<?php

namespace App\Models\Notificaciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class NotificaProveedores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase CierrePortal_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function NotificacionesProveedor($paisProveedor)
    {
        $tipoProveedor = ($paisProveedor == 'MX') ? 'NAC' : 'INT' ;
        try {
            $sql = "SELECT * 
                    FROM conf_notificaProveedor 
                    WHERE estatus = '1' AND tipoProveedor = :tipoProveedor
                        AND ((periodo = '1') OR (periodo = '2' AND NOW() BETWEEN fechaInicio AND fechaFin))
                    ORDER BY id DESC ";

            if (self::$debug) {
                $params = [':tipoProveedor' => $tipoProveedor];
                $this->db->imprimirConsulta($sql, $params, 'Busca Notificaciones a Proveedores.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':tipoProveedor', $tipoProveedor, PDO::PARAM_STR);
            $stmt->execute();
            $notificaProveedor = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($notificaProveedor);
                echo '<br><br>';
            }
            if ($notificaProveedor) {
                return ['success' => true, 'data' => $notificaProveedor]; // Retornar datos si tiene permisos a algun Area                
            } else {
                if (self::$debug) {
                    echo "No hay notificaciones.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No hay notificaciones.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/notificaciones/NotificaProveedores_Mdl.php ->Error al buscar notificaciones: " . $e->getMessage(), 3, LOG_FILE); // Manejo del error
            if (self::$debug) {
                echo "Error al buscar notificaciones: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return null; // Retorna null en caso de error
        }
    }

}
