<?php

namespace App\Models\Proveedores\Excepciones;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once __DIR__ . '/../../../config/BD_Connect.php';
}

class Alertas_Mdl
{
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Alertas_Mdl.</h2>";
        }
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos
    }

    /* CONSULTAS DE SELECT */
    public function obtenerListaAlertas()
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la lista de Alertas.<br>";
        }
        try {

            $sql = "SELECT
                        cnp.id AS 'IdNotificacion',
                        cnp.tipoProveedor AS 'TipoProveedor',
                        cnp.titulo AS 'Titulo',
                        cnp.mensaje AS 'Mensaje',
                        cnp.tipoMensaje AS 'TipoMsj',
                        cnp.periodo AS 'TipoPeriodo',
                        DATE_FORMAT( cnp.fechaInicio, '%d-%m-%Y' ) AS 'Inicio',
	                    DATE_FORMAT( cnp.fechaFin, '%d-%m-%Y' ) AS 'Fin',
                        cnp.estatus AS 'Estatus' 
                    FROM
                        conf_notificaProveedor cnp";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Lista De Alertas:');
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
                    echo "Error Al Obtener Alertas.<br>";
                }
                return ['success' => false, 'message' => 'No Se Obtuvieron Las Alertas.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/Alertas_Mdl.php -> Error al listar las Alertas: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Alertas. Notifica a tu administrador'];
        }
    }

    public function cargaDatos($idNotificacion)
    {
        if (self::$debug) {
            echo "Ya entro a la función para obtener la información de una Alerta.<br>";
        }
        try {

            $sql = "SELECT
                        cnp.id AS 'IdNotificacion',
                        cnp.tipoProveedor AS 'TipoProveedor',
                        cnp.titulo AS 'Titulo',
                        cnp.mensaje AS 'Mensaje',
                        cnp.tipoMensaje AS 'TipoMsj',
                        cnp.periodo AS 'TipoPeriodo',
                        DATE_FORMAT( cnp.fechaInicio, '%Y-%m-%d' ) AS 'Inicio',
	                    DATE_FORMAT( cnp.fechaFin, '%Y-%m-%d' ) AS 'Fin',
                        cnp.estatus AS 'Estatus' 
                    FROM
                        conf_notificaProveedor cnp
                    WHERE 
                        cnp.id = :idNotificacion;";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [
                    ':idNotificacion' => $idNotificacion,
                ];
                $this->db->imprimirConsulta($sql, $params, 'Obtener Información De Una Alerta:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idNotificacion', $idNotificacion, PDO::PARAM_INT);
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
                    echo "Error Al Obtener Datos De La Alerta.<br>";
                }
                return ['success' => false, 'message' => 'Error Al Obtener Datos De La Alerta.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/Alertas_Mdl.php -> Error Al Obtener Información De Una Alerta: " . $e->getMessage(), 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Obtener Información De Alertas. Notifica a tu administrador'];
        }
    }

    /* CONSULTAS DE INSERT */
    public function registraNotificaProveedor($campos)
    {
        if (self::$debug) {
            echo "Ya entro a la función para registrar una nueva Alerta.<br>";
        }

        $camposValidos = [
            'tipoProveedor' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'tipoProveedor = :tipoProveedor'
            ],
            'titulo' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'titulo = :titulo'
            ],
            'mensaje' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'mensaje = :mensaje'
            ],
            'tipoMensaje' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'tipoMensaje = :tipoMensaje'
            ],
            'periodo' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'periodo = :periodo'
            ],
            'fechaInicio' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaInicio = :fechaInicio'
            ],
            'fechaFin' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaFin = :fechaFin'
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

            // Construir SQL
            $sql = "INSERT INTO conf_notificaProveedor (" . implode(', ', $columnParts) . ") VALUES (" . implode(', ', $valueParts) . ")";

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Registrar Nueva Alerta');
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
                    'message' => 'Alerta creada correctamente.',
                    'filasAfectadas' => $filasAfectadas
                ];
            } else {
                if (self::$debug) {
                    echo "Error Al Crear Alerta.<br>";
                }
                return [
                    'success' => false,
                    'message' => 'Error Al Crear Alerta.',
                    'filasAfectadas' => $filasAfectadas
                ];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/Alertas_Mdl.php ->Error Al Crear Alerta: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error Al Crear Alerta: " . $e->getMessage();
            }
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }

    /* CONSULTAS DE UPDATE */
    public function actualizarNotificaProveedor($campos, $filtros)
    {
        if (self::$debug) {
            echo "Ya entro a la función para actualizar una Alerta.<br>";
        }

        $camposValidos = [
            'tipoProveedor' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'tipoProveedor = :tipoProveedor'
            ],
            'titulo' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'titulo = :titulo'
            ],
            'mensaje' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'mensaje = :mensaje'
            ],
            'tipoMensaje' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'tipoMensaje = :tipoMensaje'
            ],
            'periodo' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'periodo = :periodo'
            ],
            'fechaInicio' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaInicio = :fechaInicio'
            ],
            'fechaFin' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaFin = :fechaFin'
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
            ],
            'fechaReg' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaReg = NOW()'
            ]
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id']
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
            $sql = "UPDATE conf_notificaProveedor SET " . implode(', ', $setParts) .
                   " WHERE " . implode(' AND ', $whereParts);

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Notifica Proveedor');
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
                    'message' => 'Alerta actualizada correctamente.',
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
            error_log("[$timestamp] app/Models/Proveedores/Excepciones/Alertas_Mdl.php ->Error al actualizar Alerta: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al actualizar Alerta: " . $e->getMessage();
            }
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }
}
