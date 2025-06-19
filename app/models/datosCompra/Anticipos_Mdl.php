<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_ConnectHES; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';

class Anticipos_Mdl
{
    private $dbHES;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase Anticipos_Mdl.</h2>";
        }

        $this->dbHES = new BD_ConnectHES(); // Instancia de la conexión a la base de datos
    }

    public function verificaAnticipoDeOrdenCompra($filtros = [], INT $cantMaxRes = 0, $orden = 'DESC')
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
            error_log("[$timestamp] app/Models/datosCompra/Anticipos_Mdl.php ->Error buscar Compras por Proveedor: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "<br>Error al listar Notas Credito: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al listar las Notas De Credito, Notifica a tu administrador.'];
        }
    }

    public function verificaAnticipo($Anticipo, $noProveedor)
    {
        try {
            $sql = "SELECT COUNT(id_OC) AS cantAnticipos
              FROM vw_ext_PortalProveedores_HESporPagar
              WHERE idProveedor = :noProveedor AND OrdenCompra = :anticipo";

            if (self::$debug) {
                $params = [
                    ':anticipo' => $Anticipo,
                    ':noProveedor' => $noProveedor
                ];
                $this->dbHES->imprimirConsulta($sql, $params, 'Busca Anticipo.');
            }
            $stmt = $this->dbHES->prepare($sql);
            $stmt->bindParam(':anticipo', $Anticipo, PDO::PARAM_STR);
            $stmt->bindParam(':noProveedor', $noProveedor, PDO::PARAM_INT);
            $stmt->execute();
            $cantAnticipos = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($cantAnticipos);
                echo '<br><br>';
            }

            if ($cantAnticipos["cantAnticipos"] > 0) {
                return ['success' => true, 'data' => $cantAnticipos];
            } else {
                if (self::$debug) {
                    echo "No hay ningun Anticipo.<br>"; // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'No existe ese Codigo de Anticipo.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/datosCompra/Anticipos_Mdl.php ->Error contar HES de la OC: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error al buscar Anticipo: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas con tu Anticipo, Notifica a tu administrador.'];
        }
    }
}
