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
            'Ver_Perfil' => "Ver_Perfil",
            'Centro_Ayuda' => "Centro_Ayuda",
            'Fin' => "Fin"
        ],
    ];
    
    private static $textosEspecificos = [
        'Inicio' => [
            
        ],
        'Historico' => [
            
        ]
    ];
}