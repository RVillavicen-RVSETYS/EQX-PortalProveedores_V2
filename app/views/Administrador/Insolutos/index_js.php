<script>
    $(document).ready(function() {
        listarInsolutos();
    });

    function listarInsolutos() {
        $.ajax({
            url: 'Insolutos/listarInsolutos',
            type: 'POST',
            data: {},
            success: function(response) {
                $('#tarjetaInsolutos').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaInsolutos').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaInsolutos').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('submit', '#filtroHistorial', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'Insolutos/listarInsolutos',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#tarjetaInsolutos').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaInsolutos').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaInsolutos').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });
</script>