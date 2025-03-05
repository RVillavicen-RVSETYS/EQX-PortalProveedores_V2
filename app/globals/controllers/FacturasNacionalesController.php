<?php

namespace App\Globals\Controllers;

use Core\Controller;
use App\Models\DatosCompra\HojaEntrada_Mdl;
use App\Models\Configuraciones\RecepcionCFDIs_Mdl;
use App\Models\Empresas\Empresas_Mdl;
use App\Models\Proveedores\Proveedores_Mdl;
use App\Models\DatosCFDIs\RegistroCFDIsv40_Mdl;

class FacturasNacionalesController extends Controller
{
    protected $debug = 0;

    public function __construct()
    {
        if ($this->debug == 1) {
            echo "<h2>Ya estamos dentro de App\Globals\Controllers\FacturasNacionalesController.php.</h2>";
        }
    }

    /**
     * Esta Clase aplicara las Reglas de Negocio y Fiscales, si todo esta OK Almacenara las Facturas.
     *  - Este Controlador trabajara con Transacciones en la BD.
     * NOTA: Antes de llegar Aqui los PDF's y XML ya deben estar verificados, validadas las HES y la OC.
     */
    public function verificaNuevaFacturaIngresos($reqNotaCredito, $OC, $HES, $doctos, $isAdmin = 0, $reglasAdmin = [])
    {
        $response = [
            'success' => false,
            'message' => '',
            'data' => []
        ];

        $noProveedor = ($isAdmin == 1 && isset($_POST['admin_noProveedor'])) ? $_POST['admin_noProveedor'] : $_SESSION['EQXnoProveedor'];
        if ($this->debug == 1) {
            echo '<br>Valores para la carga:';
            echo '<br> * Requiere Nota de Credito:' . $reqNotaCredito;
            echo '<br> * No de Proveedor:' . $noProveedor;
            echo '<br> * OC:' . $OC;
            echo '<br> * HES:';
            var_dump($HES);
            echo '<br> * Es Administrador:' . $isAdmin;
            echo '<br> * Reglas a Aplicar:';
            var_dump($reglasAdmin);
            echo '<br> * DocumentosRecibidos:';
            var_dump($doctos);
        }

        //Verificamos los Archivos de la Nota de Credito
        if ($reqNotaCredito > 0) {
            if (empty($doctos['NotaCreditoPDF']) || empty($doctos['NotaCreditoXML'])) {
                return ['success' => false, 'message' => 'No se recibio correctamente la nota de Credito.'];
            }             
        } else {
            $dataNotaCredXML['data'] = '';
        }
                
        //1.- Obtenemos los datos de las HES para comparar con la Factura y definir el idEmpresa y el idMoneda segun la OC
        $MDL_hojaEntrada = new HojaEntrada_Mdl();
        $dataMontosHES = $MDL_hojaEntrada->dataMontosHES($HES, $OC);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de dataMontosHES para comparar con Factura: ' . PHP_EOL;
            var_dump($dataMontosHES);
        }
        if (!$dataMontosHES['success']) {
            return ['success' => false, 'message' => $dataMontosHES['message']];
        }
        $idEmpresa = $dataMontosHES['data']['sociedad'];
        $idMoneda = $dataMontosHES['data']['idMoneda'];

        //2.- Obtenemos las Configuraciones de la Sociedad y del Proveedor para comparar con la Factura
        $MDL_ConfigParaCFDI = new RecepcionCFDIs_Mdl();
        $configMontos = $MDL_ConfigParaCFDI->diferenciaMontoXMoneda($noProveedor, $idEmpresa, $idMoneda);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de diferenciaMontoXMoneda para comparar con Factura: ' . PHP_EOL;
            var_dump($configMontos);
        }
        if (!$configMontos['success']) {
            return ['success' => false, 'message' => $configMontos['message']];
        }

        //3.- Leer XML
        $Ctrl_CFDIs = new CfdisController();
        $dataFactXML = $Ctrl_CFDIs->leerCfdiXML($doctos['FacturaXML']['tmp_name'], 'Ingreso');
        if ($this->debug == 1) {
            echo '<br><br>Datos del XML: ' . PHP_EOL;
            var_dump($dataFactXML);
        }
        if ($dataFactXML['success']) {
            $versionDocto = $dataFactXML['version'];
            $tipoCFDI = $dataFactXML['data']['Comprobante']['TipoDeComprobante'];

            // 4. Cargar configuración del portal correspondiente al tipo de CFDI y version para las validaciones
            $configCFDI = $MDL_ConfigParaCFDI->configuracionBaseRecepcionCFDI($idEmpresa, $tipoCFDI, $versionDocto);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de configuración base del CFDI para comparar con Factura: ' . PHP_EOL;
                var_dump($configCFDI);
            }
            if (!$configCFDI['success']) {
                return ['success' => false, 'message' => $configCFDI['message']];
            }

            // 5. Obtener datos de la empresa para las validaciones
            $MDL_Empresas = new Empresas_Mdl();
            $dataEmpresa = $MDL_Empresas->empresaPorId($idEmpresa);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de Busqueda de Empresa: ' . PHP_EOL;
                var_dump($dataEmpresa);
            }
            if (!$dataEmpresa['success']) {
                return ['success' => false, 'message' => $dataEmpresa['message']];
            }

            // 7. Obtener datos de Proveedores y su configuración de Permisos (EJEMPLO: ExentoAnioFiscal, etc..)
            $MDL_Proveedores = new Proveedores_Mdl();
            $dataProv = $MDL_Proveedores->obtenerDatosProveedor($noProveedor);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de datos del Proveedor: ' . PHP_EOL;
                var_dump($dataProv);
            }
            if (!$dataProv['success']) {
                return ['success' => false, 'message' => $dataProv['message']];
            }
            $exepcionesProv = $MDL_Proveedores->exepcionesProveedoresFacturas($noProveedor);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de configuración de Permisos del Proveedor: ' . PHP_EOL;
                var_dump($exepcionesProv);
            }
            if (!$exepcionesProv['success']) {
                return ['success' => false, 'message' => $exepcionesProv['message']];
            }

            // 8. Generar arreglo de Configuración para Validaciones
            $configParaValidaciones = array();
            $configParaValidaciones['datosRecepciones'] = $dataMontosHES['data'];
            $configParaValidaciones['configCFDI'] = $configCFDI['data'];
            $configParaValidaciones['diferenciaMontos'] = $configMontos['data'];
            $configParaValidaciones['excepcionesProveedor'] = $exepcionesProv['data'];
            if ($this->debug == 1) {
                echo '<br><br>Resultado de configuraciones para la Recepción del CFDI: ' . PHP_EOL;
                var_dump($configParaValidaciones);
            }

            // 9. Cargar el archivo y clase de la versión correspondiente para las validaciones
            $claseFuncion = 'ReglasAplicadas' . $versionDocto;
            $archivoVersion = __DIR__ . "/reglas/{$claseFuncion}.php";
            if ($this->debug == 1) {
                echo '<br><br>URL de la Clase que Valida Reglas de Negocio: ' . $archivoVersion . '<br>';
                echo '<br><br>Clase que validara: ' . $claseFuncion . '<br>';
            }
            if (!file_exists($archivoVersion)) {
                return ['success' => false, 'message' => "El archivo para las Reglas de Negocio de la versión $versionDocto no existe."];
            }
            require_once $archivoVersion;

            if (!class_exists($claseFuncion)) {
                if ($this->debug == 1) {
                    echo '<br><br>El Metodo no está definido: ' . $claseFuncion . '<br>';
                }
                return ['success' => false, 'message' => "El Metodo $claseFuncion no está definido en la clase correspondiente."];
            }


            // 10. Instanciar la clase y verificar que los métodos existan para esa Versión
            $cfdiVersion = new $claseFuncion();
            $metodoValidaReglasInternas = "validarReglasInternasNacional_Ingresos";
            $metodoValidaReglasNegocio = "validarReglasNegocioNacional_Ingresos";
            if ($this->debug == 1) {
                echo '<br><br>Metodo que validara Las reglas de negocio: ' . $metodoValidaReglasNegocio . '<br>';
            }
            if (!method_exists($cfdiVersion, $metodoValidaReglasInternas)) {
                if ($this->debug == 1) {
                    echo '<br><br>El método ' . $metodoValidaReglasInternas . ' no está definido en la clase ' . $claseFuncion . '<br>';
                }
                return ['success' => false, 'message' => "El método $metodoValidaReglasNegocio no está definido en la clase $claseFuncion."];
            }
            if (!method_exists($cfdiVersion, $metodoValidaReglasNegocio)) {
                if ($this->debug == 1) {
                    echo '<br><br>El método ' . $metodoValidaReglasNegocio . ' no está definido en la clase ' . $claseFuncion . '<br>';
                }
                return ['success' => false, 'message' => "El método $metodoValidaReglasNegocio no está definido en la clase $claseFuncion."];
            }

            //11. Ejecutar el método validarReglasInternasNacional_Ingresos
            $reglasInternas = $cfdiVersion->$metodoValidaReglasInternas($dataProv['data'], $dataEmpresa['data'],  $dataFactXML['data']);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de validaReglas de Negocio ' . $metodoValidaReglasInternas . ': ' . PHP_EOL;
                var_dump($reglasInternas);
                echo '<br><br> ****** PASAMOS LAS VALIDACIONES INTERNAS <br>';
            }
            if (!$reglasInternas['success'] || !$reglasInternas['isValid']) {
                return ['success' => false, 'message' => 'Tuvimos los siguientes Problemas al Validar tu CFDI:<br>' . $reglasInternas['message']];
            }

            //12. Ejecutar el método validarReglasNegocioNacional_Ingresos
            $reglasNegocio = $cfdiVersion->$metodoValidaReglasNegocio($noProveedor, $dataFactXML['data'], $configParaValidaciones);
            if ($this->debug == 1) {
                echo '<br><br>Resultado de validaReglas de Negocio ' . $metodoValidaReglasNegocio . ': ' . PHP_EOL;
                var_dump($reglasNegocio);
            }
            if ($reglasNegocio['success'] && $reglasNegocio['isValid']) {
                if ($this->debug == 1) {
                echo '<br><br> ****** PASAMOS LAS REGLAS DE NEGOCIO <br>';
                }

                //13. Ejecutar las Validaciones Fiscales del CFDI
                $claseFuncionFiscal = 'cfdis' . $versionDocto;
                $archivoVersion = __DIR__ . "/cfdis/{$claseFuncionFiscal}.php";
                if ($this->debug == 1) {
                    echo '<br><br>URL de la Clase que Valida Fiscalmente es: ' . $archivoVersion . '<br>';
                    echo 'Clase que validara  Fiscalmente: ' . $claseFuncionFiscal . '<br>';
                }
                require_once $archivoVersion;

                $cfdiFiscalVersion = new $claseFuncionFiscal();
                $validaFiscalMente = $cfdiFiscalVersion->validarCFDIv1($dataFactXML['data']);
                if ($this->debug == 1) {
                    echo '<br><br>Resultado de Vaidación Fiscal: ' . PHP_EOL;
                    var_dump($validaFiscalMente);
                }
                if ($validaFiscalMente['success'] && $validaFiscalMente['isValid']) {
                    if ($this->debug == 1) {
                        echo '<br><br> ****** PASAMOS LA VALIDACION FISCAL <br>';
                    }

                    $response = [
                        'success' => true,
                        'message' => 'Todas las Validaciones se han aplicado correctamente.',
                        'data' => [
                            'anticipo' => $reqNotaCredito,
                            'version' => $versionDocto,
                            'isAdmin' => $isAdmin,
                            'ValidFiscal' => $validaFiscalMente['data'],
                            'dataMontosHES' => $dataMontosHES['data'],
                            'dataProv' => $dataProv['data'],
                            'dataFactXML' => $dataFactXML['data'],
                            'dataNotaCredXML' => $dataNotaCredXML['data']
                        ]
                    ];

                } else {
                    $response['message'] = 'Tuvimos los siguientes Problemas al Validar tu XML:<br>' . $validaFiscalMente['message'];
                }
            } else {
                $response['message'] = 'Tuvimos los siguientes Problemas al Validar tu XML:<br>' . $reglasNegocio['message'];
            }
        } else {
            $response['message'] = $dataFactXML['message'];
        }

        return $response;
    }

    public function registraNuevaFacturaIngresos($resultadoDeVerificacion)
    {
        
        $MDL_registraCFDI = new RegistroCFDIsv40_Mdl();
        $respRegistro = $MDL_registraCFDI->registrarCFDI_Ingresosv40($resultadoDeVerificacion);
        if ($this->debug == 1) {
            echo '<br><br>Resultado de registroCFDI: ' . PHP_EOL;
            var_dump($respRegistro);
        }

        return $respRegistro;

    }


    private function logErrorAndExit($message)
    {
        $timestamp = date("Y-m-d H:i:s");
        error_log("[$timestamp] $message" . PHP_EOL, 3, LOG_FILE);

        if ($this->debug == 1) {
            echo "<strong>Error crítico:</strong> $message<br>";
        }
        exit($message); // Detener la ejecución con un mensaje claro
    }
}
