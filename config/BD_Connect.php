<?php

namespace Config;

use mysqli;

class BD_Connect
{
    private $host = '82.165.209.227';
    private $user = 'RVSetysTest';
    private $password = 'RV53ty5.p4$$wd';
    private $database = "EQX_PortalProveedoresV2";
    private $link;
    private static $instance = null;

    private function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $this->link = new mysqli($this->host, $this->user, $this->password, $this->database);

        if ($this->link->connect_error) {
            die("Error al conectar a la base de datos: " . $this->link->connect_error);
        }

        // Configuraciones adicionales
        $this->link->query("SET NAMES 'utf8mb4'");
        $this->link->query("SET time_zone = '-06:00'");
        $this->link->query("SET lc_time_names = 'es_MX'");
        $this->link->query("SET @@global.wait_timeout=300");

        // Iniciar transacción automáticamente si es necesario
        $this->link->begin_transaction();
    }

    public static function getConnection()
    {
        if (self::$instance === null) {
            self::$instance = new BD_Connect();
        }
        return self::$instance->link;
    }

    // Función para realizar commit
    public function commit()
    {
        $this->link->commit();
    }

    // Función para realizar rollback
    public function rollback()
    {
        $this->link->rollback();
    }

    // Función para cerrar la conexión
    public function close()
    {
        if ($this->link) {
            $this->link->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
