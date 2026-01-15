<?php

namespace App\Models\Administradores;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}
require_once '../config/BD_Connect.php';

class Administradores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Administradores_Mdl.</h2>";
        }

        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function datosIniciales()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener los datos iniciales del Administrador.<br>";
        }
        try {

            $sql = "SELECT
                        uac.empleado_nombre AS 'Nombre',
                        uac.apellidoPat AS 'ApPat',
                        uac.apellidoMat AS 'ApMat',
                        uac.puesto AS 'Puesto'
                    FROM
                        vw_data_Usuarios_AccesoUsuarios uac 
                    WHERE
                        id = :idUsuario";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [
                    ':idUsuario' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Datos Iniciales Administrador');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idUsuario', $_SESSION['EQXident'], \PDO::PARAM_INT);
            $stmt->execute();

            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                if (self::$debug) {
                    echo "Error Al Obtener Alertas.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Las Alertas.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Alertas_Mdl.php -> Error al listar las Alertas: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Alertas. Notifica a tu administrador'];
        }
    }
}
