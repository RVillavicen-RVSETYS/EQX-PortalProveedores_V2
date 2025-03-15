<?php

namespace App\Models\Proveedores;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_Connect; // Asegúrate de que la conexión esté disponible
use BD_ConnectHES;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}

require_once __DIR__ . '/../../../config/BD_Connect.php';
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';

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

    public function obtenerProveedoresBloqueados()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores bloqueados <br>";
        }
        try {

            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        provFact.id AS 'IdBloqueo',
                        provFact.estatus AS 'EstatusBloqueo',
                        provFact.grupo AS 'GrupoBloqueo',
                        provFact.idUserReg AS 'IdUserRegBloqueo',
                        provFact.fechaReg AS 'FechaRegBloqueo',
                        prov.correo AS 'Correo'
                    FROM
                        vw_data_Proveedores_AccesoProveedores prov
                        LEFT JOIN conf_provFactSiempre provFact ON prov.id = provFact.idProveedor 
                    WHERE
                        prov.estatus = 1";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Bloqueados:');
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

    public function obtenerProveedores()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de proveedores <br>";
        }
        try {

            $sql = "SELECT prov.id AS 'IdProveedor', prov.nombre AS 'Proveedor', prov.razonSocial AS 'RazonSocial' FROM proveedores prov WHERE prov.estatus = 1";

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
                        prov.cpag AS 'CPag',
                        prov.razonSocial AS 'RazonSocial',
                        CONCAT_WS('-',prov.regimenFiscal, sat.descripcion) AS 'RegimenFiscal'
                    FROM
                        proveedores prov
                        INNER JOIN sat_catRegimenFiscal sat ON prov.regimenFiscal = sat.id
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
                prov.direccion AS 'Direccion',
                prov.pais AS 'Pais',
                prov.cp AS 'CP',
                prov.grupo AS 'Grupo',
                prov.rfc AS 'RFC',
                prov.cpag AS 'CPag',
                prov.idSAT_moneda AS 'Moneda',
                prov.correoPagos AS 'Correo',
                prov.estatus AS 'Estatus',
                prov.idioma AS 'Idioma',
                prov.razonSocial AS 'RazonSocial',
                prov.regimenFiscal AS 'RegimenFiscal' 
            FROM
                vw_ext_PortalProveedores_Proveedores prov
            WHERE
                prov.nombre IS NOT NULL
                AND prov.nombre <> ''
                AND prov.rfc IS NOT NULL
                AND prov.rfc <> ''
                AND prov.idioma IS NOT NULL
                AND prov.idioma <> '';";

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

    public function obtenerRecepcionesSinFactura($idProveedor)
    {
        try {
            $sql = "SELECT
                        * 
                    FROM
                        (
                        SELECT
                            erpHes.idCompra AS 'ErpIdCompra',
                            erpHes.OC AS 'OC',
                            erpHes.HES AS 'HES',
                            erpHes.subtotal AS 'Monto',
                            erpHes.idMoneda AS 'Moneda',
                            erpHes.CPago AS 'CP',
                            dtcomp.idCompra AS 'DtIdCompra' 
                        FROM
                            silmeagro_erpV1.vw_ext_PortalProveedores_MontosHES erpHes
                            LEFT JOIN EQX_PortalProveedoresV2.detcompras dtcomp ON erpHes.idCompra = dtcomp.idCompra 
                        WHERE
                            erpHes.idProveedor = :idProveedor 
                        ) Datos 
                    WHERE
                        Datos.DtIdCompra IS NULL
                    ORDER BY
                        Datos.ErpIdCompra ASC";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor
                ];
                $this->dbHes->imprimirConsulta($sql, $params, 'Busca Las Recepciones Sin Factura');
            }
            $stmt = $this->dbHes->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $dataRecepcionesSinF = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataRecepcionesSinF);
                echo '<br><br>';
            }

            if ($dataRecepcionesSinF) {
                return ['success' => true, 'data' => $dataRecepcionesSinF];
            } else {
                if (self::$debug) {
                    echo "No Se Obtuvieron Recepciones Sin Facturas.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Recepciones Sin Facturas..'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Obtener Las Recepciones Sin Facturas: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error Al Obtener Las Recepciones Sin Facturas: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas Al Obtener Recepciones Sin Facturas, Notifica a tu administrador.'];
        }
    }

    public function obtenerFacturasSinFechaPago($idProveedor)
    {
        try {
            $sql = "";

            if (self::$debug) {
                $params = [
                    ':idProveedor' => $idProveedor
                ];
                $this->dbHes->imprimirConsulta($sql, $params, 'Busca Las Facturas Sin Fecha De Pago');
            }
            $stmt = $this->dbHes->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $dataRecepcionesSinF = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataRecepcionesSinF);
                echo '<br><br>';
            }

            if ($dataRecepcionesSinF) {
                return ['success' => true, 'data' => $dataRecepcionesSinF];
            } else {
                if (self::$debug) {
                    echo "No Se Obtuvieron Facturas Sin Fecha De Pago.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Facturas Sin Fecha De Pago..'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Obtener Las Facturas Sin Fecha De Pago: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error Al Obtener Las Facturas Sin Fecha De Pago: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas Al Obtener Facturas Sin Fecha De Pago, Notifica a tu administrador.'];
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

    public function exepcionesProveedoresFacturas(INT $idProveedor)
    {
        try {
            // Validación del ID del proveedor
            if (empty($idProveedor) || !is_numeric($idProveedor)) {
                return ['success' => false, 'message' => 'El ID del proveedor no es válido.'];
            }

            // Consulta SQL
            $sql = "
                SELECT 
                    p.id AS ProveedorID, 
                    IFNULL(iDesc.idProveedor, 0) AS IgnoraDescuento, 
                    IFNULL(exanio.idProveedor, 0) AS AnioFiscal, 
                    IFNULL(exemi.idProveedor, 0) AS FechaEmision, 
                    IFNULL(ucd.idProveedor, 0) AS UsoCfdiDistinto, 
                    ucd.usoCfdi AS UsoCfdi, 
                    IFNULL(bd.idProveedor, 0) AS BloqDiferenciaMonto
                FROM proveedores p 
                LEFT JOIN conf_provIgnoraDescuento iDesc ON p.id = iDesc.idProveedor
                LEFT JOIN conf_provExentoAnoFisc exanio ON p.id = exanio.idProveedor AND exanio.estatus = '1'
                LEFT JOIN conf_provExentoFechaEmision exemi ON p.id = exemi.idProveedor AND exemi.estatus = '1'
                LEFT JOIN conf_provUsoCfdiDistinto ucd ON p.id = ucd.idProveedor AND ucd.estatus = '1'
                LEFT JOIN conf_provBloqDiferencias bd ON p.id = bd.idProveedor 
                WHERE p.id = :idProveedor
            ";

            $params = [':idProveedor' => $idProveedor];

            // Debug de la consulta antes de ejecutarla
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Consulta para excepciones de proveedor');
            }

            // Preparar y ejecutar la consulta
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();

            // Obtener resultados
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug de los resultados
            if (self::$debug) {
                echo '<br><strong>Resultado de la consulta:</strong><br>';
                var_dump($result);
                echo '<br><br>';
            }

            // Verificar si hay datos
            if (!$result) {
                return ['success' => false, 'message' => 'No se encontraron datos para el proveedor especificado.'];
            }

            // Formatear los datos para la respuesta
            $responseData = [
                'IgnoraDescuento' => $result['IgnoraDescuento'] == $idProveedor,
                'AnioFiscal' => $result['AnioFiscal'] == $idProveedor,
                'FechaEmision' => $result['FechaEmision'] == $idProveedor,
                'UsoCfdiDistinto' => $result['UsoCfdiDistinto'] == $idProveedor,
                'UsoCfdi' => $result['UsoCfdi'] ?? null,
                'BloqDiferenciaMonto' => $result['BloqDiferenciaMonto'] == $idProveedor
            ];

            return ['success' => true, 'message' => 'Todo OK', 'data' => $responseData];
        } catch (\Exception $e) {
            // Registrar error
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/proveedores/Proveedores_Mdl.php -> Error en exepcionesProveedoresFacturas: " . $e->getMessage(), 3, LOG_FILE_BD);

            // Debug del error
            if (self::$debug) {
                echo '<br><strong>Error encontrado:</strong><br>';
                echo $e->getMessage();
                echo '<br>';
            }

            return ['success' => false, 'message' => 'Problemas al obtener las excepciones del proveedor. Notifica a tu administrador.'];
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

                $pass = '$2y$10$t0KYcBv5n04.VRhkuVOCr.p8VbfkSjnZtQFWyBXVjZqhLoAXxZMFe';

                $sql = "INSERT INTO tmpproveedores ( id, nombre, direccion, pais, cp, grupo, rfc, razonSocial, regimenFiscal, cpag, moneda, pass, correo, estatus, idioma, fechaReg)
                        VALUES ( :idProveedor, :nombre, :direccion, :pais, :cp, :grupo, :rfc, :razonSocial, :regimenFiscal, :cpag, :moneda, :pass, :correo, :estatus, :idioma, NOW())
                        ON DUPLICATE KEY UPDATE
                            nombre = VALUES(nombre),
                            direccion = VALUES(direccion),
                            pais = VALUES(pais),
                            cp = VALUES(cp),
                            grupo = VALUES(grupo),
                            rfc = VALUES(rfc),
                            razonSocial = VALUES(razonSocial),
                            regimenFiscal = VALUES(regimenFiscal),
                            cpag = VALUES(cpag),
                            moneda = VALUES(moneda),
                            correo = VALUES(correo),
                            estatus = VALUES(estatus),
                            idioma = VALUES(idioma),
                            fechaReg = VALUES(fechaReg),
                            pass = pass;";

                if (self::$debug) {
                    foreach ($dataProveedorSilme['data'] as $proveedor) {
                        $params = [
                            ':idProveedor' => $proveedor['IdProveedor'],
                            ':nombre' => $proveedor['Proveedor'],
                            ':direccion' => $proveedor['Direccion'],
                            ':pais' => $proveedor['Pais'],
                            ':cp' => $proveedor['CP'],
                            ':grupo' => $proveedor['Grupo'],
                            ':rfc' => $proveedor['RFC'],
                            ':razonSocial' => $proveedor['RazonSocial'],
                            ':regimenFiscal' => $proveedor['RegimenFiscal'],
                            ':cpag' => $proveedor['CPag'],
                            ':moneda' => $proveedor['Moneda'],
                            ':pass' => $pass,
                            ':correo' => $proveedor['Correo'],
                            ':estatus' => $proveedor['Estatus'],
                            ':idioma' => $proveedor['Idioma']
                        ];
                        $this->db->imprimirConsulta($sql, $params, 'Actualiza La Lista Temporal De Proveedores.<br>');
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
                    $stmt->bindParam(':grupo', $proveedor['Grupo'], PDO::PARAM_INT);
                    $stmt->bindParam(':rfc', $proveedor['RFC'], PDO::PARAM_STR);
                    $stmt->bindParam(':razonSocial', $proveedor['RazonSocial'], PDO::PARAM_STR);
                    $stmt->bindParam(':regimenFiscal', $proveedor['RegimenFiscal'], PDO::PARAM_INT);
                    $stmt->bindParam(':cpag', $proveedor['CPag'], PDO::PARAM_STR);
                    $stmt->bindParam(':moneda', $proveedor['Moneda'], PDO::PARAM_STR);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->bindParam(':correo', $proveedor['Correo'], PDO::PARAM_STR);
                    $stmt->bindParam(':estatus', $proveedor['Estatus'], PDO::PARAM_INT);
                    $stmt->bindParam(':idioma', $proveedor['Idioma'], PDO::PARAM_STR);
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
                    return ['success' => true, 'data' => 'Lista Temporal Actualizada Correctamente.'];
                } else {
                    if (self::$debug) {
                        echo "Error Al Actualizar La Lista Temporal De Proveedores.<br>";
                    }
                    return ['success' => false, 'message' => 'Error Al Actualizar La Lista Temporal De Proveedores.'];
                }
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php ->Error Al Actualizar La Lista Temporal De Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Actualizar La Lista Temporal De Proveedores: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Con La Conexión, Notifica a tu administrador.'];
        }
    }

    public function actualizaProveedores()
    {
        try {
            $sql = "INSERT INTO proveedores ( id, nombre, direccion, pais, cp, rfc, razonSocial, regimenFiscal, grupo, cpag, moneda, pass, correo, estatus, idioma, fechaReg)
                    SELECT tmp.id, tmp.nombre, tmp.direccion, tmp.pais, tmp.cp, tmp.rfc, tmp.razonSocial, tmp.regimenFiscal, tmp.grupo, tmp.cpag, tmp.moneda, tmp.pass, tmp.correo, tmp.estatus, tmp.idioma, tmp.fechaReg
                    FROM tmpproveedores tmp
                    ON DUPLICATE KEY UPDATE
                        nombre = VALUES(nombre),
                        direccion = VALUES(direccion),
                        pais = VALUES(pais),
                        cp = VALUES(cp),
                        rfc = VALUES(rfc),
                        razonSocial = VALUES(razonSocial),
                        regimenFiscal = VALUES(regimenFiscal),
                        grupo = VALUES(grupo),
                        cpag = VALUES(cpag),
                        moneda = VALUES(moneda),
                        correo = VALUES(correo),
                        estatus = VALUES(estatus),
                        idioma = VALUES(idioma),
                        fechaReg = VALUES(fechaReg),
                        pass = proveedores.pass;";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Actualiza La Tabla De Proveedores Original.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 0) {
                return ['success' => true, 'data' => 'La Lista Esta Actualizada, No Se Realizaron Cambios.'];
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

    public function complementosPagoPendientesPorProveedor($idProveedor)
    {
        try {
            $sql = "SELECT 
                        fc.uuid, 
                        fc.monto, 
                        fc.idCatTipoMoneda, 
                        fc.serie, 
                        fc.folio, 
                        fc.fechaReg
                    FROM 
                        compras cp
                        INNER JOIN cfdi_facturas fc ON cp.id = fc.idCompra
                        LEFT JOIN cfdi_complementoPagoDet cpd ON fc.uuid = cpd.uuidFact
                    WHERE 
                        cp.idProveedor = :idProveedor 
                        AND cp.idPago >= 1 
                        AND fc.idCatMetodoPago = 'PPD' 
                        AND ISNULL(cpd.id)";

            if (self::$debug) {
                $params = [':idProveedor' => $idProveedor];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Complementos Pendientes:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $dataResul = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cantData = count($dataResul);


            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                $fechaMasVieja = null;
                foreach ($dataResul as $row) {
                    $fecha = new \DateTime($row['fechaReg']);
                    if ($fechaMasVieja === null || $fecha < $fechaMasVieja) {
                        $fechaMasVieja = $fecha;
                    }
                }

                return ['success' => true, 'data' => $dataResul, 'cantData' => $cantData, 'oldData' => $fechaMasVieja->format('Y-m-d H:i:s')];
            } else {
                if (self::$debug) {
                    echo "No Se Encontraron Complementos Pendientes.<br>";
                }
                return ['success' => true, 'message' => 'No Se Encontraron Complementos Pendientes.', 'cantData' => 0];
            }
        } catch (\PDOException $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/models/Proveedores_Mdl.php -> Error al obtener complementos pendientes: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Complementos Pendientes. Notifica a tu administrador', 'cantData' => 0];
        }
    }
}
