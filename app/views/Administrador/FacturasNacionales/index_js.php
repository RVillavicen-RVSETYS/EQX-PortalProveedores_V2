<script>
    $(document).ready(function() {
        cargarFacturasNa();

        $('#formConsultaAprobaciones').submit(function(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'FacturasNacionales/listaAprobacionesNa',
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
        });
    });

    function detalleCompra(acuse, idProveedor) {
        $('#customizer_body').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
        $(".customizer").toggleClass('show-service-panel');
        $(".service-panel-toggle").toggle();
        $.post("FacturasNacionales/detalladoDeCompra", {
                acuse: acuse,
                idProveedor: idProveedor
            },
            function(respuesta) {
                $("#customizer_body").html(respuesta);
            });
    }

    function cargarFacturasNa() {
        $.ajax({
            type: 'POST',
            url: 'FacturasNacionales/listaAprobacionesNa',
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