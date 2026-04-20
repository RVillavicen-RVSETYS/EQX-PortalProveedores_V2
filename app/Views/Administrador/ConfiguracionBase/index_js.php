<script>
    $(document).ready(function() {
        gestionarInputsTolerancia();

    });

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

        $('#cantComplemento').val('');
        $('input[name="diasPago[]"]').prop('checked', false);

        if (!idEmpresa) {
            return;
        }

        $.ajax({
            url: '<?= URL_BASE_PROYECT ?>/Administrador/ConfiguracionBase/obtenerConfiguracionEmpresa',
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
                console.log('No se pudo cargar la configuración de la empresa.');
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
                    toastr.success(response.message, 'Configuración Guardada');
                } else {
                    toastr.error(response.message, 'No se pudo guardar');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error de comunicación con el servidor. Intente nuevamente.', 'Error del Sistema');
            },
            complete: function() {
                bloqueoBtn('btnGuardarConfig', 2);
            }
        });
    });

    $(document).on('submit', '#formConfiguracionGral', function(event) {
        event.preventDefault();

        $('#btnGuardar').prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '<?= URL_BASE_PROYECT ?>/Administrador/ConfiguracionBase/guardarConfiguracionGral',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(respuesta) {
                if (respuesta && respuesta.success) {
                    notificaSucSweet('Excelente!!', respuesta.message);
                    $('#formConfiguracionGral')[0].reset();
                    $('#empresa').val('').trigger('change');
                } else {
                    notificaBadSweet('Error', respuesta ? respuesta.message : 'Respuesta inválida del servidor.');
                }
                $('#btnGuardar').prop('disabled', false).text('Guardar');
            },
            error: function() {
                notificaBadSweet('Error', 'No se pudo guardar la configuración. Consulta a tu administrador.');
                $('#btnGuardar').prop('disabled', false).text('Guardar');
            }
        });
    });
</script>