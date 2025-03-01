<?php
class Idiomas {
    private $pagina;

    public function __construct($pagina)
    {
        $this->pagina = $pagina;
    }

    public function txt($clave, $general = 0) 
    {
        $pagina = $this->pagina;
        if ($general == 0) {
            return self::$textosEspecificos[$pagina][$clave] ?? "Text NULL--";
        } else {
            return self::$textosGenerales['AllPages'][$clave] ?? "Text NULL--";
        }
        
        
    }
    
    private static $textosGenerales = [
        'AllPages' => [
            'Ver_Perfil' => "Ver Perfil",
            'Centro_Ayuda' => "Centro de Ayuda",
            'Cerrar_Session' => "Cerrar Sessión",
            'No_Vacio' => "Campo requerido, no puede ser Vacio."
        ],
    ];
    
    private static $textosEspecificos = [
        'Inicio' => [
            'Ultimas_Facturas' => "Ultimas 50 Facturas cargadas",
            'Carga_Factura' => "Carga Factura",
            'Carga_Factura_por_anticipo' => "Carga Factura por Anticipo",
            'Carga_Factura_por_consignacion' => "Carga Factura por Consignación",
            'No_Proveedor' => "No. Proveedor",
            'OC' => "Orden Compra",
            'HES' => "Numeros Recepción",
            'Facturas' => "Facturas",
            'Limpiar' => "Limpiar",
            'CodigoAnticipo' => "Código Anticipo",
            'CodigoAnticipo' => "Código Anticipo",
            'Complementos_Pendientes' => "Complementos Pendientes"
            
        ],
        'Historico' => [
            
        ]
    ];
}