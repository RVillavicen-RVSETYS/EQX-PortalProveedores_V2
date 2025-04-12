<script>
    $(document).ready(function() {
        cargarLista();
    });

    function cargarLista() {
        $.ajax({
            url: 'DescargaFacturas/listarFacturas',
            type: 'POST',
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
            url: 'DescargaFacturas/listarFacturas',
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
</script>