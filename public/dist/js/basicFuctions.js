function notificaSuc(cont){
  toastr.success(cont, 'Excelente!', {
    "progressBar": true,
    "CerrarButton": true
  });
}

function notificaBad(cont){
  toastr.error(cont, 'Lo Sentimos!', {
    "progressBar": true,
    "closeButton": true
  });
}

function bloqueoBtn(boton,no){
  // verifica si hay un valor en la variable boton, si no le coloca una por default llamada "bloquear-btn"
  if (boton == '') {
    boton = 'bloquear-btn';
  }
  // si la variable 'no' es 1 oculta el elemento y muestra el espinner
    if (no==1) {
      $("#"+boton).show();
      $("#des"+boton).hide();
    }else {
      // si la variable 'no' es 2 muestra el elemento y oculta el espinner
      $("#"+boton).hide();
      $("#des"+boton).show();
    }

  }

  function bloqueaBotones(div1,div2,no){
    // verifica si hay un valor en la variable boton, si no le coloca una por default llamada "bloquear-btn"
    if (div1 == '') {
      alert('Debes ingresar el id del div en primera posición que quieres ocultar');
    }
    if (div2 == '') {
      alert('Debes ingresar el id del div en segunda posición que quieres ocultar');
    }
    // si la variable 'no' es 1 oculta el elemento y muestra el espinner
      if (no==1) {
        $(div1).show();
        $(div2).hide();
      }else {
        // si la variable 'no' es 2 muestra el elemento y oculta el espinner
        $(div1).hide();
        $(div2).show();
      }

    }
  function limpiaCadena(dat,id){
      //alert(id);
      dat=getCadenaLimpia(dat);
    $("#"+id).val(dat);
  }

  function getCadenaLimpia(cadena){
     // Definimos los caracteres que queremos eliminar
     var specialChars = "\'!\"¬@#$^&%*()[]\/{}|:<>?¿¡";

     // Los eliminamos todos
     for (var i = 0; i < specialChars.length; i++) {
         cadena = cadena.replace(new RegExp("\\" + specialChars[i], 'gi'), '');
         cadena = cadena.replace(/[ÄÁáäà]/gi,"a");
         cadena = cadena.replace(/[ËÉëé]/gi,"e");
         cadena = cadena.replace(/[ÏÍïí]/gi,"i");
         cadena = cadena.replace(/[ÖÓöó]/gi,"o");
         cadena = cadena.replace(/[ÜÚüú]/gi,"u");
         cadena = cadena.replace(/ñ/gi,"n");
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

  function soloNumeros(cadena, id){
    var newCadena = cadena.replace(/[^0-9]/g,'');
    //alert(newCadena);
    $("#"+id).val(newCadena);
  }

  function cambiaMayusculas(cadena, id){
    var newCadena = cadena.toUpperCase();
    //alert(newCadena);
    $("#"+id).val(newCadena);
  }

  function bloqueaCard(idCard,no){
    if (no==1) {
      var block_ele = $(this).closest('.card');
      $(idCard).block({
          message: '<b class="text-white">Procesando...</b><br><i class="fas fa-spin fa-sync text-white"></i>',
          overlayCSS: {
              backgroundColor: '#000',
              opacity: 0.5,
              cursor: 'wait'
          },
          css: {
              border: 0,
              padding: 0,
              backgroundColor: 'transparent'
          }
      });
    }else {
          $(idCard).unblock();
    }
  }
