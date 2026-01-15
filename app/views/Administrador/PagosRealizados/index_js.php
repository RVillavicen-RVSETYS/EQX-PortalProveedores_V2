<script>
    $(document).ready(function() {
        listarPagos();
    });

    function listarPagos() {
        $.ajax({
            url: 'PagosRealizados/listarPagosRealizados',
            type: 'POST',
            data: {},
            success: function(response) {
                $('#tarjetaListaPagos').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaPagos').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaPagos').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('submit', '#filtroHistorial', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'PagosRealizados/listarPagosRealizados',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#tarjetaListaPagos').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaPagos').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaPagos').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });
</script>