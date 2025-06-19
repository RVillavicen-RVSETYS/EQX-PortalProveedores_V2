<script>
    $(document).ready(function() {
        listarComplementos();
    });

    function listarComplementos() {
        $.ajax({
            url: 'PagosRealizados/listarPagosRealizados',
            type: 'POST',
            data: {},
            success: function(response) {
                $('#tarjetaListaComplementos').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaComplementos').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaComplementos').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
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
                $('#tarjetaListaComplementos').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaComplementos').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaComplementos').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });
</script>