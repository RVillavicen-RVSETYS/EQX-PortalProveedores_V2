<?php

/**
 * Modelo para obtener las credenciales SMTP desde la tabla directorio_correos.
 * Adaptado del ERP SilmeAgroPVT para usar PDO (BD_Connect) en lugar de mysqli.
 * La tabla directorio_correos es compartida entre ambos sistemas (misma BD MySQL).
 */

if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}

require_once __DIR__ . '/../../../../../config/BD_Connect.php';

class Directorio
{
    private $db;
    private $debug;

    public function __construct($debug = 0)
    {
        $this->db = new BD_Connect();
        $this->debug = $debug;
    }

    /**
     * Obtiene los datos de configuración de correo por su ID.
     * Campos retornados: id, hostSMTP, correo, pass
     *
     * @param int $id - ID del registro en directorio_correos
     * @return array|false - Array asociativo con los datos o false si falla
     */
    public function getDetCorreo($id)
    {
        try {
            $sql = "SELECT * FROM directorio_correos WHERE id = :id";

            if ($this->debug) {
                echo "<br>SQL Directorio Correos: " . $sql . " | ID: " . $id . "<br>";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($this->debug) {
                echo "<br>Resultado getDetCorreo:<br>";
                var_dump($resultado);
                echo "<br>";
            }

            if ($resultado) {
                return $resultado;
            } else {
                if ($this->debug) {
                    echo "No se encontró el registro de correo con ID: $id<br>";
                }
                return false;
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] Globals/Services/Correo/Models/Mdl_DirectorioCorreo.php -> Error al obtener credenciales de correo: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE);
            if ($this->debug) {
                echo "Error al obtener correo: " . $e->getMessage() . "<br>";
            }
            return false;
        }
    }
}
