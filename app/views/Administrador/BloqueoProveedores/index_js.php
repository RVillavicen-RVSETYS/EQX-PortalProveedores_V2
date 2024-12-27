<script>
    $(document).ready(function() {
        listaProveedores();
        periodoBloqueo();
    });

    function listaProveedores() {
        $.ajax({
            type: 'POST',
            url: 'BloqueoProveedores/listaProveedor',
            data: {},
            success: function(response) {
                $('#tarjetaListaProveedor').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaListaProveedor').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaListaProveedor').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function periodoBloqueo() {
        $.ajax({
            type: 'POST',
            url: 'BloqueoProveedores/periodoBloqueo',
            data: {},
            success: function(response) {
                $('#tarjetaPeriodo').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tarjetaPeriodo').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#tarjetaPeriodo').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    $(document).on('click', '#btnTodosFacturan', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'BloqueoProveedores/todosFacturan',
            type: 'POST',
            data: {},
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    listaProveedores();
                } else {
                    notificaBad(respuesta.message);
                }
            },
            beforeSend: function() {
                $('#loadingOverlay').fadeIn();
            },
            complete: function() {
                $('#loadingOverlay').fadeOut();
            },
            error: function() {
                $('#loadingOverlay').fadeOut();
            }
        });
    });

    $(document).on('click', '#btnNadieFactura', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'BloqueoProveedores/nadieFactura',
            type: 'POST',
            data: {},
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    listaProveedores();
                } else {
                    notificaBad(respuesta.message);
                }
            },
            beforeSend: function() {
                $('#loadingOverlay').fadeIn();
            },
            complete: function() {
                $('#loadingOverlay').fadeOut();
            },
            error: function() {
                $('#loadingOverlay').fadeOut();
            }
        });
    });

    $(document).on('click', '#btnActualizarLista', function(event) {
        event.preventDefault();

        $.ajax({
            url: 'BloqueoProveedores/actualizarLista',
            type: 'POST',
            data: {},
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    listaProveedores();
                } else {
                    notificaBad(respuesta.message);
                }
            },
            beforeSend: function() {
                $('#loadingOverlay').fadeIn();
            },
            complete: function() {
                $('#loadingOverlay').fadeOut();
            },
            error: function() {
                $('#loadingOverlay').fadeOut();
            }
        });
    });

    $(document).on('submit', '#formRegistraCierre', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'BloqueoProveedores/registraCierreAnual',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnRegistraCierre', 2);
                    $('#modalRegistraCierre').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    periodoBloqueo();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnRegistraCierre', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnRegistraCierre', 1);
            }
        });
    });

    $(document).on('click', '.btn-outline-info', function() {
        const proveedorId = $(this).data('id');
        $('#proveedorId').val(proveedorId);
    });

    $(document).on('submit', '#formEditaProv', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'BloqueoProveedores/editaBloqueo',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnEditaProv', 2);
                    $('#modalEditaProv').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    listaProveedores();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEditaProv', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEditaProv', 1);
            }
        });
    });
</script>