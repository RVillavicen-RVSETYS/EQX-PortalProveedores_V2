<?php

namespace App\Models\Empresas;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_Connect.php';

class Empresas_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Empresas_Mdl.</h2>";
        }
        
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function listaEmpresas($estatus = 1)
    {
        $filtraEstatus = ($estatus == 'ALL') ? '' : "WHERE  e.estatus = '$estatus'";
        try {
            if ($estatus != 1 && $estatus != 0 && $estatus != 'ALL') {
                return [
                    'success' => false,
                    'message' => 'El estatus no es correcto.'
                ];
            }

            $sql = "SELECT *
                    FROM empresas e
                    $filtraEstatus
                    ORDER BY e.nombre ASC"; 

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, 'NA', 'Lista ultimas Compras');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $empresasresult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantEmpresas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($empresasresult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantRes' => $cantEmpresas, 'data' => $empresasresult]; 
            
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/empresas/Empresas_Mdl.php ->Error buscar Empresas: " . $e->getMessage(), 3, LOG_FILE_BD); 
            if (self::$debug) {
                echo "Error al listar Empresas: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar las Empresas, Notifica a tu administrador.'];
        }
    }

    public function empresaPorId(INT $idEmpresa)
    {
        if (empty($idEmpresa)) {
            return ['success' => false, 'message' => 'Se requiere Id de Empresa.'];
        } else {
            try {
                $sql = "SELECT *
                        FROM empresas e
                        WHERE e.id = :idempresa"; 
    
                if (self::$debug) {
                    $params = [':idempresa' => $idEmpresa];
                    $this->db->imprimirConsulta($sql, $params, 'Datos de Empresa');
                }
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':idempresa', $idEmpresa, PDO::PARAM_INT);
                $stmt->execute();
                $empresasresult = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if (self::$debug) {
                    echo '<br>Resultado de Query:';
                    var_dump($empresasresult);
                    echo '<br><br>';
                }
    
                return ['success' => true, 'data' => $empresasresult]; 
                
            } catch (\Exception $e) {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app/Models/empresas/Empresas_Mdl.php ->Error buscar Empresas por Id: " . $e->getMessage(), 3, LOG_FILE_BD); 
                if (self::$debug) {
                    echo "Error al bucar Empresa: " . $e->getMessage(); // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Problemas al buscar la Empresa, Notifica a tu administrador.'];
            }
        }        
    }
}