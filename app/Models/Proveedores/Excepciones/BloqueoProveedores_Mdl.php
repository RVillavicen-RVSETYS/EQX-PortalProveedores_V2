<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class BloqueoProveedores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase BloqueoProveedores_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    /* CONSULTAS DE SELECT */
    public function verificarBloqueo($idProveedor)
    {
        if (self::$debug) {
            echo "Ya entro a la función para verificar el bloqueo.<br>";
        }
        try {

            $sql = "SELECT
                        prov.id AS 'IdProveedor',
                        prov.nombre AS 'Proveedor',
                        provFact.id AS 'IdBloqueo',
                        provFact.idProveedor AS 'IdProvBloqueo',
                        provFact.estatus AS 'EstatusBloqueo'
                    FROM
                        proveedores prov
                        LEFT JOIN conf_provFactSiempre provFact ON prov.id = provFact.idProveedor
                    WHERE
                        prov.id = :idProveedor";
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Verificar Bloqueo:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idProveedor', $idProveedor, PDO::PARAM_INT);
            $stmt->execute();

            $dataResul = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                if (self::$debug) {
                    echo "Error Al Obtener Datos Del Periodo De Bloqueo.<br>";
                }
                return ['success' => false, 'message' => 'No Existe Bloqueo De Proveedor'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php -> Error Al Obtener Datos Del Periodo De Bloqueo: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Datos Del Periodo De Bloqueo. Notifica a tu administrador'];
        }
    }

    public function obtenerPeriodoBloqueo()
    {
        if (self::$debug) {
            echo "Ya entro a la función para buscar la fecha de cierre de año.<br>";
        }
        try {

            $sql = "SELECT
                        cca.id AS 'Id',
                        DATE_FORMAT( cca.fechaInicio, '%d-%m-%Y' ) AS 'FechaIni',
	                    DATE_FORMAT( cca.fechaFin, '%d-%m-%Y' ) AS 'FechaFin',
                        cca.mensajeCierre AS 'MsjEsp',
                        cca.mensajeCierreIng AS 'MsjIng' 
                    FROM
                        conf_cierreAnio cca 
                    WHERE
                        cca.estatus = 1";
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Periodo De Bloqueo:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $dataResul = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($dataResul);
                echo '<br><br>';
            }

            if ($dataResul) {
                return ['success' => true, 'data' => $dataResul];
            } else {
                if (self::$debug) {
                    echo "Aun No Se Registra Un Cierre Anual.<br>";
                }
                return ['success' => false, 'message' => 'Aun No Se Registra Un Cierre Anual.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php -> Error Al Obtener Cierre Anual: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Cierre Anual. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE INSERT - conf_provFactSiempre */
    public function registraBloqueoProveedor($campos)
    {
        if (self::$debug) {
            echo "Ya entro a la función para registrar un nuevo Bloqueo de Proveedor.<br>";
        }

        $camposValidos = [
            'idProveedor' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'idProveedor = :idProveedor'
            ],
            'nombre' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'nombre = :nombre'
            ],
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ],
            'grupo' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'grupo = :grupo'
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

            // Validar y construir INSERT
            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    $invalidCampos[] = $campo;
                } else {
                    // Validar valores permitidos si existen
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

            // Agregar fechaReg automáticamente
            $columnParts[] = 'fechaReg';
            $valueParts[] = 'NOW()';

            // Si hay errores de validación, lanza excepción con detalles
            if (!empty($invalidCampos)) {
                throw new \Exception('Campos no válidos: ' . implode(', ', $invalidCampos) . '.');
            }

            // Construir SQL - Si no viene nombre, usar SELECT para obtenerlo del proveedor
            if (isset($campos['idProveedor']) && !isset($campos['nombre'])) {
                $sql = "INSERT INTO conf_provFactSiempre (idProveedor, nombre, estatus, idUserReg, fechaReg) 
                        SELECT prov.id AS 'IdProveedor', prov.nombre AS 'Proveedor', :estatus, :idUserReg, NOW() 
                        FROM proveedores prov 
                        WHERE prov.id = :idProveedor";
            } else {
                $sql = "INSERT INTO conf_provFactSiempre (" . implode(', ', $columnParts) . ") VALUES (" . implode(', ', $valueParts) . ")";
            }

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Registrar Nuevo Bloqueo de Proveedor');
            }

            $stmt = $this->db->prepare($sql);

            // Bind de parámetros
            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                
                if (isset($camposValidos[$clave])) {
                    $tipoDato = $camposValidos[$clave]['tipoDato'];
                    if ($value === null) {
                        $stmt->bindValue($param, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue(
                            $param,
                            $value,
                            $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR
                        );
                    }
                }
            }

            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas == 1) {
                return [
                    'success' => true,
                    'message' => 'Datos actualizados correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                if (self::$debug) {
                    echo "Error Al Agregar Proveedor.<br>";
                }
                return [
                    'success' => false,
                    'message' => 'Error Al Agregar Proveedor.',
                    'filasAfectadas' => $filasAfectadas
                ];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Agregar Proveedor: " . $e->getMessage();
            }
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }

    /* CONSULTAS DE UPDATE - conf_provFactSiempre */
    public function actualizarBloqueoProveedor($campos, $filtros)
    {
        if (self::$debug) {
            echo "Ya entro a la función para actualizar un Bloqueo de Proveedor.<br>";
        }

        $camposValidos = [
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ],
            'grupo' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'grupo = :grupo'
            ]
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id'],
            'idProveedor' => ['tipoDato' => 'INT', 'sqlQuery' => 'idProveedor = :idProveedor']
        ];

        try {
            if (!is_array($campos) || !is_array($filtros)) {
                throw new \Exception('Los campos y filtros deben ser arreglos.');
            }

            if (count($campos) == 0) {
                throw new \Exception('Los campos no pueden estar vacíos.');
            }

            if (count($filtros) == 0) {
                throw new \Exception('Los filtros no pueden estar vacíos.');
            }

            $params = [];
            $invalidCampos = [];
            $invalidFiltros = [];
            $setParts = [];
            $whereParts = [];

            // Validar y construir SET
            foreach ($campos as $campo => $valor) {
                if (!array_key_exists($campo, $camposValidos)) {
                    $invalidCampos[] = $campo;
                } else {
                    // Validar valores permitidos si existen
                    if (isset($camposValidos[$campo]['permitidos'])) {
                        if (!in_array((string)$valor, $camposValidos[$campo]['permitidos'], true)) {
                            throw new \Exception($camposValidos[$campo]['mensajeError'] ?? "Valor inválido para el campo $campo.");
                        }
                    }
                    $setParts[] = $camposValidos[$campo]['sqlQuery'];
                    // Solo agregar a params si el sqlQuery usa parámetros (contiene ':')
                    if (strpos($camposValidos[$campo]['sqlQuery'], ':') !== false) {
                        $params[":$campo"] = $valor;
                    }
                }
            }

            // Validar y construir WHERE
            foreach ($filtros as $filtro => $valor) {
                if (!array_key_exists($filtro, $filtrosValidos)) {
                    $invalidFiltros[] = $filtro;
                } else {
                    $whereParts[] = $filtrosValidos[$filtro]['sqlQuery'];
                    $params[":$filtro"] = $valor;
                }
            }

            // Si hay errores de validación, lanza excepción con detalles
            if (!empty($invalidCampos) || !empty($invalidFiltros)) {
                throw new \Exception(
                    (!empty($invalidCampos) ? 'Campos no válidos: ' . implode(', ', $invalidCampos) . '. ' : '') .
                    (!empty($invalidFiltros) ? 'Filtros no válidos: ' . implode(', ', $invalidFiltros) . '.' : '')
                );
            }

            // Construir SQL usando implode
            $sql = "UPDATE conf_provFactSiempre SET " . implode(', ', $setParts) .
                   " WHERE " . implode(' AND ', $whereParts);

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Bloqueo Proveedor');
            }

            $stmt = $this->db->prepare($sql);

            // Bind de parámetros
            foreach ($params as $param => $value) {
                $clave = trim($param, ':');
                
                // Determinar el tipo de dato
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
                        $stmt->bindValue(
                            $param,
                            $value,
                            $tipoDato === 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR
                        );
                    }
                }
            }

            $stmt->execute();
            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas >= 1) {
                return [
                    'success' => true,
                    'message' => 'Datos actualizados correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                if (self::$debug) {
                    echo "No se actualizó ningún registro.<br>";
                }
                return [
                    'success' => false,
                    'message' => 'No se actualizó ningún registro.',
                    'filasAfectadas' => $filasAfectadas
                ];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php ->Error al actualizar Bloqueo Proveedor: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al actualizar Bloqueo Proveedor: " . $e->getMessage();
            }
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }

    /* MÉTODOS PARA conf_cierreAnio - Mantener como están */
    public function desactivarCierreAnual()
    {
        try {

            $sql = "UPDATE conf_cierreAnio SET estatus = 0";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Desactivar Cierre Anual Anterior.<br>');
            }
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($resultado);
                echo '<br><br>';
            }

            if ($resultado) {
                return ['success' => true, 'message' => 'Cierre Anual Desactivado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Desactivar Cierre Anual.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Desactivar Cierre Anual.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php ->Error Al Desactivar Cierre Anual: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Desactivar Cierre Anual: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas Al Desactivar El Cierre Anual, Notifica a tu administrador.'];
        }
    }

    public function registraCierreAnual($campos)
    {
        if (self::$debug) {
            echo "Ya entro a la función para registrar un nuevo Cierre Anual.<br>";
        }

        $camposValidos = [
            'fechaInicio' => ['tipoDato' => 'STRING', 'sqlQuery' => 'fechaInicio = :fechaInicio'],
            'fechaFin' => ['tipoDato' => 'STRING', 'sqlQuery' => 'fechaFin = :fechaFin'],
            'mensajeCierre' => ['tipoDato' => 'STRING', 'sqlQuery' => 'mensajeCierre = :mensajeCierre'],
            'mensajeCierreIng' => ['tipoDato' => 'STRING', 'sqlQuery' => 'mensajeCierreIng = :mensajeCierreIng'],
            'estatus' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1'
            ],
            'idUserReg' => ['tipoDato' => 'INT', 'sqlQuery' => 'idUserReg = :idUserReg']
        ];

        try {
            if (!is_array($campos)) {
                throw new \Exception('Los campos deben ser un arreglo.');
            }

            if (count($campos) == 0) {
                throw new \Exception('Los campos no pueden estar vacíos.');
            }

            // Primero desactivar cierres anuales anteriores
            $desactivar = $this->desactivarCierreAnual();
            if ($desactivar['success'] != true) {
                return ['success' => false, 'message' => 'Error al desactivar el cierre anual anterior.'];
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

            // Agregar campos por defecto si no vienen en $campos
            if (!isset($campos['estatus'])) {
                $columnParts[] = 'estatus';
                $valueParts[] = '1';
            }

            $columnParts[] = 'fechaReg';
            $valueParts[] = 'NOW()';

            if (!empty($invalidCampos)) {
                throw new \Exception('Campos no válidos: ' . implode(', ', $invalidCampos) . '.');
            }

            $tabla = 'conf_cierreAnio';
            $sql = "INSERT INTO {$tabla} (" . implode(', ', $columnParts) . ") VALUES (" . implode(', ', $valueParts) . ")";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Registrar Cierre Anual');
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
                return [
                    'success' => true,
                    'message' => 'Cierre anual registrado correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar el cierre anual.',
                    'filasAfectadas' => $filasAfectadas
                ];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/BloqueoProveedores_Mdl.php ->Error Al Registrar Cierre Anual: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }
}
