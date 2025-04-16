<?php

namespace App\Models;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class Menu_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Menu_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function obtenerIdAreaPorLink($areaLink){
        if (self::$debug) {
            echo "Nombre de Area a buscar: $areaLink <br>";
        }

        $sql = "SELECT id FROM segareas WHERE link = :areaLink AND estatus = 1 LIMIT 1";
        if (self::$debug) {
            $params = [':areaLink' => $areaLink];
            $this->db->imprimirConsulta($sql, $params, 'Verifica si el usuario sigue Activo.');
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':areaLink', $areaLink);
            $stmt->execute();

            $idArea = $stmt->fetch(PDO::FETCH_ASSOC);
            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($idArea);
                echo '<br><br>';
            }
            if ($idArea) {
                return ['success' => true, 'data' => $idArea['id']]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No se detecto el Area.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No se encontro el id del Area.Notifica a tu Administrador'];
            }
        } catch (\PDOException $e) {
            // Registro del error
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] Error al buscar Id del Area (nombre: $areaLink): " . $e->getMessage(), 3, LOG_FILE_BD);
    
            return [
                'success' => false,
                'message' => 'Error al buscar Id del Area. Notifica a tu Administrador'
            ];
        }
    }

    public function obtenerEstructuraMenu($nivel, $idArea)
    {
        if (self::$debug) {
            echo "Nivel Recibido: $nivel <br> Area Recibida: $idArea <br>";
        }
        try {

            $sql = "SELECT 
                        m.id AS menu_id,
                        m.nombre AS menu_nombre,
                        m.descripcion AS menu_descripcion,
                        m.icono AS menu_icono,
                        m.link AS menu_link,
                        m.orden AS menu_orden,
                        m.tipo AS menu_tipo,
                        m.visible AS menu_visible,
                        
                        sm1.id AS submenu1_id,
                        sm1.nombre AS submenu1_nombre,
                        sm1.descripcion AS submenu1_descripcion,
                        sm1.icono AS submenu1_icono,
                        sm1.link AS submenu1_link,
                        sm1.orden AS submenu1_orden,
                        sm1.visible AS submenu1_visible,
                        
                        sm2.id AS submenu2_id,
                        sm2.nombre AS submenu2_nombre,
                        sm2.descripcion AS submenu2_descripcion,
                        sm2.icono AS submenu2_icono,
                        sm2.link AS submenu2_link,
                        sm2.orden AS submenu2_orden,
                        sm2.visible AS submenu2_visible,
                        
                        sm3.id AS submenu3_id,
                        sm3.nombre AS submenu3_nombre,
                        sm3.descripcion AS submenu3_descripcion,
                        sm3.icono AS submenu3_icono,
                        sm3.link AS submenu3_link,
                        sm3.orden AS submenu3_orden,
                        sm3.visible AS submenu3_visible

                    FROM segdetnivel dn
                    INNER JOIN segareas a ON a.id = dn.idArea AND a.estatus = 1
                    INNER JOIN segmenus m ON m.id = dn.idMenu AND m.estatus = 1
                    LEFT JOIN segsubmenus sm1 ON sm1.id = dn.idSubMenu AND sm1.estatus = 1
                    LEFT JOIN segsubmenu2 sm2 ON sm2.id = dn.idSubMenu2 AND sm2.estatus = 1
                    LEFT JOIN segsubmenu3 sm3 ON sm3.id = dn.idSubMenu3 AND sm3.estatus = 1

                    WHERE 
                        dn.idNivel = :nivel AND dn.idArea = :idArea

                    ORDER BY 
                        a.orden, m.orden, sm1.orden, sm2.orden, sm3.orden";
            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [':nivel' => $nivel, ':idArea' => $idArea];
                $this->db->imprimirConsulta($sql, $params, 'Obtener detalle de menú:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nivel', $nivel, \PDO::PARAM_INT);
            $stmt->bindParam(':idArea', $idArea, \PDO::PARAM_INT);
            $stmt->execute();

            $dataMenu = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataMenu);
                echo '<br><br>';
            }

            if ($dataMenu) {
                return ['success' => true, 'data' => $dataMenu]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "El Usuario no tiene ninguna pagina a donde direccionarlo.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No tiene permisos a nada.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Menu_Mdl.php -> Error al obtener datos para el menú: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al obtener el menú. Notifica a tu administrador'];
        }
    }

    public function listarAreasDisponibles($nivel){
        if (self::$debug) {
            echo "Nivel Recibido: $nivel <br>";
        }
        try {

            $sql = "SELECT DISTINCT(dtnvl.idArea) AS identArea, ars.*, 
                        m.id AS menu_id, m.nombre AS menu_nombre, m.link AS menu_link
                    FROM segdetnivel dtnvl
                    INNER JOIN segareas ars ON dtnvl.idArea = ars.id
                    INNER JOIN segmenus m ON dtnvl.idMenu = m.id AND m.estatus = 1 AND m.visible = 1
                    WHERE dtnvl.idNivel = :nivel
                    GROUP BY dtnvl.idArea
                    ORDER BY ars.orden DESC, m.orden ASC";
            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [':nivel' => $nivel];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Areas a las que tiene Acceso:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nivel', $nivel, \PDO::PARAM_INT);
            $stmt->execute();

            $dataAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataAreas);
                echo '<br><br>';
            }

            if ($dataAreas) {
                return ['success' => true, 'data' => $dataAreas]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "El Usuario no tiene ninguna pagina a donde direccionarlo.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No tiene permisos a nada.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Menu_Mdl.php -> Error al listar las Areas de acceso: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al obtener las Areas. Notifica a tu administrador'];
        }
    }
}
