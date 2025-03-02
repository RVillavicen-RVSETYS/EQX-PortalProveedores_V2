<?php

namespace App\Models\Configuraciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class DescuentoProveedores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase DescuentoProveedores_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    /* CONSULTAS DE SELECT */
    public function listaProveedores()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener el select de proveedores que no tienen descuento.<br>";
        }
        try {

            $sql = "SELECT
                            prov.id AS 'IdProveedor',
                            prov.nombre AS 'Proveedor' 
                        FROM
                            proveedores prov
                        WHERE
                            prov.id NOT IN 
                            (SELECT pfd.idProveedor FROM conf_provFactDescuento pfd)";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Con Descuento:');
            }

            $stmt = $this->db->prepare($sql);
            //$stmt->bindParam('', $nivel, \PDO::PARAM_INT);
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
                    echo "Error Al Obtener Lista De Proveedores Con Descuento.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener Lista De Proveedores Con Descuento.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/DescuentoProveedores_Mdl.php -> Error al listar Proveedores Con Descuento: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al listar Proveedores Con Descuento. Notifica a tu administrador'];
        }
    }

    public function provedoresConDesc()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de Proveedores con Descuento.<br>";
        }
        try {

            $sql = "SELECT
                        cpfd.id AS 'Id',
                        cpfd.idProveedor AS 'IdProveedor',
                        cpfd.nombre AS 'Proveedor',
                        cpfd.estatus AS 'Estatus' 
                    FROM
                        conf_provFactDescuento cpfd";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Con Descuento:');
            }

            $stmt = $this->db->prepare($sql);
            //$stmt->bindParam('', $nivel, \PDO::PARAM_INT);
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
                    echo "Error Al Obtener Lista De Proveedores Con Descuento.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener Lista De Proveedores Con Descuento.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/DescuentoProveedores_Mdl.php -> Error al listar Proveedores Con Descuento: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al listar Proveedores Con Descuento. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function agregarProvDesc($idProveedor)
    {
        try {
            $sql = "INSERT INTO conf_provFactDescuento ( idProveedor, nombre, estatus ) SELECT
                    prov.id AS 'IdProveedor',
                    prov.nombre AS 'Proveedor',
                    1 AS 'Estatus' 
                    FROM
                        proveedores prov 
                    WHERE
                        prov.id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Agregar Proveedor Con Descuento.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/DescuentoProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Agregar Los Proveedores Con Descuento, Notifica a tu administrador.'];
        }
    }

    public function cambiaEstatus($idDescuento, $nuevoEstatus)
    {
        try {
            $sql = "UPDATE conf_provFactDescuento SET estatus = :nuevoEstatus WHERE id = :idDescuento;";


            if (self::$debug) {
                $params = [
                    ':idDescuento' => $idDescuento,
                    ':nuevoEstatus' => $nuevoEstatus
                ];
                $this->db->imprimirConsulta($sql, $params, 'Cambiar Estatus De Permiso Para Descuento.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idDescuento', $idDescuento, PDO::PARAM_INT);
            $stmt->bindParam(':nuevoEstatus', $nuevoEstatus, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Estatus Cambiado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Cambiar Estatus.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Cambiar Estatus.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/DescuentoProveedores_Mdl.php ->Error Al Cambiar Estatus: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Cambiar Estatus: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Los Permisos Para Los Descuentos, Notifica a tu administrador.'];
        }
    }
}
