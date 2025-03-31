function bloqueoModal(event, modal, opcion) {
    if (modal == '') {
        modal = 'bloquear-modal';
    }

    var block_ele = event.target.closest("." + modal);

    // si la variable 'no' es 1 bloquea el modal
    if (opcion == 1) {
        $(block_ele).block({

            message: 'Espere un momento...',
            css: {
                border: 0,
                hover: 'wait',
                padding: 0,
                cursor: 'wait',
                color: '#848789',
                backgroundColor: 'transparent'
            },

            overlayCSS: {
                backgroundColor: '#A4A7A9',
                opacity: 0.8,

            }

        });
    } else { // si la variable 'no' es diferente de 1 desbloquea el modal
        $(block_ele).unblock();
    }

}

function bloqueoBtn(boton, no) {
    // verifica si hay un valor en la variable boton, si no le coloca una por default llamada "bloquear-btn"
    if (boton == '') {
        boton = 'bloquear-btn';
    }
    // si la variable 'no' es 1 oculta el elemento y muestra el espinner
    if (no == 1) {
        $("#" + boton).show();
        $("#des" + boton).hide();
    } else {
        // si la variable 'no' es 2 muestra el elemento y oculta el espinner
        $("#" + boton).hide();
        $("#des" + boton).show();
    }

}

function limpiaCadena(dat, id) {
    //alert(id);
    dat = getCadenaLimpia(dat);
    $("#" + id).val(dat);
}

function getCadenaLimpia(cadena) {
    // Definimos los caracteres que queremos eliminar
    var specialChars = "\'!\"¬@#$^&%*()[]\/{}|:<>?¿¡";

    // Los eliminamos todos
    for (var i = 0; i < specialChars.length; i++) {
        cadena = cadena.replace(new RegExp("\\" + specialChars[i], 'gi'), '');
        cadena = cadena.replace(/[ÄÁáäà]/gi, "a");
        cadena = cadena.replace(/[ËÉëé]/gi, "e");
        cadena = cadena.replace(/[ÏÍïí]/gi, "i");
        cadena = cadena.replace(/[ÖÓöó]/gi, "o");
        cadena = cadena.replace(/[ÜÚüú]/gi, "u");
        cadena = cadena.replace(/ñ/gi, "n");
    }

    // Lo queremos devolver limpio en minusculas
    //cadena = cadena.toLowerCase();

    // Quitamos espacios y los sustituimos por _ porque nos gusta mas asi
    //cadena = cadena.replace(/ /g,"_");

    /* Quitamos acentos y "ñ". Fijate en que va sin comillas el primer parametro
    cadena = cadena.replace(/á/gi,"a");
    cadena = cadena.replace(/é/gi,"e");
    cadena = cadena.replace(/í/gi,"i");
    cadena = cadena.replace(/ó/gi,"o");
    cadena = cadena.replace(/ú/gi,"u");
    cadena = cadena.replace(/ñ/gi,"n");*/
    return cadena;
}

function soloNumerosID(cadena, id) {
    var newCadena = cadena.replace(/[^0-9]/g, '');
    //alert(newCadena);
    $("#" + id).val(newCadena);
}
function soloNumeros(str){
    var newStr = str.replace(/[^0-9]/g, '');
    return newStr;
}

function cambiaMayusculas(cadena, id) {
    var newCadena = cadena.toUpperCase();
    //alert(newCadena);
    $("#" + id).val(newCadena);
}