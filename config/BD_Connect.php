<?php
if (!defined('INCLUDE_CHECK')) die('No se puede leer este archivo');

class BD_Connect {
    private static $conn;        // Conexión de la base de datos
    private static $debug = 0;   // Cambia a 0 para desactivar los mensajes de depuración
    private static $logFile = '../logs/debug_BD.log'; // Archivo de log para errores de conexión

    /**
     * Obtiene la conexión a la base de datos
     *
     * @return PDO
     */
    public static function getConnection() {
        if (!isset(self::$conn)) {
            try {
                // Configura los detalles de conexión de PDO
                $host = '82.165.209.227';
                $database = 'EQX_PortalProveedoresV2';
                $username = 'RVSetysTest';
                $password = 'RV53ty5.p4$$wd';

                $dsn = "mysql:host=".$host.";dbname=".$database.";charset=utf8";

                // Crea la conexión con PDO y establece opciones
                self::$conn = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false, // Desactiva la conexión persistente
                    PDO::ATTR_EMULATE_PREPARES => false // Usa consultas preparadas reales en MySQL
                ]);

                if (self::$debug) {
                    echo "<br>Conexión a la base de datos establecida con PDO.";
                }
            } catch (PDOException $e) {
                $timestamp = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual
                $errorMessage = "[{$timestamp}] Error en la conexión: " . $e->getMessage() . "\n";
                error_log($errorMessage, 3, self::$logFile);
                echo "<br>Error en la conexión a la base de datos.";
                exit;
            }
        }
        return self::$conn;
    }

    /**
     * Prepara una consulta SQL
     *
     * @param string $sql
     * @return PDOStatement
     */
    public static function prepare($sql) {
        $conn = self::getConnection(); // Obtiene la conexión
        return $conn->prepare($sql); // Prepara y retorna la consulta
    }

    /**
     * Inicia una transacción en la base de datos
     *
     * @return void
     */
    public static function beginTransaction() {
        if (self::$conn && !self::$conn->inTransaction()) {
            self::$conn->beginTransaction();
            if (self::$debug) {
                echo "<br>Transacción iniciada.";
            }
        }
    }
    
    /**
     * Confirma la transacción en la base de datos
     *
     * @return void
     */
    public static function commit() {
        if (self::$conn && self::$conn->inTransaction()) {
            self::$conn->commit();
            if (self::$debug) {
                echo "<br>Transacción confirmada.";
            }
        }
    }

    /**
     * Cancela la transacción en la base de datos
     *
     * @return void
     */
    public static function rollBack() {
        if (self::$conn && self::$conn->inTransaction()) {
            self::$conn->rollBack();
            if (self::$debug) {
                echo "<br>Transacción revertida.";
            }
        }
    }

    /**
     * Cierra la conexión a la base de datos
     *
     * @return void
     */
    public static function closeConnection() {
        if (isset(self::$conn)) {
            self::$conn = null;
            if (self::$debug) {
                echo "<br>Conexión a la base de datos cerrada.";
            }
        }
    }

    public function imprimirConsulta($sql, $params, $msj) {
        foreach ($params as $key => $value) {
            // Escapar los valores para evitar inyecciones de SQL
            $value = is_null($value) ? 'NULL' : "'".addslashes($value)."'";
            $sql = preg_replace('/' . preg_quote($key, '/') . '/', $value, $sql, 1);
        }
        echo '<br>Consulta '.$msj.':'. $sql.'<br>';
    }
}
