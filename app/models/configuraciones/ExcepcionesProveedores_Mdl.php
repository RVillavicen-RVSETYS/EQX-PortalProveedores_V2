<?php

namespace App\Models\Configuraciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class ExcepcionesProveedores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase ExcepcionesProveedores_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    /* CONSULTAS DE SELECT */
    public function obtenerIgnoraDesc()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores con descuento que se van a ignorar.<br>";
        }
        try {

            $sql = "SELECT
                        pid.id AS 'IdConfDesc',
                        pid.idProveedor AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        pid.motivo AS 'Motivo',
                        pid.estatus AS 'Estatus'
                    FROM
                        conf_provIgnoraDescuento pid
                        INNER JOIN proveedores prov ON pid.idProveedor = prov.id
                    ORDER BY pid.idProveedor ASC";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Ignorados Con Descuento:');
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
                    echo "Error Al Obtener Proveedores Ignorados.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores Ignorados.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener Proveedores Ignorados: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores Ignorados. Notifica a tu administrador'];
        }
    }

    public function obtenerExentos()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores exentos de año fiscal.<br>";
        }
        try {

            $sql = "SELECT
                        pe.id AS 'IdExento',
                        pe.idProveedor AS 'IdProveedor',
                        pe.nombre AS 'Proveedor',
                        pe.estatus AS 'Estatus' 
                    FROM
                        conf_provExentoAnoFisc pe
                    ORDER BY pe.idProveedor ASC";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Exentos De Año Fiscal:');
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
                    echo "Error Al Obtener Proveedores Exentos Año Fiscal.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores Exentos Año Fiscal.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener Proveedores Exentos Año Fiscal: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores Exentos Año Fiscal. Notifica a tu administrador'];
        }
    }

    public function obtenerFechaEmision()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores exentos de Fecha De Emision.<br>";
        }
        try {

            $sql = "SELECT
                        efe.id AS 'IdExento',
                        efe.idProveedor AS 'IdProveedor',
                        efe.nombre AS 'Proveedor',
                        efe.estatus AS 'Estatus' 
                    FROM
                        conf_provExentoFechaEmision efe
                    ORDER BY
                        efe.idProveedor ASC";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Exentos De Fecha De Emision:');
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
                    echo "Error Al Obtener Proveedores Exentos Fecha De Emision.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores Exentos Fecha De Emision.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener Proveedores Exentos Fecha De Emision: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores Exentos Fecha De Emision. Notifica a tu administrador'];
        }
    }

    public function obtenerUsoCfdi()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores con Uso De CFDI Distinto.<br>";
        }
        try {

            $sql = "SELECT
                        ucd.id AS 'IdConf',
                        ucd.idProveedor AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        ucd.usoCFDI AS 'Codigo',
                        uc.descripcion AS 'UsoCfdi',
                        ucd.estatus AS 'Estatus'
                    FROM
                        conf_provUsoCfdiDistinto ucd
                        INNER JOIN proveedores prov ON ucd.idProveedor = prov.id
                        INNER JOIN sat_catUsoCFDI uc ON ucd.usoCFDI = uc.id";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores con Uso De CFDI Distinto:');
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
                    echo "Error Al Obtener Proveedores con Uso De CFDI Distinto.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores con Uso De CFDI Distinto.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener Proveedores con Uso De CFDI Distinto: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores con Uso De CFDI Distinto. Notifica a tu administrador'];
        }
    }

    public function obtenerBloqueoDiferencias()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores que tienen bloqueo de diferencias en montos.<br>";
        }
        try {

            $sql = "SELECT
                        bf.id AS 'IdBloq',
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        prov.razonSocial AS 'RazonSocial',
                        bf.motivo AS 'Motivo'
                    FROM
                        conf_provBloqDiferencias bf
                        INNER JOIN proveedores prov ON bf.idProveedor = prov.id";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores con Bloqueo De Diferencias En Montos:');
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
                    echo "Error Al Obtener Proveedores con Uso De CFDI Distinto.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores con Uso De CFDI Distinto.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener Proveedores con Uso De CFDI Distinto: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores con Uso De CFDI Distinto. Notifica a tu administrador'];
        }
    }

    public function getProveedores($tabla)
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores.<br>";
        }
        try {

            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor' 
                    FROM
                        proveedores prov
                        LEFT JOIN $tabla confProv ON prov.id = confProv.idProveedor 
                    WHERE
                        confProv.idProveedor IS NULL;";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores:');
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
                    echo "Error Al Obtener Proveedores.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores. Notifica a tu administrador'];
        }
    }

    public function obtenerCatUsoCfdi()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener el catalogo de uso de CFDI.<br>";
        }
        try {

            $sql = "SELECT
                        uc.id AS 'IdUsoCfdi',
                        uc.descripcion AS 'UsoCfdi' 
                    FROM
                        sat_catUsoCFDI uc 
                    WHERE
                        uc.estatus = 1";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Catalogo Uso CFDI:');
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
                    echo "Error Al Obtener El Catalogo De Usos De CFDI.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron El Catalogo De Usos De CFDI.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Error Al Obtener El Catalogo De Usos De CFDI: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener El Catalogo De Usos De CFDI. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function cambiarEstatus($tabla, $identificador, $nuevoEstatus, $idProveedor)
    {
        try {
            $timestamp = date("Y-m-d H:i:s");
            $year = date("Y");
            $logDir = LOG_SYSTEM . 'excepciones/' . $year;
            $estatus = ($nuevoEstatus == 1) ? 'Activó' : 'Desactivó';

            switch ($tabla) {
                case '1':
                    $tabla = "conf_provIgnoraDescuento";
                    $logFile = $logDir . '/ignoraDescuento.log';
                    break;
                case '2':
                    $tabla = "conf_provExentoAnoFisc";
                    $logFile = $logDir . '/exentoAnioFiscal.log';
                    break;
                case '3':
                    $tabla = "conf_provExentoFechaEmision";
                    $logFile = $logDir . '/exentoFechaEmision.log';
                    break;
                case '4':
                    $tabla = "conf_provUsoCfdiDistinto";
                    $logFile = $logDir . '/usoCfdiDistinto.log';
                    break;
                case '5':
                    $tabla = "conf_provBloqDiferencias";
                    $logFile = $logDir . '/bloqueoDiferencias.log';
                    break;
            }

            $sql = "UPDATE $tabla SET estatus = :nuevoEstatus WHERE id = :identificador";

            if (self::$debug) {
                $params = [
                    ':identificador' => $identificador,
                    ':nuevoEstatus' => $nuevoEstatus
                ];
                $this->db->imprimirConsulta($sql, $params, 'Cambiar Estatus De Alerta.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador, PDO::PARAM_INT);
            $stmt->bindParam(':nuevoEstatus', $nuevoEstatus, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {

                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }

                error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Se " . $estatus . " El Proveedor: " . $idProveedor . ", IdUserReg: " . $_SESSION['EQXident'] . PHP_EOL, 3, $logFile);

                return ['success' => true, 'data' => 'Estatus Cambiado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Cambiar Estatus.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Cambiar Estatus.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Cambiar Estatus: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Cambiar Estatus: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }

    public function eliminarReg($identificador)
    {
        try {

            $sql = "DELETE FROM conf_provBloqDiferencias WHERE id = :identificador";

            if (self::$debug) {
                $params = [
                    ':identificador' => $identificador,

                ];
                $this->db->imprimirConsulta($sql, $params, 'Eliminar Proveedor De Bloqueo Diferencias.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Proveedor Eliminado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Eliminar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Eliminar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Eliminar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Eliminar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }

    public function registraProveedorIG($idProveedor, $motivo)
    {
        try {

            $sql = "INSERT INTO conf_provIgnoraDescuento (idProveedor, motivo, estatus, idUserReg, fechaReg) VALUES (:idProveedor, :motivo, 1, :idUserReg, NOW());";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor,
                    ':motivo' => $motivo,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Registrar Proveedor Ignorar Descuento.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/ignoraDescuento.log';
                error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . " Con Motivo: '$motivo', IdUserReg: " . $_SESSION['EQXident'] . PHP_EOL, 3, $logFile);

                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }

    public function registraProveedorEAF($idProveedor)
    {
        try {

            $sql = "INSERT INTO conf_provExentoAnoFisc (idProveedor, nombre, estatus, idUserReg, fechaReg) SELECT prov.id, prov.nombre, 1, :idUserReg, NOW() FROM proveedores prov WHERE prov.id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Registrar Proveedor Exento Año Fiscal.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {

                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/exentoAnioFiscal.log';
                error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . ", IdUserReg: " . $_SESSION['EQXident'] . PHP_EOL, 3, $logFile);

                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }

    public function registraProveedorEFE($idProveedor)
    {
        try {

            $sql = "INSERT INTO conf_provExentoFechaEmision (idProveedor, nombre, estatus, idUserReg, fechaReg) SELECT prov.id, prov.nombre, 1, :idUserReg, NOW() FROM proveedores prov WHERE prov.id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Registrar Proveedor Exento Fecha Emisión.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {

                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/exentoTiempoEmision.log';
                error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . ", IdUserReg: " . $_SESSION['EQXident'] . PHP_EOL, 3, $logFile);

                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }

    public function registraProveedorUC($idProveedor, $idUsoCfdi)
    {
        try {

            $sql = "INSERT INTO conf_provUsoCfdiDistinto (idProveedor, usoCFDI, estatus, idUserReg, fechaReg) VALUES (:idProveedor, :idUsoCfdi, 1, :idUserReg, NOW());";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor,
                    ':idUsoCfdi' => $idUsoCfdi,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Registrar Proveedor Exento Fecha Emisión.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':idUsoCfdi', $idUsoCfdi, PDO::PARAM_STR);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {

                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/usoCfdiDistinto.log';
                error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . ", IdUserReg: " . $_SESSION['EQXident'] . PHP_EOL, 3, $logFile);

                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }

    public function registraProveedorBD($idProveedor, $motivo)
    {
        try {

            $sql = "INSERT INTO conf_provBloqDiferencias (idProveedor, idUserReg, motivo, fechaReg) VALUES (:idProveedor, :idUserReg, :motivo, NOW());";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor,
                    ':idUserReg' => $_SESSION['EQXident'],
                    ':motivo' => $motivo,
                ];
                $this->db->imprimirConsulta($sql, $params, 'Registrar Proveedor Exento Fecha Emisión.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {

                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/bloqueoDeDiferencias.log';
                error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php -> Se Agregó El Proveedor: " . $idProveedor . " Con Motivo: '$motivo', IdUserReg: " . $_SESSION['EQXident'] . PHP_EOL, 3, $logFile);

                return ['success' => true, 'data' => 'Proveedor Agregado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Agregar Proveedor.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/ExcepcionesProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Excepciones, Notifica a tu administrador.'];
        }
    }
}
