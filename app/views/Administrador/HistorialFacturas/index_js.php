<script>
    $(document).ready(function() {
        historialFact();
    });

    function historialFact() {
        $.ajax({
            type: 'POST',
            url: 'HistorialFacturas/listarFacturas',
            data: {},
            success: function(response) {
                $('#tarjetaListaFacturas').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaFacturas').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaFacturas').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('submit', '#filtroHistorial', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'HistorialFacturas/listarFacturas',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#tarjetaListaFacturas').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaFacturas').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaFacturas').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });

    $(document).on('submit', '#formRangoBuscaPago', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'HistorialFacturas/buscarPagos',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    $('#modalFechasPagos').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    historialFact();
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