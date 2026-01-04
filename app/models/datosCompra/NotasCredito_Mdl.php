<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_ConnectHES; // Asegúrate de que la conexión esté disponible
use BD_Connect; // Conexión a la BD principal para cfdi_notasCreditos

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';
require_once __DIR__ . '/../../../config/BD_Connect.php';

class NotasCredito_Mdl
{
    private $dbHES;
    private $db; // Conexión a BD principal para actualizar cfdi_notasCreditos
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase NotasCredito_Mdl.</h2>";
        }

        $this->dbHES = new BD_ConnectHES(); // Instancia de la conexión a la base de datos HES (para vistas)
        $this->db = new BD_Connect(); // Instancia de la conexión a la base de datos principal (para cfdi_notasCreditos)
    }

    public function verificaNotaCreditoDeOrdenCompra($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
    {

        self::$debug = 0; // Cambiar a 0 para desactivar mensajes de depuración
        if (self::$debug) {
            echo '<br><br>Filtros Recibidos: ';
            var_dump($filtros);
        }
        $filtrosDisponibles = [
            'folioCompra' => ['tipoDato' => 'STRING', 'sqlFiltro' => 'FolioCompra = :folioCompra']
        ];

        $filtrosSQL = '';
        $params = [];

        try {
            if (!is_int($cantMaxRes)) {
                throw new \Exception('El valor de $cantMaxRes debe ser un entero.');
            }
            $limiteResult = ($cantMaxRes == 0) ? '' : 'LIMIT ' . $cantMaxRes;

            if (!in_array($orden, ['DESC', 'ASC'])) {
                throw new \Exception('El orden debe ser DESC o ASC.');
            } else {
                $orden = strtoupper($orden);
            }

            foreach ($filtros as $nombreFiltro => $valorFiltro) {
                if (isset($filtrosDisponibles[$nombreFiltro]) && $valorFiltro !== null) {
                    switch ($nombreFiltro) {
                        case 'entreFechasRecepcion':
                            list($fechaInicial, $fechaFinal) = explode(',', $valorFiltro);
                            if (!strtotime($fechaInicial) || !strtotime($fechaFinal)) {
                                throw new \Exception('Las fechas proporcionadas no son válidas.');
                            }
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':fechaInicial'] = $fechaInicial;
                            $params[':fechaFinal'] = $fechaFinal;
                            break;

                        default:
                            $filtrosSQL .= ' AND ' . $filtrosDisponibles[$nombreFiltro]['sqlFiltro'];
                            $params[':' . $nombreFiltro] = $valorFiltro;
                            break;
                    }
                }
            }

            if (empty($filtrosSQL)) {
                throw new \Exception('No se encontró ningún parámetro válido.');
            }
            $filtrosSQL = ltrim($filtrosSQL, ' AND');

            if (self::$debug) {
                echo '<br><br>Parametros: ';
                var_dump($params);
                echo '<br><br>';
            }

            $sql = "SELECT * FROM vw_ext_PortalProveedores_NotasCredito WHERE $filtrosSQL";

            if (self::$debug) {
                $this->dbHES->imprimirConsulta($sql, $params, 'Lista Notas Credito');
            }
            $stmt = $this->dbHES->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $anticiposResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la cantidad de registros
            $cantResult = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($anticiposResult);
                echo '<br><br>';
            }

            return ['success' => true, 'cantAnticipos' => $cantResult, 'data' => $anticiposResult];
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            $errorMessage = $e->getMessage();
            error_log("[$timestamp] app/Models/DatosCompra/NotasCredito_Mdl.php ->Error en verificaNotaCreditoDeOrdenCompra: " . $errorMessage, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Notas Credito: " . $errorMessage; // Mostrar error en modo depuración
            }
            // Retornar mensaje más específico si es posible
            $mensajeUsuario = 'Problemas al listar las Notas De Credito, Notifica a tu administrador.';
            if (strpos($errorMessage, 'No se encontró ningún parámetro válido') !== false) {
                $mensajeUsuario = 'No se proporcionaron parámetros válidos para buscar las Notas de Crédito.';
            } elseif (strpos($errorMessage, 'SQLSTATE') !== false || strpos($errorMessage, 'SQL') !== false) {
                $mensajeUsuario = 'Error de conexión con la base de datos al buscar Notas de Crédito.';
            }
            return ['success' => false, 'message' => $mensajeUsuario];
        }
    }

    public function obtenerPoliticaPorIdNotaCredito(int $idNotaCredito)
    {
        self::$debug = 0; // Silencioso por defecto
        if (self::$debug) {
            echo "<br>Buscando política de Nota de Crédito con ID: $idNotaCredito<br>";
        }

        try {
            if (empty($idNotaCredito)) {
                throw new \Exception('El ID de la política no puede estar vacío.');
            }

            // Usamos la vista del ERP para obtener los datos de la política
            $sql = "SELECT * FROM vw_ext_PortalProveedores_NotasCredito WHERE IdNotaCredito = :idNotaCredito LIMIT 1";
            
            $params = [':idNotaCredito' => $idNotaCredito];

            if (self::$debug) {
                $this->dbHES->imprimirConsulta($sql, $params, 'Obtener Política NC por ID');
            }

            $stmt = $this->dbHES->prepare($sql);
            $stmt->bindValue(':idNotaCredito', $idNotaCredito, PDO::PARAM_INT);
            $stmt->execute();
            
            $politicaResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $cantResult = $stmt->rowCount();

            if ($cantResult > 0) {
                if (self::$debug) {
                    echo '<br>Política encontrada:';
                    var_dump($politicaResult);
                }
                return ['success' => true, 'data' => $politicaResult];
            } else {
                if (self::$debug) {
                    echo '<br>No se encontró la política.';
                }
                return ['success' => false, 'message' => 'No se encontró una política de nota de crédito con el ID proporcionado.'];
            }

        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/DatosCompra/NotasCredito_Mdl.php -> Error en obtenerPoliticaPorId: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al obtener la política de NC: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas al obtener la política de Nota de Crédito, notifica a tu administrador.'];
        }
    }

    public function actualizarNotaCredito($campos = [], $filtros = [])
    {
        $camposValidos = [
            'estatus' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'estatus = :estatus',
                'permitidos' => ['0', '1', '2', '3'],
                'mensajeError' => 'Estatus inválido. Permitidos: 0,1,2,3'
            ],
            'urlPDF' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'urlPDF = :urlPDF'
            ],
            'urlXML' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'urlXML = :urlXML'
            ],
            'validacionEFOS' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'validacionEFOS = :validacionEFOS'
            ],
            'detalleValidaciónEFOS' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'detalleValidaciónEFOS = :detalleValidaciónEFOS'
            ],
            'codigoEstatusValida' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'codigoEstatusValida = :codigoEstatusValida'
            ],
            'estadoValida' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'estadoValida = :estadoValida'
            ],
            'idUserValida' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'idUserValida = :idUserValida'
            ],
            'fechaValida' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaValida = :fechaValida'
            ],
            'idUserRechaza' => [
                'tipoDato' => 'INT',
                'sqlQuery' => 'idUserRechaza = :idUserRechaza'
            ],
            'fechaRechaza' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'fechaRechaza = :fechaRechaza'
            ],
            'motivoRechazo' => [
                'tipoDato' => 'STRING',
                'sqlQuery' => 'motivoRechazo = :motivoRechazo'
            ]
        ];

        $filtrosValidos = [
            'id' => ['tipoDato' => 'INT', 'sqlQuery' => 'id = :id'],
            'uuid' => ['tipoDato' => 'STRING', 'sqlQuery' => 'uuid = :uuid'],
            'idCompra' => ['tipoDato' => 'INT', 'sqlQuery' => 'idCompra = :idCompra']
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
                    $params[":$campo"] = $valor;
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
            $sql = "UPDATE cfdi_notasCreditos SET " . implode(', ', $setParts) .
                   " WHERE " . implode(' AND ', $whereParts);

            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $params, 'Actualizar Nota de Crédito');
            }

            $stmt = $this->db->prepare($sql);

            // Bind de parámetros
            foreach ($params as $param => $value) {
                $clave = trim($param, ':'); // Elimina ":" del nombre del parámetro
                
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
                    'message' => 'Nota de crédito actualizada correctamente.',
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
            error_log("[$timestamp] app/Models/DatosCompra/NotasCredito_Mdl.php ->Error al actualizar Nota de Crédito: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al actualizar Nota de Crédito: " . $e->getMessage();
            }
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'filasAfectadas' => 0
            ];
        }
    }
}
