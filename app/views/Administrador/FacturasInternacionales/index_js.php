<script>
    $(document).ready(function() {
        cargarTablaFacturasInt();

        $('#formConsultaAprobaciones').submit(function(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'FacturasInternacionales/listaAprobacionesInter',
                data: $(this).serialize(),
                success: function(response) {
                    $('#tarjetaListaAprob').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#tarjetaListaAprob').html('Error en el inicio de sesión.Consulta a tu administrador');
                },
                beforeSend: function() {
                    $('#tarjetaListaAprob').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                }
            });
        })
    });

    function cargarTablaFacturasInt() {
        $.ajax({
            type: 'POST',
            url: 'FacturasInternacionales/listaAprobacionesInter',
            data: {},
            success: function(response) {
                $('#tarjetaListaAprob').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaAprob').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaAprob').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }
</script>