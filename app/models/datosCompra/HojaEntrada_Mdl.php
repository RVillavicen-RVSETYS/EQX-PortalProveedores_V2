<?php

namespace App\Models\DatosCompra;

use PDO; // Asegúrate de importar PDO si es necesario
use BD_ConnectHES; // Asegúrate de que la conexión esté disponible

// Incluye conección a la BD
if (!defined('INCLUDE_CHECK')) {
    define('INCLUDE_CHECK', true);
}
require_once __DIR__ . '/../../../config/BD_ConnectHES.php';

class HojaEntrada_Mdl
{
    private $dbHES;
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
        if (self::$debug) {
        echo "<br>verificaHojaEntrada ordenCompra: $ordenCompra<br>";
        echo "<br>verificaHojaEntrada HojaEntrada: $hojaEntrada<br>";
        }

        $estructura = $this->verificaEstructuraHES($hojaEntrada);
        if ($estructura['cantidadInvalidas'] > 0) {
            $cantInv = $estructura['cantidadInvalidas'];
            $inv = implode(', ', $estructura['invalidas']) . PHP_EOL;
            return ['success' => false, 'message' => 'Hay '.$cantInv.' Hojas de Entrada erroneas:'. PHP_EOL .$inv];
        } else {
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
                    if ($comparaHESvsOC['validasEnOC']) {
                        return ['success' => true, 'cantHES' => $estructura['cantidadValidas'], 'hesOK' => $estructura['validas'], 'data' => $resultHES]; // Todas las HES son válidas y corresponden a la OC.;
                    } else {
                        $mensaje = 'HES erroneas: '. implode(", ", $comparaHESvsOC['faltantes']) . ".";
                        return ['success' => false, 'message' => $mensaje];
                    }
                    
                } else {
                    if (self::$debug) {
                        echo "No hay ninguna HES para esta OC.<br>"; // Mostrar error en modo depuración
                    }
                    return ['success' => false, 'message' => 'No hay recepciones para la OC: '.$ordenCompra.'.'];
                }
            } catch (\Exception $e) {
                $timestamp = date("Y-m-d H:i:s");
                error_log("[$timestamp] app/models/datosCompra/HojaEntrada_Mdl.php ->Error contar HES de la OC: " . $e->getMessage(), 3, LOG_FILE_BD); // Manejo del error
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

    function validaHESContraOrden($estructura, $ordenCompra, $resultadoDB) {
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
}
