<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class DescuentoProveedores_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase DescuentoProveedores_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    /* CONSULTAS DE SELECT */
    public function listaProveedores()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener el select de proveedores que no tienen descuento.<br>";
        }
        try {

            $sql = "SELECT
                            prov.id AS 'IdProveedor',
                            prov.nombre AS 'Proveedor' 
                        FROM
                            proveedores prov
                        WHERE
                            prov.id NOT IN 
                            (SELECT pfd.idProveedor FROM conf_provFactDescuento pfd)";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Con Descuento:');
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
                    echo "Error Al Obtener Lista De Proveedores Con Descuento.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener Lista De Proveedores Con Descuento.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/DescuentoProveedores_Mdl.php -> Error al listar Proveedores Con Descuento: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al listar Proveedores Con Descuento. Notifica a tu administrador'];
        }
    }

    public function provedoresConDesc()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de Proveedores con Descuento.<br>";
        }
        try {

            $sql = "SELECT
                        cpfd.id AS 'Id',
                        cpfd.idProveedor AS 'IdProveedor',
                        cpfd.nombre AS 'Proveedor',
                        cpfd.estatus AS 'Estatus' 
                    FROM
                        conf_provFactDescuento cpfd";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Proveedores Con Descuento:');
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
                    echo "Error Al Obtener Lista De Proveedores Con Descuento.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener Lista De Proveedores Con Descuento.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/DescuentoProveedores_Mdl.php -> Error al listar Proveedores Con Descuento: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al listar Proveedores Con Descuento. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE INSERT */
    public function registraDescuentoProveedor($campos)
    {
        if (self::$debug) {
            echo "Ya entro a la función para registrar un nuevo Proveedor con Descuento.<br>";
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

            // Si hay errores de validación, lanza excepción con detalles
            if (!empty($invalidCampos)) {
                throw new \Exception('Campos no válidos: ' . implode(', ', $invalidCampos) . '.');
            }

            // Construir SQL - Usar SELECT para obtener nombre del proveedor
            $sql = "INSERT INTO conf_provFactDescuento (idProveedor, nombre, estatus) 
                    SELECT prov.id AS 'IdProveedor', prov.nombre AS 'Proveedor', :estatus AS 'Estatus' 
                    FROM proveedores prov 
                    WHERE prov.id = :idProveedor";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Registrar Nuevo Proveedor con Descuento');
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
                    'message' => 'Proveedor agregado correctamente.',
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
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/DescuentoProveedores_Mdl.php ->Error Al Agregar Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
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

    /* CONSULTAS DE UPDATE */
    public function actualizarDescuentoProveedor($campos, $filtros)
    {
        if (self::$debug) {
            echo "Ya entro a la función para actualizar un Proveedor con Descuento.<br>";
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
            $sql = "UPDATE conf_provFactDescuento SET " . implode(', ', $setParts) .
                   " WHERE " . implode(' AND ', $whereParts);

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Descuento Proveedor');
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
                    'message' => 'Estatus cambiado correctamente.',
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
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/DescuentoProveedores_Mdl.php ->Error al actualizar Descuento Proveedor: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al actualizar Descuento Proveedor: " . $e->getMessage();
            }
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }
}
