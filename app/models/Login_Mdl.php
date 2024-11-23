<?php

namespace App\Models;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class Login_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    public function verificarProveedor($usuario, $password)
    {
        if (self::$debug) {
            echo "User: $usuario | Pass: $password <br>";
        }
        try {

            $sql = "SELECT pv.id, pv.nombre, pv.pais, pv.poblacion, if(pv.pais = 'MX', 2, 3) AS idNivel, pv.grupo, pv.rfc, pv.cpag,
                    pv.moneda, pv.pass, pv.correo, pv.estatus, if(pv.pais = 'MX', 'Proveedor', 'Supplier') AS nivel_nombre, pv.idioma
                    FROM vw_data__Proveedores_AccesoProveedores pv
                    WHERE pv.id = :usuario "; // Usa un marcador de posición

            if (self::$debug) {
                $params = [':usuario' => $usuario];
                $this->db->imprimirConsulta($sql, $params, 'Busca usuarios');
            }

            $stmt = $this->db->prepare($sql); // Aquí debe existir el método prepare
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR); // Especifica el tipo de dato
            $stmt->execute();
            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($usuarioData);
                echo '<br><br>';
            }
            if ($usuarioData) {
                // Verificar si el usuario está deshabilitado
                if ($usuarioData['estatus'] == 0) {
                    if (self::$debug) {
                        echo "El Usuario esta Deshabilitado.<br>"; // Mostrar error en modo depuración
                    }
                    return ['success' => false, 'message' => 'Usuario deshabilitado'];
                }

                // Verificar si el usuario tiene asignado algun Nivel
                if ($usuarioData['pais'] == 'MX' && empty($usuarioData['rfc'])) {
                    if (self::$debug) {
                        echo "El Proveedor no tiene RFC registrado.<br>"; // Mostrar error en modo depuración
                    }
                    return ['success' => false, 'message' => 'El Proveedor no tiene RFC registrado.'];
                }

                // Verificar si la contraseña es correcta
                if (password_verify($password, $usuarioData['pass'])) {
                    return ['success' => true, 'data' => $usuarioData]; // Retornar datos si la autenticación es exitosa
                } else {
                    if (self::$debug) {
                        echo "La contraseña no corresponde.<br>"; // Mostrar error en modo depuración
                    }
                    return ['success' => false, 'message' => 'Usuario o contraseña Incorrecta.'];
                }
            } else {
                if (self::$debug) {
                    echo "No se encontro el Usuario.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Usuario o contraseña Incorrecta.'];
            }
        } catch (\Exception $e) {
            if (self::$debug) {
                echo "Error en verificarUsuario: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Login_Mdl.php ->Error en verificarUsuario: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            return ['success' => false, 'message' => 'Error al iniciar Session. Notifica a tu Administrador'];
        }
    }

    public function obtenerPrimerArea($idNivel)
    {
        try {
            $sql = "SELECT a.nombre, a.link AS linkArea,     
                    COALESCE(m.link, s1.link, s2.link, s3.link) AS linkMenu
                    FROM segdetnivel d
                    INNER JOIN segareas a ON a.id = d.idArea
                    LEFT JOIN segmenus m ON m.idArea = d.idArea AND m.id = d.idMenu
                    LEFT JOIN segsubmenus s1 ON s1.idSegMenu = m.id AND s1.id = d.idSubMenu
                    LEFT JOIN segsubmenu2 s2 ON s2.idSegSubMenu = s1.id AND s2.id = d.idSubMenu2
                    LEFT JOIN segsubmenu3 s3 ON s3.idSegSubMenu2 = s2.id AND s3.id = d.idSubMenu3
                    WHERE 
                        d.idNivel = :idNivel
                        AND a.estatus = 1 
                        AND m.estatus = 1
                        AND (s1.estatus = 1 OR s1.estatus IS NULL)
                        AND (s2.estatus = 1 OR s2.estatus IS NULL)
                        AND (s3.estatus = 1 OR s3.estatus IS NULL)
                    ORDER BY 
                        a.orden, m.orden, s1.orden, s2.orden, s3.orden
                    LIMIT 1";

            if (self::$debug) {
                $params = [':idNivel' => $idNivel];
                $this->db->imprimirConsulta($sql, $params, 'Busca primer Area para acceso.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idNivel', $idNivel, PDO::PARAM_INT);
            $stmt->execute();
            $areaData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($areaData);
                echo '<br><br>';
            }
            if ($areaData) {
                return ['success' => true, 'data' => $areaData]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "El Usuario no tiene Areas Asignadas.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No tiene permisos Asignados a ningun Area.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Login_Mdl.php ->Error en obtenerPrimerArea: " . $e->getMessage(), 3, LOG_FILE); // Manejo del error
            if (self::$debug) {
                echo "Error en obtenerPrimerArea: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return null; // Retorna null en caso de error
        }
    }

    public function verificarEstatusUsuario($userId)
    {
        if (self::$debug) {
            echo "Id de Usuario: $userId <br>";
        }

        $sql = "SELECT estatus FROM vw_data__Proveedores_AccesoProveedores WHERE id = :userId";
        if (self::$debug) {
            $params = [':userId' => $userId];
            $this->db->imprimirConsulta($sql, $params, 'Verifica si el usuario sigue Activo.');
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($result);
                echo '<br><br>';
            }
            return [
                'active' => $result['estatus'] == 1 // Verifica que el usuario esté activo
            ];
        } catch (\PDOException $e) {
            // Registro del error
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] Error en verificarEstatusUsuario (id: $userId): " . $e->getMessage(), 3, LOG_FILE_BD);
    
            return [
                'success' => false,
                'message' => 'Error al verificar el estatus del usuario. Notifica a tu Administrador'
            ];
        }
    }
}
