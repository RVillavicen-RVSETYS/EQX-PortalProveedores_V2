<script>
    $(document).ready(function() {
        gestionarInputsTolerancia();

        // Cargar correos de la empresa de la sesión al iniciar
        $.ajax({
            url: 'ConfiguracionBase/obtenerConfiguracionCorreo',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#correoRechazoFactura').val(res.data);
                }
            }
        });

    });

    function limpiarInfoEmpresa() {
        $('#selectRazonSocial').text('Empresa');
        $('#selectRFC').text('RFC');
        $('#limiteComplementos').text('Limite de Complementos');
        $('#diasPago').text('Dias de Pago');
        $('#logoEmpresa').attr('src', '../assets/images/sinImagen.png');
    }

    function formatearDiasPago(diasPago) {
        const diasSemana = {
            '1': 'Lunes',
            '2': 'Martes',
            '3': 'Miercoles',
            '4': 'Jueves',
            '5': 'Viernes',
            '6': 'Sabado',
            '7': 'Domingo'
        };

        if (!diasPago) {
            return 'Dias de Pago';
        }

        return diasPago
            .split(',')
            .map(function(dia) {
                return diasSemana[dia.trim()] || dia.trim();
            })
            .join(', ');
    }

    function gestionarInputsTolerancia() {
        const tipoRegla = $('#tipoRegla').val();
        const $containerMonto = $('#containerMonto');
        const $containerPorcentaje = $('#containerPorcentaje');
        const $inputMonto = $('#montoTolerancia');
        const $inputPorcentaje = $('#porcentajeTolerancia');

        $containerMonto.hide();
        $containerPorcentaje.hide();
        $inputMonto.prop('required', false);
        $inputPorcentaje.prop('required', false);

        switch (tipoRegla) {
            case '1':
                $containerMonto.fadeIn();
                $inputMonto.prop('required', true);
                break;
            case '2':
                $containerPorcentaje.fadeIn();
                $inputPorcentaje.prop('required', true);
                break;
            case '3':
                $containerMonto.fadeIn();
                $containerPorcentaje.fadeIn();
                $inputMonto.prop('required', true);
                $inputPorcentaje.prop('required', true);
                break;
        }
    }

    $(document).on('change', '#empresa', function() {
        const idEmpresa = $(this).val();

        $('#idEmpresaCorreo').val(idEmpresa); // Sincroniza el ID para la pestaña de correos

        $('#cantComplemento').val('');
        $('input[name="diasPago[]"]').prop('checked', false);

        if (!idEmpresa) {
            return;
        }

        $.ajax({
            url: 'ConfiguracionBase/obtenerConfiguracionEmpresa',
            type: 'POST',
            dataType: 'json',
            data: {
                idEmpresa: idEmpresa
            },
            success: function(respuesta) {
                if (respuesta && respuesta.success && respuesta.data) {
                    const config = respuesta.data;

                    $('#cantComplemento').val(config.maxComplementosPendientes);
                    if (config.diasPago) {
                        const dias = config.diasPago.split(',');
                        dias.forEach(function(dia) {
                            $('input[name="diasPago[]"][value="' + dia.trim() + '"]').prop('checked', true);
                        });
                    }
                }
            },
            error: function() {
                console.log('No se pudo cargar la configuraciÃ³n de la empresa.');
            }
        });

        // Cargar correos de notificación
        $.ajax({
            url: 'ConfiguracionBase/obtenerConfiguracionCorreo',
            type: 'POST',
            dataType: 'json',
            data: {
                idEmpresa: idEmpresa
            },
            success: function(res) {
                if (res.success) {
                    $('#correoRechazoFactura').val(res.data); // Llena el textarea con lo que hay en BD
                }
            }
        });

    });

    $(document).on('change', '#empresaSelect', function() {
        const idEmpresa = $(this).val();

        if (!idEmpresa) {
            limpiarInfoEmpresa();
            return;
        }

        $.ajax({
            url: 'ConfiguracionBase/obtenerConfiguracionEmpresa',
            type: 'POST',
            dataType: 'json',
            data: {
                idEmpresa: idEmpresa
            },
            success: function(respuesta) {
                if (respuesta && respuesta.success && respuesta.data) {
                    const config = respuesta.data;

                    $('#selectRazonSocial').text(config.Empresa || 'Empresa');
                    $('#selectRFC').text(config.RFC || 'RFC');
                    $('#limiteComplementos').text(config.maxComplementosPendientes || 'Limite de Complementos');
                    $('#diasPago').text(formatearDiasPago(config.diasPago));
                    if (config.Logo) {
                        $('#logoEmpresa').attr('src', '../' + config.Logo);
                    } else {
                        $('#logoEmpresa').attr('src', '../assets/images/sinImagen.png');
                    }

                } else {
                    limpiarInfoEmpresa();
                }
            },
            error: function() {
                limpiarInfoEmpresa();
                console.log('No se pudo cargar la configuraciÃ³n de la empresa.');
            }
        });
    });

    $(document).on('submit', '#formConfiguracionPrecios', function(event) {
        event.preventDefault();

        const formData = $(this).serialize();

        bloqueoBtn('btnGuardarConfig', 1);

        $.ajax({
            url: 'ConfiguracionBase/guardarConfiguracion',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    notificaSucSweet('Excelente!!', response.message);

                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    notificaBadSweet('Error', response.message);
                }
            },
            error: function(xhr, status, error) {
                notificaBadSweet('Error', 'Error de comunicaciÃ³n con el servidor. Intente nuevamente.');
            },
            complete: function() {
                bloqueoBtn('btnGuardarConfig', 2);
            }
        });
    });

    $(document).on('submit', '#formConfiguracionGral', function(event) {
        event.preventDefault();

        bloqueoBtn('btnGuardar', 1);

        $.ajax({
            url: 'ConfiguracionBase/guardarConfiguracionGral',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(respuesta) {
                if (respuesta && respuesta.success) {
                    notificaSucSweet('Excelente!!', respuesta.message);
                    $('#formConfiguracionGral')[0].reset();
                    $('#empresa').val('').trigger('change');
                } else {
                    notificaBadSweet('Error', respuesta ? respuesta.message : 'Respuesta invÃ¡lida del servidor.');
                }
                bloqueoBtn('btnGuardar', 2);
            },
            error: function() {
                notificaBadSweet('Error', 'No se pudo guardar la configuraciÃ³n. Consulta a tu administrador.');
                bloqueoBtn('btnGuardar', 2);
            }
        });
    });

    $(document).on('submit', '#formConfiguracionCorreo', function(event) {
        event.preventDefault(); // Evita que la página se recargue

        // 2. Validar formato de los correos (NUEVO)
        // Pasamos el ID del textarea y un nombre para el mensaje de error
        if (!validarCorreosMultiples('correoRechazoFactura', 'Rechazo de Factura')) {
            return; // Si no es válido, se detiene aquí y no llega al AJAX
        }

        const formData = $(this).serialize();

        // Puedes usar la función de bloqueo que ya tienes para mostrar el spinner
        // Asegúrate de que el ID del botón coincida o usa uno genérico
        bloqueoBtn('btnGuardarCorreo', 1);

        $.ajax({
            url: 'ConfiguracionBase/guardarConfiguracionCorreo', // Esta ruta la debes crear en el controlador
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Notificación premium que ya usas en el proyecto
                    notificaSucSweet('¡Guardado!', response.message);

                    // OPCIONAL: Si quieres "limpiar" visualmente o actualizar algo más
                    // pero no hace falta recargar.
                } else {
                    notificaBadSweet('Error', response.message);
                }
            },
            error: function() {
                notificaBadSweet('Error', 'Error de comunicación con el servidor.');
            },
            complete: function() {
                bloqueoBtn('btnGuardarCorreo', 2); // Desbloquea el botón
            }
        });
    });

    // =====================================================
    // Metodo para Validar los campos de correos
    // =====================================================
    function validarCorreosMultiples(idCampo, nombreSeccion) {
        let valor = $("#" + idCampo).val().trim();
        //if (valor === "") return true; // Si está vacío es válido (aunque el textarea sea required)

        // 1. Limpiamos saltos de línea y dividimos por comas
        let lista = valor.split(',')
            .map(correo => correo.trim().replace(/[\n\r]/g, ""))
            .filter(correo => correo !== "");

        // 2. Expresión regular para validar formato
        let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let errorEncontrado = false;
        let correoMal = "";

        for (let email of lista) {
            if (!regex.test(email)) {
                errorEncontrado = true;
                correoMal = email;
                break;
            }
        }

        if (errorEncontrado) {
            // Usamos la notificación Sweet que ya tienes en el proyecto
            notificaBadSweet('Correo Inválido', "El correo '" + correoMal + "' en '" + nombreSeccion + "' no tiene un formato válido.");
            return false;
        }

        // 3. Devolvemos el campo limpio al textarea (formateado: correo1, correo2)
        $("#" + idCampo).val(lista.join(', '));
        return true;
    }
</script>