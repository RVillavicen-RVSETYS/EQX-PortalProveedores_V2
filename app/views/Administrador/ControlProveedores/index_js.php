<script>
    function datosGenerales(idProveedor) {
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/datosGenerales',
            data: {
                idProveedor: idProveedor
            },
            success: function(response) {
                $('#list-Proveedor').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#list-Proveedor').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#list-Proveedor').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function recepciones(idProveedor) {
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/recepcionesSinFactura',
            data: {
                idProveedor: idProveedor
            },
            success: function(response) {
                $('#list-Recepciones').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#list-Recepciones').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#list-Recepciones').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function sinFecha(idProveedor) {
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/sinFechaPago',
            data: {
                idProveedor: idProveedor
            },
            success: function(response) {
                $('#list-SinFecha').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#list-SinFecha').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#list-SinFecha').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function historico(idProveedor) {
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/historico',
            data: $(this).serialize(),
            success: function(response) {
                $('#list-Historico').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#list-Historico').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#list-Historico').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('submit', '#consultarProveedor', function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/infoProveedor',
            data: $(this).serialize(),
            success: function(response) {
                $('#tarjetaProveedor').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaProveedor').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaProveedor').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });

    $(document).on('submit', '#nuevoCorreo', function(event) {
        event.preventDefault();

        const idProveedor = document.getElementById('idProveedor').value;
        const nuevoCorreo = document.getElementById('newCorreo').value;
        //console.log('Id Del Proveedor: ' + idProveedor + " Nuevo Correo: " + nuevoCorreo);
        $.ajax({
            url: 'ControlProveedores/actualizarCorreo',
            type: 'POST',
            data: {
                idProveedor: idProveedor,
                nuevoCorreo: nuevoCorreo
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnNevoCorreo', 2);
                    $('#modalNuevoCorreo').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    datosGenerales(idProveedor)
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnNevoCorreo', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnNevoCorreo', 1);
            }
        });
    });

    $(document).on('submit', '#nuevoRFC', function(event) {

        event.preventDefault();

        const idProveedor = document.getElementById('idProveedor').value;
        const nuevoRFC = document.getElementById('newRfc').value;
        //console.log('Id Del Proveedor: ' + idProveedor + " Nuevo RFC: " + nuevoRFC);
        $.ajax({
            url: 'ControlProveedores/actualizarRFC',
            type: 'POST',
            data: {
                idProveedor: idProveedor,
                nuevoRFC: nuevoRFC
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnNevoRFC', 2);
                    $('#modalNuevoRFC').modal('hide');
                    datosGenerales(idProveedor)
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnNevoRFC', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnNevoRFC', 1);
            }
        });
    });

    $(document).on('submit', '#formNewPass', function(event) {
        event.preventDefault();

        const idProveedor = document.getElementById('idProveedor').value;
        const nuevaPass = document.getElementById('newPass').value;

        $.ajax({
            url: 'ControlProveedores/actualizarPassword',
            type: 'POST',
            data: {
                idProveedor: idProveedor,
                nuevaPass: nuevaPass
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnNuevaPass', 2);
                    $('#modalNuevaPass').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    datosGenerales(idProveedor)
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnNuevaPass', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnNuevaPass', 1);
            }
        });
    });

    $(document).on('click', '#btnActualizaProveedores', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'ControlProveedores/actualizarProveedores',
            type: 'POST',
            data: {},
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    window.location.reload();
                } else {
                    notificaBad(respuesta.message);

                }
            },
            beforeSend: function() {
                $('#loadingOverlay').fadeIn();
            },
            complete: function() {
                $('#loadingOverlay').fadeOut();
            },
            error: function() {
                $('#loadingOverlay').fadeOut();
            }
        });
    });
</script>