<?php

namespace App\Models\Configuraciones;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class Alertas_Mdl
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
    public function obtenerListaAlertas()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de Alertas.<br>";
        }
        try {

            $sql = "SELECT
                        cnp.id AS 'IdNotificacion',
                        cnp.tipoProveedor AS 'TipoProveedor',
                        cnp.titulo AS 'Titulo',
                        cnp.mensaje AS 'Mensaje',
                        cnp.tipoMensaje AS 'TipoMsj',
                        cnp.periodo AS 'TipoPeriodo',
                        DATE_FORMAT( cnp.fechaInicio, '%d-%m-%Y' ) AS 'Inicio',
	                    DATE_FORMAT( cnp.fechaFin, '%d-%m-%Y' ) AS 'Fin',
                        cnp.estatus AS 'Estatus' 
                    FROM
                        conf_notificaProveedor cnp";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Alertas:');
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

    public function cargaDatos($idNotificacion)
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la información de una Alerta.<br>";
        }
        try {

            $sql = "SELECT
                        cnp.id AS 'IdNotificacion',
                        cnp.tipoProveedor AS 'TipoProveedor',
                        cnp.titulo AS 'Titulo',
                        cnp.mensaje AS 'Mensaje',
                        cnp.tipoMensaje AS 'TipoMsj',
                        cnp.periodo AS 'TipoPeriodo',
                        DATE_FORMAT( cnp.fechaInicio, '%d-%m-%Y' ) AS 'Inicio',
	                    DATE_FORMAT( cnp.fechaFin, '%d-%m-%Y' ) AS 'Fin',
                        cnp.estatus AS 'Estatus' 
                    FROM
                        conf_notificaProveedor cnp
                    WHERE 
                        cnp.id = :idNotificacion;";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [
                    ':idNotificacion' => $idNotificacion,
                ];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Información De Una Alerta:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idNotificacion', $idNotificacion, PDO::PARAM_INT);
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
                    echo "Error Al Obtener Datos De La Alerta.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener Datos De La Alerta.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Alertas_Mdl.php -> Error Al Obtener Información De Una Alerta: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Información De Alertas. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function nuevasAlertas($titulo, $descripcion, $tipoMensaje, $tipoProveedor, $tipoPeriodo, $fechaInicio, $fechaFin)
    {
        try {
            $sql = "INSERT INTO conf_notificaProveedor (tipoProveedor, titulo, mensaje, tipoMensaje, periodo, fechaInicio, fechaFin, estatus, idUserReg, fechaReg) 
                    VALUES (:tipoProveedor, :titulo, :mensaje, :tipoMensaje, :periodo, :fechaInicio, :fechaFin, :estatus, :idUserReg, NOW())";
            $estatus = 1;
            if ($tipoPeriodo == 1) {
                $fechaInicio = NULL;
                $fechaFin = NULL;
            }

            if (self::$debug) {
                $params = [
                    ':tipoProveedor' => $tipoProveedor,
                    ':titulo' => $titulo,
                    ':mensaje' => $descripcion,
                    ':tipoMensaje' => $tipoMensaje,
                    ':periodo' => $tipoPeriodo,
                    ':fechaInicio' => $fechaInicio,
                    ':fechaFin' => $fechaFin,
                    ':estatus' => $estatus,
                    ':idUserReg' => $_SESSION['EQXident']
                ];
                $this->db->imprimirConsulta($sql, $params, 'Crear Nueva Alerta.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':tipoProveedor', $tipoProveedor, PDO::PARAM_STR);
            $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindParam(':mensaje', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':tipoMensaje', $tipoMensaje, PDO::PARAM_STR);
            $stmt->bindParam(':periodo', $tipoPeriodo, PDO::PARAM_INT);
            $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_INT);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Alerta Creada Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Crear Alerta.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Crear Alerta.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Alertas_Mdl.php ->Error Al Crear Alerta: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Crear Alerta: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con La Creación De Alertas, Notifica a tu administrador.'];
        }
    }

    public function cambiaEstatus($idNotificacion, $nuevoEstatus)
    {
        try {
            $sql = "UPDATE conf_notificaProveedor SET estatus = :nuevoEstatus WHERE id = :idNotificacion;";


            if (self::$debug) {
                $params = [
                    ':idNotificacion' => $idNotificacion,
                    ':nuevoEstatus' => $nuevoEstatus
                ];
                $this->db->imprimirConsulta($sql, $params, 'Cambiar Estatus De Alerta.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idNotificacion', $idNotificacion, PDO::PARAM_INT);
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
            error_log("[$timestamp] app/models/Alertas_Mdl.php ->Error Al Cambiar Estatus: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Cambiar Estatus: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con Las Alertas, Notifica a tu administrador.'];
        }
    }

    public function editarAlertas($idNotificacion, $titulo, $descripcion, $tipoMensaje, $tipoProveedor, $tipoPeriodo, $fechaInicio, $fechaFin)
    {
        try {

            $sql = "UPDATE conf_notificaProveedor 
                    SET tipoProveedor = :tipoProveedor,
                    titulo = :titulo,
                    mensaje = :mensaje,
                    tipoMensaje = :tipoMensaje,
                    periodo = :periodo,
                    fechaInicio = :fechaInicio,
                    fechaFin = :fechaFin,
                    estatus = :estatus,
                    idUserReg = :idUserReg,
                    fechaReg = NOW()
                    WHERE id = :idNotificacion";

            $estatus = 1;
            if ($tipoPeriodo == 1) {
                $fechaInicio = NULL;
                $fechaFin = NULL;
            }

            if (self::$debug) {
                $params = [
                    ':tipoProveedor' => $tipoProveedor,
                    ':titulo' => $titulo,
                    ':mensaje' => $descripcion,
                    ':tipoMensaje' => $tipoMensaje,
                    ':periodo' => $tipoPeriodo,
                    ':fechaInicio' => $fechaInicio,
                    ':fechaFin' => $fechaFin,
                    ':estatus' => $estatus,
                    ':idUserReg' => $_SESSION['EQXident'],
                    ':idNotificacion' => $idNotificacion
                ];
                $this->db->imprimirConsulta($sql, $params, 'Editar Una Alerta.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':tipoProveedor', $tipoProveedor, PDO::PARAM_STR);
            $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindParam(':mensaje', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':tipoMensaje', $tipoMensaje, PDO::PARAM_STR);
            $stmt->bindParam(':periodo', $tipoPeriodo, PDO::PARAM_INT);
            $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_INT);
            $stmt->bindParam(':idUserReg', $_SESSION['EQXident'], PDO::PARAM_INT);
            $stmt->bindParam(':idNotificacion', $idNotificacion, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Alerta Actualizada Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar Alerta.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar Alerta.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Alertas_Mdl.php ->Error Al Actualizar Alerta: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar Alerta: " . $e->getMessage() . PHP_EOL;
            }
            return ['success' => false, 'message' => 'Problemas Con La Actualización De Alertas, Notifica a tu administrador.'];
        }
    }
}
