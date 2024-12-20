<script>
    $(document).ready(function() {
        listaAlertas();
    });

    $(document).on('submit', '#formNuevaAlerta', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'Alertas/nuevaAlerta',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnNuevaAlerta', 2);
                    $('#modalNuevaAlerta').modal('hide');
                    listaAlertas();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnNuevaAlerta', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnNuevaAlerta', 1);
            }
        });
    });

    $(document).on('submit', '#formEditaAlerta', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'Alertas/editaAlerta',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnEditaAlerta', 2);
                    $('#modalEditaAlerta').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    listaAlertas();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEditaAlerta', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEditaAlerta', 1);
            }
        });
    });

    function listaAlertas() {
        $.ajax({
            type: 'POST',
            url: 'Alertas/listaAlertas',
            data: {},
            success: function(response) {
                $('#tarjetaListaAlertas').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaAlertas').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaAlertas').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cambiarEstatus(estatus, idNotificacion) {
        $.ajax({
            url: 'Alertas/cambiarEstatus',
            type: 'POST',
            data: {
                estatus: estatus,
                idNotificacion: idNotificacion
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + idNotificacion, 2);
                    listaAlertas();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + idNotificacion, 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEstatus' + idNotificacion, 1);
            }
        });
    }

    function mostrarFechas(valor, opcion) {
        if (opcion == 1) {
            var fechas = document.getElementById('fechas');
        } else {
            var fechas = document.getElementById('editFechas');
        }

        if (valor === '2') {
            fechas.style.display = 'block'; // Mostrar las fechas
        } else {
            fechas.style.display = 'none'; // Ocultar las fechas
        }
    }

    function cargarDatos(idNotificacion) {
        $.ajax({
            url: 'Alertas/cargarDatos',
            type: 'POST',
            data: {
                idNotificacion: idNotificacion
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    const data = respuesta.message[0];

                    $('#editIdNotificacion').val(data.IdNotificacion);
                    $('#editTitulo').val(data.Titulo);
                    $('#editDescripcion').val(data.Mensaje);
                    $('#editTipoMensaje').val(data.TipoMsj).trigger('change');
                    $('#editTipoProveedor').val(data.TipoProveedor).trigger('change');
                    $('#editTipoPeriodo').val(data.TipoPeriodo).trigger('change');

                    if (data.TipoPeriodo == '2') {
                        const fechas = document.getElementById('editFechas');
                        fechas.style.setProperty('display', 'block');
                        $('#editFechaInicio').val(data.Inicio);
                        $('#editFechaFin').val(data.Fin);
                    } else {
                        $('#editFechas').hide();
                    }

                } else {
                    notificaBad(respuesta.message);
                }
            },
            beforeSend: function() {

            }
        });
    }
</script>