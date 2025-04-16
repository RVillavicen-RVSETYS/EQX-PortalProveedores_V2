<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_ConnectHES; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';

class OrdenCompra_Mdl
{
    private $dbHES;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase OrdenCompra_Mdl.</h2>";
        }
        
        $this->dbHES = new BD_ConnectHES(); // Instancia de la conexión a la base de datos
    }

    public function verificaOrdenCompra($ordenCompra, $noProveedor)
    {
        try {
            $sql = "SELECT COUNT(id_OC) AS cantHES
              FROM vw_ext_PortalProveedores_HESporPagar
              WHERE idProveedor = :noProveedor AND OrdenCompra = :ordenCompra";

            if (self::$debug) {
                $params = [
                    ':ordenCompra' => $ordenCompra,
                    ':noProveedor' => $noProveedor
                ];
                $this->dbHES->imprimirConsulta($sql, $params, 'Busca Orden de Compra.');
            }
            $stmt = $this->dbHES->prepare($sql);
            $stmt->bindParam(':ordenCompra', $ordenCompra, PDO::PARAM_STR);
            $stmt->bindParam(':noProveedor', $noProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $cantRecepciones = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($cantRecepciones);
                echo '<br><br>';
            }

            if ($cantRecepciones["cantHES"] > 0) {
                return ['success' => true, 'data' => $cantRecepciones]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No hay ninguna HES para esta OC.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No hay recepciones para esta OC o no es de este Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/datosCompra/OrdenCompra_Mdl.php ->Error contar HES de la OC: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error al contar HES: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas con tu OC, Notifica a tu administrador.'];
        }
    }
}