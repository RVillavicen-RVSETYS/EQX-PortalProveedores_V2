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

    function mostrarFechas(valor) {
        const fechas = document.getElementById('fechas');

        if (valor === '2') {
            fechas.style.display = 'block'; // Mostrar las fechas
        } else {
            fechas.style.display = 'none'; // Ocultar las fechas
        }
    }
</script>