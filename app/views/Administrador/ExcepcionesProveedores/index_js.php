<script>
    $(document).ready(function() {
        cargarIgnoraDescuento();
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function cargarIgnoraDescuento() {
        $.ajax({
            type: 'POST',
            url: 'ExcepcionesProveedores/listaIgnoraDesc',
            data: {},
            success: function(response) {
                $('#ignoraDesc').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#ignoraDesc').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#ignoraDesc').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cargarExento() {
        $.ajax({
            type: 'POST',
            url: 'ExcepcionesProveedores/listaExentos',
            data: {},
            success: function(response) {
                $('#añoFisc').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#añoFisc').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#añoFisc').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cargarFechaEmision() {
        $.ajax({
            type: 'POST',
            url: 'ExcepcionesProveedores/listaFechaEmision',
            data: {},
            success: function(response) {
                $('#fechaEm').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#fechaEm').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#fechaEm').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cargarCfdiDistinto() {
        $.ajax({
            type: 'POST',
            url: 'ExcepcionesProveedores/listaCfdiDistinto',
            data: {},
            success: function(response) {
                $('#usoCFDI').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#usoCFDI').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#usoCFDI').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cambiarEstatus(estatus, ident, tabla) {

        $.ajax({
            url: 'ExcepcionesProveedores/cambiarEstatus',
            type: 'POST',
            data: {
                estatus: estatus,
                ident: ident,
                tabla: tabla
            },
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + tabla + ident, 2);
                    switch (tabla) {
                        case 1:
                            cargarIgnoraDescuento();
                            break;
                        case 2:
                            cargarExento();
                            break;
                        case 3:
                            cargarFechaEmision();
                            break;
                        case 4:
                            cargarCfdiDistinto();
                            break;
                    }

                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + tabla + ident, 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEstatus' + tabla + ident, 1);
            }
        });
    }

    $(document).on('submit', '#agregarProveedorIG', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'ExcepcionesProveedores/agregarProveedorIG',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarIgnoraDescuento();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorIG', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorIG', 1);
            }
        });
    });

    $(document).on('submit', '#agregarProveedorEAF', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'ExcepcionesProveedores/agregarProveedorEAF',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarExento();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorEAF', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorEAF', 1);
            }
        });
    });

    $(document).on('submit', '#agregarProveedorEFE', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'ExcepcionesProveedores/agregarProveedorEFE',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarFechaEmision();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorEFE', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorEFE', 1);
            }
        });
    });

    $(document).on('submit', '#agregarProveedorUC', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'ExcepcionesProveedores/agregarProveedorUC',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarCfdiDistinto();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorUC', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorUC', 1);
            }
        });
    });
</script>