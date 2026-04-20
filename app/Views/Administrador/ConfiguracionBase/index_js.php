<script>
    $(document).ready(function() {
        gestionarInputsTolerancia();
    });

    function limpiarInfoEmpresa() {
        $('#selectRazonSocial').text('Empresa');
        $('#selectRFC').text('RFC');
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
</script>
