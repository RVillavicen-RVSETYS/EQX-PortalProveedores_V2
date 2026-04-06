<script>
    $(document).ready(function() {
        listarReporteContable();
    });

    function listarReporteContable() {
        $.ajax({
            url: 'ReporteContable/listarReporteContable',
            type: 'POST',
            data: {},
            success: function(response) {
                $('#tarjetaReporteContable').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaReporteContable').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaReporteContable').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('submit', '#filtroHistorial', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'ReporteContable/listarReporteContable',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#tarjetaReporteContable').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaReporteContable').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaReporteContable').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    });
</script>