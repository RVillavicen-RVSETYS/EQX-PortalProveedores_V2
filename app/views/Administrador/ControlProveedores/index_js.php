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
            }
        });
    }

    function recepciones(idProveedor) {
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/recepciones',
            data: $(this).serialize(),
            success: function(response) {
                $('#list-Recepciones').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#list-Recepciones').html('Error en el inicio de sesión.Consulta a tu administrador');
            }
        });
    }

    function sinFecha(idProveedor) {
        $.ajax({
            type: 'POST',
            url: 'ControlProveedores/sinFecha',
            data: $(this).serialize(),
            success: function(response) {
                $('#list-SinFecha').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#list-SinFecha').html('Error en el inicio de sesión.Consulta a tu administrador');
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
            }
        });
    }

    $('#nuevoCorreo').on('submit', function(event) {
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

    $('#nuevoRFC').on('submit', function(event) {
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
</script>