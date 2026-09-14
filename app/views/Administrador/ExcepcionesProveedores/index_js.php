<script>
    // Base URL para AJAX: desde /Administrador/ExcepcionesProveedores la ruta relativa
    // "ExcepcionesProveedores/accion" se resuelve mal (duplica segmento). Usar path actual.
    var EXCEPCIONES_AJAX_BASE = (function() {
        var p = window.location.pathname;
        if (/ExcepcionesProveedores\/?$/i.test(p) || /ExcepcionesProveedores\/index\/?$/i.test(p)) {
            return p.replace(/\/index\/?$/i, '').replace(/\/?$/, '');
        }
        return '/Administrador/ExcepcionesProveedores';
    })();

    $(document).ready(function() {
        cargarIgnoraDescuento();
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Delegación: botón Deshabilitar PUE (contenido cargado por AJAX/DataTables)
    $(document).on('click', '.btn-deshabilitar-pue', function() {
        var idConf = $(this).data('id-conf');
        var idProveedor = $(this).data('id-proveedor');
        if (idConf != null && idProveedor != null && typeof deshabilitarPermisoPUE === 'function') {
            deshabilitarPermisoPUE(idConf, idProveedor);
        }
    });

    // Delegación: botón Reactivar PUE
    $(document).on('click', '[data-accion="reactivar-pue"]', function() {
        var idConf = $(this).data('id-conf');
        var idProveedor = $(this).data('id-proveedor');
        if (idConf != null && idProveedor != null && typeof cambiarEstatus === 'function') {
            cambiarEstatus(0, idConf, 6, idProveedor);
        }
    });

    function cargarIgnoraDescuento() {
        $.ajax({
            type: 'POST',
            url: EXCEPCIONES_AJAX_BASE + '/listaIgnoraDesc',
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
            url: EXCEPCIONES_AJAX_BASE + '/listaExentos',
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
            url: EXCEPCIONES_AJAX_BASE + '/listaFechaEmision',
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
            url: EXCEPCIONES_AJAX_BASE + '/listaCfdiDistinto',
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

    function cargarBloqueoDiferencias() {
        $.ajax({
            type: 'POST',
            url: EXCEPCIONES_AJAX_BASE + '/listaBloqueoDiferencias',
            data: {},
            success: function(response) {
                $('#bloqueoDif').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#bloqueoDif').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#bloqueoDif').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cargarPoliticasComerciales() {
        $.ajax({
            type: 'POST',
            url: EXCEPCIONES_AJAX_BASE + '/listaPoliticasComerciales',
            data: {},
            success: function(response) {
                $('#politicasComerciales').html(response);
            },
            error: function() {
                $('#politicasComerciales').html('Error al cargar la sección. Consulta a tu administrador.');
            },
            beforeSend: function() {
                $('#politicasComerciales').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function eliminarPoliticaComercial(idProveedor) {
        var idBtn = 'bloquear-btnPolitica' + idProveedor;
        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/eliminarProveedorPoliticasComerciales',
            type: 'POST',
            dataType: 'json',
            data: { idProveedor: idProveedor },
            success: function(respuesta) {
                if (respuesta && respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarPoliticasComerciales();
                } else {
                    notificaBad((respuesta && respuesta.message) || 'No se pudo quitar el proveedor.');
                }
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 2);
            },
            beforeSend: function() {
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 1);
            },
            error: function(xhr) {
                notificaBad(xhr.status === 404 ? 'Ruta no encontrada.' : 'Error al eliminar.');
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 2);
            }
        });
    }

    function eliminarProveedorFechaPagoIgnorada(idProveedor){
        var idBtn = 'bloquear-btnFechaPago' + idProveedor;
        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/eliminarProveedorFechaPagoIgnorada',
            type: 'POST',
            dataType: 'json',
            data: { idProveedor: idProveedor },
            success: function(respuesta) {
                if (respuesta && respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarAnulacionValidacionFechaPagoProveedor();
                } else {
                    notificaBad((respuesta && respuesta.message) || 'No se pudo quitar el proveedor.');
                }
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 2);
            },
            beforeSend: function() {
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 1);
            },
            error: function(xhr) {
                notificaBad(xhr.status === 404 ? 'Ruta no encontrada.' : 'Error al eliminar.');
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 2);
            }
        });
    }

    function cargarBloqueoDeCFDIs() {
        $.ajax({
            type: 'POST',
            url: EXCEPCIONES_AJAX_BASE + '/cfdisPorProveedor',
            data: {},
            success: function(response) {
                $('#bloqueoDeCfdis').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#bloqueoDeCfdis').html('Error en el inicio de sesión.Consulta a tu administrador');
            },
            beforeSend: function() {
                $('#bloqueoDeCfdis').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function cargarPermitirPueSiempre() {
        $.ajax({
            type: 'POST',
            url: EXCEPCIONES_AJAX_BASE + '/listaPermitirPueSiempre',
            data: {},
            success: function(response) {
                $('#permitirPueSiempre').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#permitirPueSiempre').html('Error al cargar la sección. Intente de nuevo.');
            },
            beforeSend: function() {
                $('#permitirPueSiempre').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function deshabilitarPermisoPUE(idConf, idProveedor) {
        var idBtn = 'bloquear-btnEstatus6' + idConf;
        Swal.fire({
            title: 'Deshabilitar permiso PUE',
            text: 'Ingresa el motivo por el que se deshabilitará este permiso',
            input: 'textarea',
            inputPlaceholder: 'Motivo de deshabilitación...',
            inputAttributes: {
                'aria-label': 'Motivo de deshabilitación',
                'rows': 3
            },
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
            preConfirm: function() {
                var input = Swal.getInput();
                var val = input ? input.value : '';
                if (!val || !String(val).trim()) {
                    Swal.showValidationMessage('Debes ingresar el motivo.');
                    return false;
                }
                return String(val).trim();
            }
        }).then(function(result) {
            if (result.value) {
                var motivoCancela = result.value;
                if ($('#' + idBtn).length) bloqueoBtn(idBtn, 1);
                $.ajax({
                    url: EXCEPCIONES_AJAX_BASE + '/cambiarEstatus',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        estatus: 1,
                        ident: idConf,
                        tabla: 6,
                        idProveedor: idProveedor,
                        motivoCancela: motivoCancela
                    },
                    success: function(respuesta) {
                        if (respuesta && respuesta.success) {
                            notificaSuc(respuesta.message);
                            cargarPermitirPueSiempre();
                        } else {
                            notificaBad((respuesta && respuesta.message) || 'Error al deshabilitar.');
                        }
                        if ($('#' + idBtn).length) bloqueoBtn(idBtn, 2);
                    },
                    error: function(xhr, status, err) {
                        if (typeof console !== 'undefined' && console.error) {
                            console.error('AJAX deshabilitar PUE:', status, err, xhr.responseText);
                        }
                        notificaBad(xhr.status === 404 ? 'Ruta no encontrada. Revisa la URL del módulo.' : 'Error al deshabilitar el permiso.');
                        if ($('#' + idBtn).length) bloqueoBtn(idBtn, 2);
                    }
                });
            }
        });
    }

    // Para el nuevo apartado de Ignorar Fecha Pago
    function cargarAnulacionValidacionFechaPagoProveedor() {
        $.ajax({
            type: 'POST',
            url: EXCEPCIONES_AJAX_BASE + '/listaAnulacionValidacionFechaPagoProveedor',
            data: {},
            success: function(response) {
                $('#fechaPagoProveedor').html(response);
            },
            error: function() {
                $('#fechaPagoProveedor').html('Error al cargar la sección. Consulta a tu administrador.');
            },
            beforeSend: function() {
                $('#fechaPagoProveedor').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            }
        });
    }

    function agregarAnulacionValidacionFechaPagoProveedor() {
    }

    function cambiarEstatus(estatus, ident, tabla, idProveedor) {

        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/cambiarEstatus',
            type: 'POST',
            dataType: 'json',
            data: {
                estatus: estatus,
                ident: ident,
                tabla: tabla,
                idProveedor: idProveedor
            },
            success: function(respuesta) {
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
                        case 5:
                            cargarBloqueoDiferencias();
                            break;
                        case 6:
                            cargarPermitirPueSiempre();
                            break;
                    }

                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus' + tabla + ident, 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEstatus' + tabla + ident, 1);
            },
            error: function(xhr, status, err) {
                if (typeof console !== 'undefined' && console.error) {
                    console.error('AJAX cambiarEstatus:', status, err, xhr.responseText);
                }
                notificaBad(xhr.status === 404 ? 'Ruta no encontrada.' : 'Error al cambiar estatus.');
                bloqueoBtn('bloquear-btnEstatus' + tabla + ident, 2);
            }
        });
    }

    function eliminar(ident) {
        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/eliminar',
            type: 'POST',
            data: {
                ident: ident,
            },
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus5' + ident, 2);
                    cargarBloqueoDiferencias();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnEstatus5' + ident, 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnEstatus5' + ident, 1);
            }
        });
    }

    function eliminarCfdiPermitido(ident) {
        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/eliminarCfdiPermitido',
            type: 'POST',
            data: {
                ident: ident,
            },
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarBloqueoDeCFDIs();
                } else {
                    notificaBad(respuesta.message);
                }
            },
            beforeSend: function() {}
        });
    }

    $(document).on('submit', '#agregarProveedorIG', function(event) {

        event.preventDefault();

        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorIG',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
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
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorEAF',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
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
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorEFE',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
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
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorUC',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
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

    $(document).on('submit', '#agregarProveedorBD', function(event) {

        event.preventDefault();

        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorBD',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarBloqueoDiferencias();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorBD', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorBD', 1);
            }
        });
    });

    $(document).on('submit', '#agregarProveedorPoliticaComercial', function(event) {
        event.preventDefault();
        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorPoliticasComerciales',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(respuesta) {
                if (respuesta && respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarPoliticasComerciales();
                    $('#motivoPoliticaComercial').val('');
                    $('#idProveedorPolitica').val(null).trigger('change');
                } else {
                    notificaBad((respuesta && respuesta.message) || 'No se pudo guardar.');
                    bloqueoBtn('bloquear-btnAgregaProveedorPolitica', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorPolitica', 1);
            },
            error: function(xhr) {
                notificaBad(xhr.status === 404 ? 'Ruta no encontrada.' : 'Error al guardar.');
                bloqueoBtn('bloquear-btnAgregaProveedorPolitica', 2);
            }
        });
    });

    $(document).on('submit', '#agregarProveedorPUE', function(event) {
        event.preventDefault();
        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorPUE',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarPermitirPueSiempre();
                    $('#agregarProveedorPUE')[0].reset();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorPUE', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorPUE', 1);
            }
        });
    });

    $(document).on('submit', '#agregarAnulacionValidacionFechaPagoProveedor', function(event) {

        event.preventDefault();

        $.ajax({
            url: EXCEPCIONES_AJAX_BASE + '/agregarProveedorIFP',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var respuesta = typeof response === 'string' ? JSON.parse(response) : response;
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarAnulacionValidacionFechaPagoProveedor();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorFechaPagoInvalidada', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorFechaPagoInvalidada', 1);
            }
        });
    });
</script>