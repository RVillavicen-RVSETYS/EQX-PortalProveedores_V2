function getCadenaLimpia(cadena){
   // Definimos los caracteres que queremos eliminar
   var specialChars = "\'!\"¬@#$^&*()[]\/{}|:<>?¿¡";

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
