<?php
function eliminar_simbolos($string){

    $string = trim($string);

    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'Á', 'A', 'A'),
        $string
    );

    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'É', 'E', 'E'),
        $string
    );

    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'Í', 'I', 'I'),
        $string
    );

    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'Ó', 'O', 'O'),
        $string
    );

    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ù', 'Û', 'Ü'),
        array('u', 'ú', 'u', 'u', 'Ú', 'U', 'U'),
        $string
    );

    $string = str_replace(
        array('ç', 'Ç'),
        array('c', 'C',),
        $string
    );

    $string = str_replace(
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", "\"",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", " "),
        ' ',
        $string
    );
return $string;
}
?>
