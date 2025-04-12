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
            'Ver_Perfil' => "Profile",
            'Centro_Ayuda' => "Help Center",
            'Cerrar_Session' => "Logout",
            'No_Vacio' => "Required field, cannot be empty",
        ],
    ];
    
    private static $textosEspecificos = [
        'Inicio' => [
            'Ultimas_Facturas' => "Last 50 invoices uploaded",
            'Carga_Factura' => "Invoice Load",
            'Carga_Factura_por_anticipo' => "Carga Factura por Anticipo",
            'Carga_Factura_por_consignacion' => "Carga Factura por Consignación",
            'No_Proveedor' => "Supplier ID",
            'OC' => "Purchase Order",
            'HES' => "Reception Number",
            'Facturas' => "Invoices",
            'Limpiar' => "Clear",
            'CodigoAnticipo' => "Advance Code",
            'Complementos_Pendientes' => "Complementos Pendientes",
            'Informacion_Factura' => "Invoice Information",
        ],
        'Historico' => [
            'Filtro_Busqueda' => "Serach Filter",
            'Rango_Fechas' => "Date Range",
            'Desde' => "From",
            'Hasta' => "To",
        ]
    ];
}