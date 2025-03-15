<?php

namespace App\Models\Configuraciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class BloqueoProveedores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Alertas_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    /* CONSULTAS DE SELECT */
    public function verificarBloqueo($idProveedor)
    {
        if (self::$debug) {
            echo "Ya entro a la función para verificar el bloqueo.<br>";
        }
        try {

            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        provFact.id AS 'IdBloqueo',
                        provFact.idProveedor AS 'IdProvBloqueo',
                        provFact.estatus AS 'EstatusBloqueo'
                    FROM
                        proveedores prov
                        LEFT JOIN conf_provFactSiempre provFact ON prov.id = provFact.idProveedor
                    WHERE
                        prov.id = :idProveedor";
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Verificar Bloqueo:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();

            $dataResul = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                if (self::$debug) {
                    echo "Error Al Obtener Datos Del Periodo De Bloqueo.<br>";
                }
                return ['success' => false, 'message' => 'No Existe Bloqueo De Proveedor'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php -> Error Al Obtener Datos Del Periodo De Bloqueo: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Datos Del Periodo De Bloqueo. Notifica a tu administrador'];
        }
    }

    public function obtenerPeriodoBloqueo()
    {
        if (self::$debug) {
            echo "Ya entro a la función para buscar la fecha de cierre de año.<br>";
        }
        try {

            $sql = "SELECT
                        cca.id AS 'Id',
                        DATE_FORMAT( cca.fechaInicio, '%d-%m-%Y' ) AS 'FechaIni',
	                    DATE_FORMAT( cca.fechaFin, '%d-%m-%Y' ) AS 'FechaFin',
                        cca.mensajeCierre AS 'MsjEsp',
                        cca.mensajeCierreIng AS 'MsjIng' 
                    FROM
                        conf_cierreAnio cca 
                    WHERE
                        cca.estatus = 1";
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Periodo De Bloqueo:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $dataResul = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                if (self::$debug) {
                    echo "Aun No Se Registra Un Cierre Anual.<br>";
                }
                return ['success' => false, 'message' => 'Aun No Se Registra Un Cierre Anual.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php -> Error Al Obtener Cierre Anual: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Cierre Anual. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function insertarBloqueo($idProveedor, $nombre, $estatusFact, $bloque)
    {
        try {

            $sql = "INSERT INTO conf_provFactSiempre (idProveedor, nombre, estatus, grupo, idUserReg, fechaReg) 
                        VALUES (:idProveedor, :nombre, :estatus, :grupo, :idUserReg, NOW())";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor,
                    ':nombre' => $nombre,
                    ':estatus' => $estatusFact,
                    ':grupo' => $bloque,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Insertar Nuevo Bloqueo.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':estatus', $estatusFact, PDO::PARAM_INT);
            $stmt->bindParam(':grupo', $bloque, PDO::PARAM_STR);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Datos Actualizados Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Datos.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar Datos.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Actualizar Datos De Bloqueo: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar Datos De Bloqueo: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Bloqueo De Proveedores, Notifica a tu administrador.'];
        }
    }

    public function actualizarBloqueo($idBloqueo, $idProveedor, $bloque, $estatusFact)
    {
        try {

            $sql = "UPDATE conf_provFactSiempre SET grupo = :grupo, estatus = :estatus WHERE id = :idBloqueo AND idProveedor = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':idBloqueo' => $idBloqueo,
                    ':idProveedor' => $idProveedor,
                    ':grupo' => $bloque,
                    ':estatus' => $estatusFact
                ];
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Datos Bloqueo.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idBloqueo', $idBloqueo, PDO::PARAM_INT);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':grupo', $bloque, PDO::PARAM_INT);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Datos Actualizados Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Datos.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar Datos.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Actualizar Datos De Bloqueo: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar Datos De Bloqueo: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Bloqueo De Proveedores, Notifica a tu administrador.'];
        }
    }

    public function desactivarCierreAnual()
    {
        try {

            $sql = "UPDATE conf_cierreAnio SET estatus = 0";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Desactivar Cierre Anual Anterior.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute();


            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'data' => 'Cierre Anual Desactivado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Desactivar Cierre Anual.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Desactivar Cierre Anual.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Desactivar Cierre Anual: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Desactivar Cierre Anual: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Al Desactivar El Cierre Anual, Notifica a tu administrador.'];
        }
    }

    public function insertarCierreAnual($fechaInicio, $fechaFin, $msjEsp, $msjIng)
    {
        try {
            $desactivar = $this->desactivarCierreAnual();
            if ($desactivar['success'] != true) {
                return ['success' => false, 'message' => 'Error Al Finalizar El Registro.'];
                exit(0);
            }

            $sql = "INSERT INTO conf_cierreAnio (fechaInicio, fechaFin, mensajeCierre, mensajeCierreIng, estatus, fechaReg, idUserReg) 
                    VALUES (:fechaInicio, :fechaFin, :mensajeCierre, :mensajeCierreIng, 1, NOW(), :idUserReg)";

            if (self::$debug) {
                $params = [
                    ':fechaInicio' => $fechaInicio,
                    ':fechaFin' => $fechaFin,
                    ':mensajeCierre' => $msjEsp,
                    ':mensajeCierreIng' => $msjIng,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Insertar Nuevo Bloqueo.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':mensajeCierre', $msjEsp, PDO::PARAM_STR);
            $stmt->bindParam(':mensajeCierreIng', $msjIng, PDO::PARAM_STR);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Cierre Anual Registrado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Registrar Cierre Anual.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Registrar Cierre Anual.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Registrar Cierre Anual: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Registrar Cierre Anual: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Registro De Cierre Anual, Notifica a tu administrador.'];
        }
    }

    public function actualizarTodosFacturan()
    {
        try {

            $sql = "UPDATE conf_provFactSiempre SET estatus = 1";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Proveedores Para Que Facturen.<br>');
            }
            $stmt = $this->db->prepare($sql);

            $resultado = $stmt->execute();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'data' => 'Estatus Actualizado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Estatus.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar Estatus.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Actualizar Estatus: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar Estatus: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Cambio De Estatus, Notifica a tu administrador.'];
        }
    }

    public function actualizarNadieFactura()
    {
        try {

            $sql = "UPDATE conf_provFactSiempre SET estatus = 0";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Proveedores Para Que Nadie Facture.<br>');
            }
            $stmt = $this->db->prepare($sql);

            $resultado = $stmt->execute();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'data' => 'Estatus Actualizado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Estatus.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar Estatus.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Actualizar Estatus: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar Estatus: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Cambio De Estatus, Notifica a tu administrador.'];
        }
    }

    public function actualizarListaProv()
    {
        try {

            $sql = "INSERT INTO conf_provFactSiempre ( idProveedor, nombre, estatus, grupo, idUserReg, fechaReg ) SELECT
                    prov.id AS 'idProveedor',
                    prov.nombre AS 'Proveedor',
                    0,
                    '',
                    :idUserReg,
                    NOW() 
                    FROM
                        proveedores prov 
                    WHERE
                        prov.id NOT IN (
                        SELECT
                            cpf.idProveedor 
                    FROM
                        conf_provFactSiempre cpf)";

            if (self::$debug) {
                $params = [
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Lista De Permisos Proveedores.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $resultado = $stmt->execute();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'data' => 'Lista Actualizada Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Lista De Proveedores.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar Lista De Proveedores.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Actualizar Lista De Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar Lista De Proveedores: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con La Actualización De La Lista De Proveedores, Notifica a tu administrador.'];
        }
    }

    public function agregaProveedor($idProveedor)
    {
        try {

            $estatus = 1;

            $sql = "INSERT INTO conf_provFactSiempre (idProveedor, nombre, estatus, idUserReg, fechaReg)
                    SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        :estatus,
                        :idUserReg,
                        NOW()
                    FROM
                        proveedores prov 
                    WHERE
                        id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':estatus' => $estatus,
                    ':idUserReg' => $_SESSION['EQXident'],
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Agregar Proveedor A Factura Siempre.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_INT);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $resultado = $stmt->execute();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar El Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar El Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Agregar El Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar El Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Los Proveedores, Notifica a tu administrador.'];
        }
    }

    public function cambiaEstatus($idProveedor)
    {
        try {

            $sql = "UPDATE conf_provFactSiempre SET estatus = IF(estatus = 1,0,1) WHERE idProveedor = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Agregar Proveedor A Factura Siempre.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $resultado = $stmt->execute();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'data' => 'Estatus Cambiado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Cambiar Estatus.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Cambiar Estatus.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/BloqueoProveedores_Mdl.php ->Error Al Cambiar Estatus: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Cambiar Estatus: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Los Proveedores, Notifica a tu administrador.'];
        }
    }
}
