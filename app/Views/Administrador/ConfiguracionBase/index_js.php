<script>
    $(document).ready(function() {
        
        // Cargar datos cuando se cambia de empresa
        $(document).on('change', '#empresa', function() {
            const idEmpresa = $(this).val();
            
            // Limpiar formulario siempre al cambiar de empresa
            $('#cantComplemento').val('');
            $('input[name="diasPago[]"]').prop('checked', false);
            
            if (!idEmpresa) {
                // Si no hay empresa seleccionada, campos quedan vacíos
                return;
            }

            $.ajax({
                url: '<?= URL_BASE_PROYECT ?>/Administrador/ConfiguracionBase/obtenerConfiguracionEmpresa',
                type: 'POST',
                dataType: 'json',
                data: { idEmpresa: idEmpresa },
                success: function(respuesta) {
                    // Si existe configuración para esta empresa, cargarla
                    if (respuesta && respuesta.success && respuesta.data) {
                        const config = respuesta.data;
                        
                        // Cargar el límite de complementos
                        $('#cantComplemento').val(config.maxComplementosPendientes);
                        
                        // Marcar los días de pago que están en la BD
                        if (config.diasPago) {
                            const dias = config.diasPago.split(',');
                            dias.forEach(function(dia) {
                                $('input[name="diasPago[]"][value="' + dia.trim() + '"]').prop('checked', true);
                            });
                        }
                    }
                    // Si no hay datos, los campos ya están limpios
                },
                error: function() {
                    // En caso de error, los campos ya están limpios
                    console.log('No se pudo cargar la configuración de la empresa.');
                }
            });
        });

        // Enviar formulario
        $(document).on('submit', '#formConfiguracionGral', function(event) {
            event.preventDefault();

            // Deshabilitar el botón para evitar dobles clics
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
                    // Rehabilitar el botón
                    $('#btnGuardar').prop('disabled', false).text('Guardar');
                },
                error: function() {
                    notificaBadSweet('Error', 'No se pudo guardar la configuración. Consulta a tu administrador.');
                    // Rehabilitar el botón
                    $('#btnGuardar').prop('disabled', false).text('Guardar');
                }
            });
        });
    });
</script>