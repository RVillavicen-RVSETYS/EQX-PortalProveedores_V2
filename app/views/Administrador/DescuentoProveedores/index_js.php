<script>
    $(document).ready(function() {
        listaProveedoresDesc();
    });

    function listaProveedoresDesc() {
        $.ajax({
            type: 'POST',
            url: 'DescuentoProveedores/listaProveedoresDesc',
            data: {},
            success: function(response) {
                $('#tarjetaProveedoresDescuento').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaProveedoresDescuento').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaProveedoresDescuento').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cambiarEstatus(estatus, idDescuento) {
        $.ajax({
            url: 'DescuentoProveedores/cambiarEstatus',
            type: 'POST',
            data: {
                estatus: estatus,
                idDescuento: idDescuento
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + idDescuento, 2);
                    listaProveedoresDesc();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + idDescuento, 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEstatus' + idDescuento, 1);
            }
        });
    }

    $(document).on('submit', '#agregaProveedor', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'DescuentoProveedores/agregarProveedorDesc',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedor', 2);
                    window.location.reload();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedor', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedor', 1);
            }
        });
    });
</script>