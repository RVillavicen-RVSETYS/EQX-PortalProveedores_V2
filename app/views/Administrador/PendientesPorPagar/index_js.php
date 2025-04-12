<script>
    $(document).ready(function() {
        cargarFacturas();

        $('#formConsultaPendientesPorPagar').submit(function(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'PendientesPorPagar/listaPendientesPorPagar',
                data: $(this).serialize(),
                success: function(response) {
                    $('#tarjetaListaPendientesPagar').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#tarjetaListaPendientesPagar').html('Error en el inicio de sesión.Consulta a tu administrador');
                },
                beforeSend: function() {
                    $('#tarjetaListaPendientesPagar').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                }
            });
        });
    });

    function detalleCompra(acuse, idProveedor) {
        $('#customizer_body').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
        $(".customizer").toggleClass('show-service-panel');
        $(".service-panel-toggle").toggle();
        $.post("PendientesPorPagar/detalladoDeCompra", {
                acuse: acuse,
                idProveedor: idProveedor
            },
            function(respuesta) {
                $("#customizer_body").html(respuesta);
            });
    }

    function cargarFacturas() {
        $.ajax({
            type: 'POST',
            url: 'PendientesPorPagar/listaPendientesPorPagar',
            data: {},
            success: function(response) {
                $('#tarjetaListaPendientesPagar').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaPendientesPagar').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaPendientesPagar').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }
</script>