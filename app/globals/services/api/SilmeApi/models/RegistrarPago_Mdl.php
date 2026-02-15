<?php

namespace App\Globals\Services\Api\SilmeApi\Models;

use PDO;
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
    require_once '../config/BD_Connect.php';
}

class RegistrarPago_Mdl
{
    private $db;
    private static $debug = 0;

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase RegistrarPago_Mdel.</h2>";
        }
        $this->db = new BD_Connect();
    }

    public function ejecutarActualizaEstatus($idAcuse)
    {
        if (self::$debug) {
            echo "<strong>Entrando a ejecutarActualizaEstatus()</strong><br>";
        }

        if (empty($idAcuse)) {
            return [
                'success' => false,
                'message' => 'No se recibió el IdAcuse.',
            ];
        }

        $resultado = [
            'success' => false,
            'message' => ''
        ];

        try {

            // 1. Iniciar transacción
            $this->db->beginTransaction();

            // 2. Preparar CALL
            $sql = "CALL sp_oper_Compras_ActualizaEstatus(?)";

            if (self::$debug) {
                echo "<br><strong>Consulta a ejecutar:</strong><br>";
                $this->db->imprimirConsulta($sql, [$idAcuse], 'CALL Actualiza Estatus');
                echo "<br>";
            }

            // 3. Ejecutar
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idAcuse]);

            // MUY IMPORTANTE en procedimientos almacenados
            $stmt->closeCursor();

            // 4. Commit
            $this->db->commit();

            $resultado['success'] = true;
            $resultado['message'] = "Estatus actualizado correctamente.";
        } catch (\PDOException $e) {

            $this->db->rollBack();

            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] ejecutarActualizaEstatus -> Error: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);

            if (self::$debug) {
                echo "<strong>Error PDO:</strong> " . $e->getMessage() . "<br>";
            }

            $resultado['success'] = false;
            $resultado['message'] = 'Error al actualizar el estatus. Notifica a tu administrador';
        }

        return $resultado;
    }

    public function insertaPagos(array $pagos)
    {
        if (self::$debug) {
            echo "<strong>Entrando a insertaPagos()</strong><br>";
        }

        if (empty($pagos)) {
            return [
                'success' => false,
                'message' => 'No hay datos para insertar.',
                'insertados' => 0
            ];
        }

        $resultado = [
            'success' => false,
            'insertados' => 0,
            'message' => ''
        ];

        try {

            // 1. Iniciar transacción
            $this->db->beginTransaction();

            // 2. Armar SQL base
            $sql = "INSERT INTO pagos_compras ( idPagoDet, idAcuse, OC, HES, montoPagado, saldoInsoluto, moneda, tipoCambio, montoTipoCambio, monedaTipoCambio, formaPago, formaPagoSAT, fechaPago, fechaReg ) VALUES ";

            $placeholders = [];
            $values = [];
            $idsAcuse = [];

            foreach ($pagos as $index => $pago) {

                $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $values[] = $pago['IdPagoDet'];
                $values[] = $pago['IdAcuse'];
                $values[] = $pago['OC'];
                $values[] = $pago['HES'];
                $values[] = $pago['MontoPagado'];
                $values[] = $pago['SaldoInsoluto'];
                $values[] = $pago['Moneda'];
                $values[] = $pago['TipoCambio'];
                $values[] = $pago['MontoTipoCambio'];
                $values[] = $pago['MonedaTipoCambio'];
                $values[] = $pago['FormaPago'];
                $values[] = $pago['FormaPagoSAT'];
                $values[] = $pago['FechaPago'];

                // Guardar idAcuse para actualizar después
                if (!empty($pago['IdAcuse'])) {
                    $idsAcuse[] = $pago['IdAcuse'];
                }

                if (self::$debug) {
                    echo "Pago {$index}: " . json_encode($pago) . "<br>";
                }
            }

            // 3. Unir placeholders
            $sql .= implode(',', $placeholders);

            // 4. Debug de consulta
            if (self::$debug) {
                echo "<br><strong>Consulta a ejecutar:</strong><br>";
                $this->db->imprimirConsulta($sql, $values, 'Bulk Insert Pagos API');
                echo "<br>";
            }

            // 5. Ejecutar
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo "<strong>Filas afectadas:</strong> {$filasAfectadas}<br><br>";
            }

            $idsAcuseUnicos = array_unique($idsAcuse);
            if (!empty($idsAcuseUnicos)) {
                $stmtSP = $this->db->prepare("CALL sp_oper_Compras_ActualizaEstatus(?)");
                foreach ($idsAcuseUnicos as $id) {
                    if (self::$debug) {
                        echo "Actualizando estatus compra ID: {$id}<br>";
                    }
                    $stmtSP->execute([$id]);
                    $stmtSP->closeCursor(); // MUY IMPORTANTE
                }
            }

            // 6. Commit
            $this->db->commit();

            $resultado['success'] = true;
            $resultado['insertados'] = $filasAfectadas;
            $resultado['message'] = "Se insertaron {$filasAfectadas} pagos correctamente.";
        } catch (\PDOException $e) {

            // 7. Rollback
            $this->db->rollBack();
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] RegistrarPago_Mdl.php -> Error Bulk Insert: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);

            if (self::$debug) {
                echo "<strong>Error PDO:</strong> " . $e->getMessage() . "<br>";
            }

            $resultado['success'] = false;
            $resultado['message'] = 'Error al insertar pagos. Notifica a tu administrador';
            $resultado['insertados'] = 0;
        }

        return $resultado;
    }



    public function insertaPago($IdPago, $IdDetPago, $OrdenCompra, $HojaEntrada, $MontoPago, $SaldoInsoluto, $Moneda, $FormaPago, $FechaPago)
    {
        if (self::$debug) {
            echo "Ya entro a la función para insertar el pago.<br>";
        }
        try {

            $sql = "INSERT INTO pagos_compras (idPago, idDetPago, OC, HES, montoPagado, saldoInsoluto, moneda, formaPago, fechaPago)
                    VALUES (:idPago, :idDetPago, :OC, :HES, :montoPagado, :saldoInsoluto, :moneda, :formaPago, :fechaPago)";

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $params = [
                    ':idPago' => $IdPago,
                    ':idDetPago' => $IdDetPago,
                    ':OC' => $OrdenCompra,
                    ':HES' => $HojaEntrada,
                    ':montoPagado' => $MontoPago,
                    ':saldoInsoluto' => $SaldoInsoluto,
                    ':moneda' => $Moneda,
                    ':formaPago' => $FormaPago,
                    ':fechaPago' => $FechaPago
                ];
                $this->db->imprimirConsulta($sql, $params, 'Insertar Pago:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idPago', $IdPago, \PDO::PARAM_INT);
            $stmt->bindParam(':idDetPago', $IdDetPago, \PDO::PARAM_INT);
            $stmt->bindParam(':OC', $OrdenCompra, \PDO::PARAM_STR);
            $stmt->bindParam(':HES', $HojaEntrada, \PDO::PARAM_STR);
            $stmt->bindParam(':montoPagado', $MontoPago, \PDO::PARAM_STR);
            $stmt->bindParam(':saldoInsoluto', $SaldoInsoluto, \PDO::PARAM_STR);
            $stmt->bindParam(':moneda', $Moneda, \PDO::PARAM_STR);
            $stmt->bindParam(':formaPago', $FormaPago, \PDO::PARAM_STR);
            $stmt->bindParam(':fechaPago', $FechaPago, \PDO::PARAM_STR);
            $stmt->execute();

            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas > 0) {
                return ['success' => true, 'data' => 'Pago Insertado Correctamente.'];
            } else {
                if (self::$debug) {
                    echo "Error Al Insertar Pago.<br>";
                }
                return ['success' => false, 'message' => 'No Se Puedo Insertar El Pago.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] globals/services/api/SilmeApi/models/RegistrarPago_Mdl.php -> Error Al Insertar Pago: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error Al Insertar Pago. Notifica a tu administrador'];
        }
    }
}
