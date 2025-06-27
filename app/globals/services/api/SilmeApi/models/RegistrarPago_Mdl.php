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

    public function insertaPagos($pagos)
    {
        if (self::$debug) {
            echo "Entrando a la función para insertar múltiples pagos.<br>";
        }

        if (empty($pagos)) {
            return ['success' => false, 'message' => 'No hay datos para insertar.'];
        }

        try {
            $sql = "INSERT INTO pagos_compras (idPago, idDetPago, OC, HES, montoPagado, saldoInsoluto, moneda, formaPago, fechaPago, idAcuse) VALUES ";

            $values = [];
            $index = 0;

            foreach ($pagos as $pago) {
                $sql .= "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?),";

                $values[] = $pago['IdPago'];
                $values[] = $pago['IdDetPago'];
                $values[] = $pago['OrdenCompra'];
                $values[] = $pago['HojaEntrada'];
                $values[] = $pago['MontoPago'];
                $values[] = $pago['SaldoInsoluto'];
                $values[] = $pago['Moneda'];
                $values[] = $pago['FormaPago'];
                $values[] = $pago['FechaPago'];
                $values[] = $pago['IdAcuse'];

                if (self::$debug) {
                    echo "Registro $index: " . json_encode($pago) . "<br>";
                }
                $index++;
            }

            // Quitamos la última coma para evitar errores de sintaxis
            $sql = rtrim($sql, ",");

            // Modo debug para imprimir consulta con parámetros
            if (self::$debug) {
                $this->db->imprimirConsulta($sql, $values, 'Bulk Insert de Pagos:');
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            $filasAfectadas = $stmt->rowCount();

            if (self::$debug) {
                echo '<br>Resultado de Query: ';
                var_dump($filasAfectadas);
                echo '<br><br>';
            }

            if ($filasAfectadas > 0) {
                return ['success' => true, 'message' => "Se insertaron $filasAfectadas pagos correctamente."];
            } else {
                return ['success' => false, 'message' => 'No se pudo insertar ningún pago.'];
            }
        } catch (\PDOException $e) {
            // Captura de errores y almacenamiento en el log
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] globals/services/api/SilmeApi/models/RegistrarPago_Mdl.php -> Error en Bulk Insert: " . $e->getMessage() . PHP_EOL, 3, LOG_FILE_BD);
            return ['success' => false, 'message' => 'Error al insertar pagos. Notifica a tu administrador'];
        }
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
