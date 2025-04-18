<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_ConnectHES; // Asegúrate de que la conexión esté disponible
use BD_Connect;

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';
require_once __DIR__ . '/../../../config/BD_Connect.php';

class HojaEntrada_Mdl
{
    private $dbHES;
    private $db;
    private static $debug = 0; // Cambiar a 0 para desactivar mensajes de depuración

    public function __construct()
    {
        if (self::$debug) {
            echo "<h2>Ya estamos dentro de la Clase HojaEntrada_Mdl.</h2>";
        }

        $this->dbHES = new BD_ConnectHES(); // Instancia de la conexión a la base de datos

    }

    public function verificaHojaEntrada($ordenCompra, $hojaEntrada)
    {
        //self::$debug = 1;
        if (self::$debug) {
            echo "<br>verificaHojaEntrada ordenCompra: $ordenCompra";
            echo "<br>verificaHojaEntrada HojaEntrada: $hojaEntrada<br><br>";
        }

        // Verificar la estructura de las HES
        $estructura = $this->verificaEstructuraHES($hojaEntrada);
        if ($estructura['cantidadInvalidas'] > 0) {
            $cantInv = $estructura['cantidadInvalidas'];
            $inv = implode(', ', $estructura['invalidas']) . PHP_EOL;
            return ['success' => false, 'message' => 'Hay ' . $cantInv . ' Hojas de Entrada erroneas:' . PHP_EOL . $inv];
        } else {
            // Validar si hay HES repetidas en la entrada
            $hesUnicas = array_unique($estructura['validas']);
            $hesRepetidas = array_diff_assoc($estructura['validas'], $hesUnicas);

            if (!empty($hesRepetidas)) {
                $cantRep = count($hesRepetidas);
                $rep = implode(', ', $hesRepetidas);
                return ['success' => false, 'message' => "Se encontraron $cantRep Hojas de Entrada repetidas: $rep"];
            }
            
            // Preparar lista de HES para la consulta
            $hesList = implode(', ', array_map(function ($item) {
                return "'" . addslashes($item) . "'";
            }, $estructura['validas']));
            $cantHES = $estructura['cantidadValidas'];

            if (self::$debug) {
                echo "Válidas: " . $hesList . PHP_EOL;
            }

            try {
                $sql = "SELECT *
                  FROM vw_ext_PortalProveedores_HESporPagar
                  WHERE OrdenCompra = :ordenCompra AND HES IN ($hesList)";

                if (self::$debug) {
                    $params = [
                        ':ordenCompra' => $ordenCompra
                    ];
                    $this->dbHES->imprimirConsulta($sql, $params, 'Consulta las HES solicitadas.');
                }
                $stmt = $this->dbHES->prepare($sql);
                $stmt->bindParam(':ordenCompra', $ordenCompra, PDO::PARAM_STR);
                $stmt->execute();
                $resultHES = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (self::$debug) {
                    echo '<br>Resultado de Query:';
                    var_dump($resultHES);
                    echo '<br><br>';
                }

                if (count($resultHES) > 0) {
                    $comparaHESvsOC = $this->validaHESContraOrden($estructura, $ordenCompra, $resultHES);
                    if (self::$debug) {
                        echo '<br>Resultado de validaHESContraOrden:';
                        var_dump($comparaHESvsOC);
                        echo '<br><br>';
                    }

                    if ($comparaHESvsOC['validasEnOC']) {
                        // Todas las HES son válidas y corresponden a la OC.;
                        $verificaHESFacturada = $this->verificaYaFacturadaHES($hesList);
                        if ($verificaHESFacturada['success']) {
                            // OK Aun no ha sido Facturada ninguna HES
                            return ['success' => true, 'cantHES' => $estructura['cantidadValidas'], 'hesOK' => $estructura['validas'], 'preparedHes' => $hesList, 'data' => $resultHES];
                        } else {
                            $mensaje = $verificaHESFacturada['message'];
                            return ['success' => false, 'message' => $mensaje];
                        }
                    } else {
                        $mensaje = 'HES erroneas: ' . implode(", ", $comparaHESvsOC['faltantes']) . ".";
                        return ['success' => false, 'message' => $mensaje];
                    }
                } else {
                    if (self::$debug) {
                        echo "La HES no es de esta OC.<br>"; // Mostrar error en modo depuración
                    }
                    return ['success' => false, 'message' => 'Recepcion incorrecta para la OC: ' . $ordenCompra . '.'];
                }
            } catch (\Exception $e) {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app/Models/datosCompra/HojaEntrada_Mdl.php ->Error contar HES de la OC: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
                if (self::$debug) {
                    echo "Error al contar HES: " . $e->getMessage(); // Mostrar error en modo depuración
                }
                return ['success' => false, 'message' => 'Problemas con tu OC, Notifica a tu administrador.'];
            }
        }
    }

    public function verificaEstructuraHES($hojaEntrada)
    {
        // Expresión regular para validar HES-XXX-######
        $pattern = '/^HES-[A-Z]{3}-\d{6}$/';

        // Separar las entradas por coma, limpiar espacios y convertir a mayúsculas
        $entradas = array_map('trim', explode(',', strtoupper($hojaEntrada)));

        // Inicializar resultados
        $validas = [];
        $invalidas = [];

        // Validar cada entrada
        foreach ($entradas as $entrada) {
            if (preg_match($pattern, $entrada)) {
                $validas[] = $entrada; // Si es válida, agregar a válidas
            } else {
                $invalidas[] = $entrada; // Si no es válida, agregar a inválidas
            }
        }

        // Retornar resultados con cantidades y valores
        return [
            'cantidadValidas' => count($validas),
            'cantidadInvalidas' => count($invalidas),
            'validas' => $validas,
            'invalidas' => $invalidas
        ];
    }

    public function verificaYaFacturadaHES($hojaEntrada)
    {
        //self::$debug = 1;
        if (self::$debug) {
            echo "<br>Valor de Hoja de entrada: " . $hojaEntrada . PHP_EOL;
        }
        $hesList = $hojaEntrada;

        $this->db = new BD_Connect();

        try {

            $sql = "SELECT	GROUP_CONCAT(DISTINCT dc.idCompra) AS idCompras
            FROM detcompras dc
            INNER JOIN compras c ON dc.idCompra = c.id
            WHERE c.estatus < 3 AND dc.noRecepcion IN ($hesList)";

            if (self::$debug) {
                $params = [];
                $this->db->imprimirConsulta($sql, $params, 'Consulta HES repetidas.');
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $repiteHES = $stmt->fetch(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:';
                var_dump($repiteHES);
                echo '<br><br>';
            }

            if ($repiteHES['idCompras'] > 0) {
                $acuses = $repiteHES['idCompras'];
                if (self::$debug) {
                    echo "Una o mas HES ya fueron Facturadas en el acuse: $acuses.<br>";
                }
                return ['success' => false, 'message' => 'Una o mas HES ya fueron Facturadas en el acuse:' . $acuses . '.'];
            } else {
                return ['success' => true];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/datosCompra/HojaEntrada_Mdl.php ->Error al consultar HES ya Facturadas: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
            if (self::$debug) {
                echo "Error al consultar HES ya Facturadas: " . $e->getMessage(); // Mostrar error en modo depuración
            }
            return ['success' => false, 'message' => 'Problemas al consultar las HES ya Facturadas. Notifica a tu administrador.'];
        }
    }

    function validaHESContraOrden($estructura, $ordenCompra, $resultadoDB)
    {
        // Extrae las HES válidas del array resultado de `validaEntradas()`
        $validasEntradas = $estructura['validas'];
        $totalValidasEntradas = $estructura['cantidadValidas'];

        // Extrae las HES encontradas en la base de datos
        $validasDB = array_column($resultadoDB, 'HES'); // Obtiene el campo HES del arreglo SQL
        $totalValidasDB = count($validasDB);

        // Validar si todas las entradas válidas están en la base de datos
        $faltantes = array_diff($validasEntradas, $validasDB); // HES válidos que no existen en la DB
        $extras = array_diff($validasDB, $validasEntradas);    // HES en DB que no están en las entradas
        $validasEnOC = count($faltantes) === 0; // Todas las HES válidas están en la DB y corresponden a la OC

        return [
            'ordenCompra' => $ordenCompra,
            'validasEntradas' => $validasEntradas,
            'validasDB' => $validasDB,
            'totalValidasEntradas' => $totalValidasEntradas,
            'totalValidasDB' => $totalValidasDB,
            'faltantes' => $faltantes, // Lista de HES válidas que no están en la DB
            'extras' => $extras,       // Lista de HES en la DB que no están en las entradas válidas
            'validasEnOC' => $validasEnOC // Boolean: todas las HES corresponden a la OC
        ];
    }

    public function dataMontosHES($hojasEntrada, $ordenCompra)
    {
        $hesList = implode(', ', array_map(function ($item) {
            return "'" . addslashes($item) . "'";
        }, $hojasEntrada));

        if (self::$debug) {
            echo "<br>HES Preparadas: " . $hesList . PHP_EOL;
        }

        try {
            $sql = "SELECT *
                FROM vw_ext_PortalProveedores_MontosHES
                WHERE HES IN ($hesList)";

            if (self::$debug) {
                $params = []; // Sin parámetros porque HES ya están preparados
                $this->dbHES->imprimirConsulta($sql, $params, 'Consulta de Montos de HES.');
            }

            $stmt = $this->dbHES->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (self::$debug) {
                echo '<br>Resultado de Query:<br>';
                var_dump($resultados);
                echo '<br>';
            }

            if (count($resultados) > 0) {
                // Validar las inconsistencias en los datos
                $ordenesCompra = array_unique(array_column($resultados, 'OC'));
                $sociedades = array_unique(array_column($resultados, 'sociedad'));
                $monedas = array_unique(array_column($resultados, 'idMoneda'));
                $compromisosPago = array_unique(array_column($resultados, 'CPago'));

                $errores = [];
                if (count($ordenesCompra) > 1) {
                    $errores[] = 'Hay HES asociadas a diferentes órdenes de compra.';
                }
                if (count($sociedades) > 1) {
                    $errores[] = 'Hay HES asociadas a diferentes sociedades.';
                }
                if (count($monedas) > 1) {
                    $errores[] = 'Hay HES con diferentes tipos de moneda.';
                }
                if (count($compromisosPago) > 1) {
                    $errores[] = 'Hay HES con diferentes compromisos de pago (CPago).';
                }

                if (!empty($errores)) {
                    return ['success' => false, 'message' => 'Se encontraron inconsistencias en las HES.', 'errors' => $errores];
                }

                // Calcular el subtotal
                $subtotal = array_sum(array_column($resultados, 'subtotal'));

                return [
                    'success' => true,
                    'data' => [
                        'Subtotal' => $subtotal,
                        'idCompra' => $resultados[0]['idCompra'],
                        'idProveedor' => $resultados[0]['idProveedor'],
                        'sociedad' => $resultados[0]['sociedad'],
                        'OC' => $resultados[0]['OC'],
                        'HES_Query' => $hesList,
                        'idMoneda' => $resultados[0]['idMoneda'],
                        'CPago' => $resultados[0]['CPago'],
                        'resultQuery' => $resultados
                    ]
                ];
            } else {
                if (self::$debug) {
                    echo "No se encontraron registros para las HES proporcionadas.<br>";
                }
                return ['success' => false, 'message' => 'No se encontraron registros para las HES proporcionadas.'];
            }
        } catch (\Exception $e) {
            $timestamp = date("Y-m-d H:i:s");
            error_log("[$timestamp] app/Models/datosCompra/HojaEntrada_Mdl.php ->Error al consultar montos HES: " . $e->getMessage(), 3, LOG_FILE_BD);
            if (self::$debug) {
                echo "Error al consultar montos HES: " . $e->getMessage();
            }
            return ['success' => false, 'message' => 'Problemas al consultar los montos de las HES. Notifica a tu administrador.'];
        }
    }
}
