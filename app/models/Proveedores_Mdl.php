<?php

namespace App\Models;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible
use BD_ConnectHES;

// Incluye conección a la BD
require_once '../config/BD_Connect.php';
require_once '../config/BD_ConnectHES.php';

class Proveedores_Mdl
{
    private $db;
    private $dbHes;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase ControlProveedores_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
        $this->dbHes = new BD_ConnectHES();
    }

    /* CONSULTAS DE SELECT */
    public function obtenerProveedores()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores <br>";
        }
        try {

            $sql = "SELECT prov.id AS 'IdProveedor', prov.nombre AS 'Proveedor' FROM vw_data__Proveedores_AccesoProveedores prov WHERE prov.estatus = 1";

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
                    echo "Error Al Obtener Proveedores Activos.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Los Proveedores.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/ControlProveedores_Mdl.php -> Error al listar las Areas de acceso: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores. Notifica a tu administrador'];
        }
    }

    public function obtenerDatosProveedor($idProveedor)
    {
        try {
            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        prov.rfc AS 'RFC',
                        prov.correo AS 'Correo',
                        prov.pais AS 'Pais',
                        prov.cpag AS 'CPag' 
                    FROM
                        proveedores prov 
                    WHERE
                        prov.id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Obtiene Los Datos De Un Proveedor.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $dataProveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataProveedor);
                echo '<br><br>';
            }

            if ($dataProveedor) {
                return ['success' => true, 'data' => $dataProveedor]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No Hay Ningún Proveedor Con Ese Nombre.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No Hay Ningún Proveedor Con Ese Nombre..'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Obtener Datos Del Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error Al Obtener Datos Del Proveedor: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas Con El Proveedor, Notifica a tu administrador.'];
        }
    }

    public function obtenerMonedasProveedores()
    {
        try {
            $sql = "SELECT DISTINCT(idCatTipoMoneda) AS 'Moneda' FROM compras";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Busca Los Tipos De Monedas En Las Compras.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dataProveedor = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataProveedor);
                echo '<br><br>';
            }

            if ($dataProveedor) {
                return ['success' => true, 'data' => $dataProveedor]; // Retornar datos si tiene permisos a algun Area
            } else {
                if (self::$debug) {
                    echo "No Se Encontró Ningún Tipo De Moneda.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No Se Encontró Ningún Tipo De Moneda..'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Obtener Tipos De Monedas: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error Al Obtener Tipos De Monedas: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas Al Obtener Monedas, Notifica a tu administrador.'];
        }
    }

    public function buscaProveedoresSilme()
    {
        try {
            $sql = "SELECT
                    prov.id AS 'IdProveedor',
                    prov.nombre AS 'Proveedor',
                    prov.correoPagos AS 'Correo',
                    prov.rfc AS 'RFC',
                    prov.telEmpresa AS 'telEmpresa',
                    prov.idUserReg AS 'IdUserReg',
                    prov.tieneCredito AS 'TieneCredito',
                    prov.idSAT_pais AS 'Pais',
                    prov.idSAT_moneda AS 'Moneda',
                    prov.estatus AS 'Estatus',
                    prov.direccion AS 'Direccion',
                    prov.cp AS 'CP',
                    prov.idCatEstado AS 'Estado',
                    prov.idCatMunicipio AS 'Municipio',
                    prov.tipoLimite AS 'CPag'
                FROM
                    proveedores prov";

            if (self::$debug) {
                $params = [];
                $this->dbHes->imprimirConsulta($sql, $params, 'Busca Los Proveedores En SilmeAgro.');
            }
            $stmt = $this->dbHes->prepare($sql);
            $stmt->execute();
            $dataProveedorSilme = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataProveedorSilme);
                echo '<br><br>';
            }

            if ($dataProveedorSilme) {
                return ['success' => true, 'data' => $dataProveedorSilme];
            } else {
                if (self::$debug) {
                    echo "No Se Obtuvieron Proveedores.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Proveedores..'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Obtener Los Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error Al Obtener Los Proveedores: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas Al Obtener Proveedores, Notifica a tu administrador.'];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function actualizaRFC($idProveedor, $nuevoRFC)
    {
        try {
            $sql = "UPDATE proveedores 
                    SET rfc = :newRFC 
                    WHERE id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':newRFC' => $nuevoRFC,
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Actualiza El RFC.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':newRFC', $nuevoRFC, PDO::PARAM_STR);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'RFC Actualizado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar El RFC.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar El RFC.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Actualizar El RFC Del Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar El RFC Del Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Proveedor, Notifica a tu administrador.'];
        }
    }

    public function actualizaCorreo($idProveedor, $nuevoCorreo)
    {
        try {
            $sql = "UPDATE proveedores 
                    SET correo = :newCorreo 
                    WHERE id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':newCorreo' => $nuevoCorreo,
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Actualiza El Correo.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':newCorreo', $nuevoCorreo, PDO::PARAM_STR);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Correo Actualizado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar El Correo.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar El Correo.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Actualizar El Correo Del Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar El Correo Del Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Proveedor, Notifica a tu administrador.'];
        }
    }

    public function actualizaPassword($idProveedor, $nuevaPass)
    {
        try {
            $sql = "UPDATE proveedores
                    SET pass = :newPass
                    WHERE id = :idProveedor";

            if (self::$debug) {
                $params = [
                    ':newPass' => $nuevaPass,
                    ':idProveedor' => $idProveedor
                ];
                $this->db->imprimirConsulta($sql, $params, 'Actualiza La Contraseña.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':newPass', $nuevaPass, PDO::PARAM_STR);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return ['success' => true, 'data' => 'Contraseña Actualizada Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar La Contraseña.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar La Contraseña.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Actualizar La Contraseña Del Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar La Contraseña Del Proveedor: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con El Proveedor, Notifica a tu administrador.'];
        }
    }

    public function actualizaProveedoresTemp()
    {
        try {

            $dataProveedorSilme = $this->buscaProveedoresSilme();

            if ($dataProveedorSilme['success']) {

                $sql = "INSERT INTO tmpproveedores ( id, nombre, direccion, pais, cp, rfc, cpag, moneda, correo, estatus)
                VALUES ( :idProveedor, :nombre, :direccion, :pais, :cp, :rfc, :cpag, :moneda, :correo, :estatus)
                ON DUPLICATE KEY UPDATE
                    nombre = VALUES(nombre),
                    direccion = VALUES(direccion),
                    pais = VALUES(pais),
                    cp = VALUES(cp),
                    rfc = VALUES(rfc),
                    cpag = VALUES(cpag),
                    moneda = VALUES(moneda),
                    correo = VALUES(correo),
                    estatus = VALUES(estatus);";

                if (self::$debug) {
                    foreach ($dataProveedorSilme['data'] as $proveedor) {
                        $params = [
                            ':idProveedor' => $proveedor['IdProveedor'],
                            ':nombre' => $proveedor['Proveedor'],
                            ':direccion' => $proveedor['Direccion'],
                            ':pais' => $proveedor['Pais'],
                            ':cp' => $proveedor['CP'],
                            ':rfc' => $proveedor['RFC'],
                            ':cpag' => $proveedor['CPag'],
                            ':moneda' => $proveedor['Moneda'],
                            ':correo' => $proveedor['Correo'],
                            ':estatus' => $proveedor['Estatus']
                        ];
                        $this->db->imprimirConsulta($sql, $params, 'Actualiza La Lista De Proveedores.<br>');
                    }
                }

                $stmt = $this->db->prepare($sql);
                $cant = 0;
                foreach ($dataProveedorSilme['data'] as $proveedor) {
                    $stmt->bindParam(':idProveedor', $proveedor['IdProveedor'], PDO::PARAM_INT);
                    $stmt->bindParam(':nombre', $proveedor['Proveedor'], PDO::PARAM_STR);
                    $stmt->bindParam(':direccion', $proveedor['Direccion'], PDO::PARAM_STR);
                    $stmt->bindParam(':pais', $proveedor['Pais'], PDO::PARAM_INT);
                    $stmt->bindParam(':cp', $proveedor['CP'], PDO::PARAM_STR);
                    $stmt->bindParam(':rfc', $proveedor['RFC'], PDO::PARAM_STR);
                    $stmt->bindParam(':cpag', $proveedor['CPag'], PDO::PARAM_STR);
                    $stmt->bindParam(':moneda', $proveedor['Moneda'], PDO::PARAM_STR);
                    $stmt->bindParam(':correo', $proveedor['Correo'], PDO::PARAM_STR);
                    $stmt->bindParam(':estatus', $proveedor['Estatus'], PDO::PARAM_INT);
                    $stmt->execute();

                    $cant++;
                }

                $filasAfectadas = $cant;

                if (self::$debug) {
                    echo '<br>Resultado de Query:';
                    var_dump($filasAfectadas);
                    echo '<br><br>';
                }

                if ($filasAfectadas >= 1) {
                    return ['success' => true, 'data' => 'Lista Actualizada Correctamente.'];
                } else {
                    if (self::$debug) {
                        echo "Error Al Actualizar La Lista De Proveedores.<br>";
                    }
                    return ['success' => false, 'message' => 'Error Al Actualizar La Lista De Proveedores.'];
                }
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Actualizar La Lista De Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar La Lista De Proveedores: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con La Conexión, Notifica a tu administrador.'];
        }
    }

    public function actualizaProveedores()
    {
        try {
            $sql = "INSERT INTO proveedores ( id, nombre, direccion, pais, cp, rfc, cpag, moneda, correo, estatus)
                    SELECT id, nombre, direccion, pais, cp, rfc, cpag, moneda, correo, estatus FROM tmpproveedores
                    ON DUPLICATE KEY UPDATE
                    nombre = VALUES(nombre),
                    direccion = VALUES(direccion),
                    pais = VALUES(pais),
                    cp = VALUES(cp),
                    rfc = VALUES(rfc),
                    cpag = VALUES(cpag),
                    moneda = VALUES(moneda),
                    correo = VALUES(correo),
                    estatus = VALUES(estatus);";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Actualiza La Contraseña.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas >= 1) {
                return ['success' => true, 'data' => 'Lista Actualizada Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Actualizar La Lista De Proveedores.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Actualizar La Lista De Proveedores.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Actualizar La Lista De Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar La Lista De Proveedores: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con La Conexión, Notifica a tu administrador.'];
        }
    }
}
