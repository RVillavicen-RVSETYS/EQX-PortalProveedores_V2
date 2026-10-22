<script>
    $(document).ready(function() {
        listarNotasCredito();
    });

    function listarNotasCredito() {
        $.ajax({
            url: 'NotasCredito/listaNotasCredito',
            type: 'POST',
            data: {},
            success: function(response) {
                $('#tarjetaListaNotasCredito').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaNotasCredito').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaNotasCredito').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('submit', '#filtroHistorial', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'NotasCredito/listaNotasCredito',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#tarjetaListaNotasCredito').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaNotasCredito').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaNotasCredito').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });
</script>