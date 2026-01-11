<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class IgnoraDescuento_Mdl
{
    private $db;
    private static $debug = 0;
    private $tabla = 'conf_provIgnoraDescuento';
    private $logFile = 'ignoraDescuento.log';

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase IgnoraDescuento_Mdl.</h2>";
        }
        $this->db = new BD_Connect();
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

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Ignorados Con Descuento:');
            }

            $stmt = $this->db->prepare($sql);
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
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/IgnoraDescuento_Mdl.php -> Error Al Obtener Proveedores Ignorados: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores Ignorados. Notifica a tu administrador'];
        }
    }

    public function getProveedores()
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
                        LEFT JOIN {$this->tabla} confProv ON prov.id = confProv.idProveedor 
                    WHERE
                        confProv.idProveedor IS NULL";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores:');
            }

            $stmt = $this->db->prepare($sql);
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
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/IgnoraDescuento_Mdl.php -> Error Al Obtener Proveedores: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Proveedores. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE INSERT */
    public function registraIgnoraDescuento($campos)
    {
        if (self::$debug) {
            echo "Ya entro a la función para registrar un nuevo Proveedor que Ignora Descuento.<br>";
        }

        $camposValidos = [
            'idProveedor' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'idProveedor = :idProveedor'
            ],
            'motivo' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'motivo = :motivo'
            ],
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ],
            'idUserReg' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'idUserReg = :idUserReg'
            ]
        ];

        try {
            if (!is_array($campos)) {
                throw new \Exception('Los campos deben ser un arreglo.');
            }

            if (count($campos) == 0) {
                throw new \Exception('Los campos no pueden estar vacíos.');
            }

            $params = [];
            $invalidCampos = [];
            $columnParts = [];
            $valueParts = [];

            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    $invalidCampos[] = $campo;
                } else {
                    if (isset($camposValidos[$campo]['permitidos'])) {
                        if (!in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                            throw new \Exception($camposValidos[$campo]['mensajeError'] ?? "Valor inválido para el campo $campo.");
                        }
                    }
                    $columnParts[] = $campo;
                    $valueParts[] = ":{$campo}";
                    $params[":{$campo}"] = $valor;
                }
            }

            $columnParts[] = 'fechaReg';
            $valueParts[] = 'NOW()';

            if (!empty($invalidCampos)) {
                throw new \Exception('Campos no válidos: ' . implode(', ', $invalidCampos) . '.');
            }

            $sql = "INSERT INTO {$this->tabla} (" . implode(', ', $columnParts) . ") VALUES (" . implode(', ', $valueParts) . ")";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Registrar Proveedor que Ignora Descuento');
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                if (isset($camposValidos[$clave])) {
                    $tipoDato = $camposValidos[$clave]['tipoDato'];
                    if ($value === null) {
                        $stmt->bindValue($param, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
                    }
                }
            }

            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas == 1) {
                // Log
                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/' . $this->logFile;
                $motivo = $campos['motivo'] ?? '';
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/IgnoraDescuento_Mdl.php -> Se Agregó El Proveedor: " . $campos['idProveedor'] . " Con Motivo: '$motivo', IdUserReg: " . ($campos['idUserReg'] ?? 0) . PHP_EOL, 3, $logFile);

                return [
                    'success' => true,
                    'message' => 'Proveedor agregado correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error Al Agregar Proveedor.',
                    'filasAfectadas' => $filasAfectadas
                ];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/IgnoraDescuento_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function actualizarIgnoraDescuento($campos, $filtros)
    {
        if (self::$debug) {
            echo "Ya entro a la función para actualizar un Proveedor que Ignora Descuento.<br>";
        }

        $camposValidos = [
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ]
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id']
        ];

        try {
            if (!is_array($campos) || !is_array($filtros)) {
                throw new \Exception('Los campos y filtros deben ser arreglos.');
            }

            if (count($campos) == 0 || count($filtros) == 0) {
                throw new \Exception('Los campos y filtros no pueden estar vacíos.');
            }

            $params = [];
            $invalidCampos = [];
            $invalidFiltros = [];
            $setParts = [];
            $whereParts = [];

            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    $invalidCampos[] = $campo;
                } else {
                    if (isset($camposValidos[$campo]['permitidos'])) {
                        if (!in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                            throw new \Exception($camposValidos[$campo]['mensajeError'] ?? "Valor inválido para el campo $campo.");
                        }
                    }
                    $setParts[] = $camposValidos[$campo]['sqlQuery'];
                    if (strpos($camposValidos[$campo]['sqlQuery'], ':') !== false) {
                        $params[":$campo"] = $valor;
                    }
                }
            }

            foreach ($filtros as $filtro => $valor) {
                if (!array_key_exists($filtro, $filtrosValidos)) {
                    $invalidFiltros[] = $filtro;
                } else {
                    $whereParts[] = $filtrosValidos[$filtro]['sqlQuery'];
                    $params[":$filtro"] = $valor;
                }
            }

            if (!empty($invalidCampos) || !empty($invalidFiltros)) {
                throw new \Exception(
                    (!empty($invalidCampos) ? 'Campos no válidos: ' . implode(', ', $invalidCampos) . '. ' : '') .
                    (!empty($invalidFiltros) ? 'Filtros no válidos: ' . implode(', ', $invalidFiltros) . '.' : '')
                );
            }

            $sql = "UPDATE {$this->tabla} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Ignora Descuento');
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                $tipoDato = null;
                if (isset($camposValidos[$clave])) {
                    $tipoDato = $camposValidos[$clave]['tipoDato'];
                } elseif (isset($filtrosValidos[$clave])) {
                    $tipoDato = $filtrosValidos[$clave]['tipoDato'];
                }

                if ($tipoDato) {
                    if ($value === null) {
                        $stmt->bindValue($param, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue($param, $value, $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR);
                    }
                }
            }

            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if ($filasAfectadas >= 1) {
                // Obtener idProveedor para el log
                $sqlSelect = "SELECT idProveedor FROM {$this->tabla} WHERE id = :id LIMIT 1";
                $stmtSelect = $this->db->prepare($sqlSelect);
                $stmtSelect->bindValue(':id', $filtros['id'], PDO::PARAM_INT);
                $stmtSelect->execute();
                $resultSelect = $stmtSelect->fetch(PDO::FETCH_ASSOC);
                $idProveedor = $resultSelect['idProveedor'] ?? 0;

                // Log
                $timestamp = date("Y-m-d H:i:s");
                $year = date("Y");
                $logDir = LOG_SYSTEM . 'excepciones/' . $year;
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . '/' . $this->logFile;
                $estatus = ($campos['estatus'] == 1) ? 'Activó' : 'Desactivó';
                error_log("[$timestamp] app/Models/Proveedores/Excepciones/IgnoraDescuento_Mdl.php -> Se " . $estatus . " El Proveedor: " . $idProveedor . ", IdUserReg: " . ($_SESSION['EQXident'] ?? 0) . PHP_EOL, 3, $logFile);

                return [
                    'success' => true,
                    'message' => 'Estatus cambiado correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se actualizó ningún registro.',
                    'filasAfectadas' => $filasAfectadas
                ];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/IgnoraDescuento_Mdl.php ->Error al actualizar: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }
}
