<script>
    $(document).ready(function() {
        // Llamada inicial para asegurar que el estado visual coincida con el select al cargar/recargar
        gestionarInputsTolerancia();
    });

    /**
     * Controla la visibilidad y obligatoriedad de los campos de monto y porcentaje
     * basándose en la selección del tipo de regla.
     */
    function gestionarInputsTolerancia() {
        const tipoRegla = $('#tipoRegla').val();
        const $containerMonto = $('#containerMonto');
        const $containerPorcentaje = $('#containerPorcentaje');
        const $inputMonto = $('#montoTolerancia');
        const $inputPorcentaje = $('#porcentajeTolerancia');

        // Ocultamos ambos y quitamos requerimientos por defecto
        $containerMonto.hide();
        $containerPorcentaje.hide();
        $inputMonto.prop('required', false);
        $inputPorcentaje.prop('required', false);

        // Switch basado en los IDs de tipo de regla definidos en la lógica de negocio
        switch (tipoRegla) {
            case '1': // Solo Monto
                $containerMonto.fadeIn();
                $inputMonto.prop('required', true);
                break;
            case '2': // Solo Porcentaje
                $containerPorcentaje.fadeIn();
                $inputPorcentaje.prop('required', true);
                break;
            case '3': // Ambos (Intersección)
                $containerMonto.fadeIn();
                $containerPorcentaje.fadeIn();
                $inputMonto.prop('required', true);
                $inputPorcentaje.prop('required', true);
                break;
        }
    }

    $(document).on('submit', '#formConfiguracionPrecios', function(event) {
        event.preventDefault();
        
        // 1. Recopilación de datos
        const formData = $(this).serialize();
        
        // 2. Feedback visual (Bloqueo de botón)
        // Esta función global oculta el botón de "Guardar" y muestra el spinner de carga
        bloqueoBtn('btnGuardarConfig', 1);

        // 3. Petición Asíncrona
        $.ajax({
            // Al estar en el mismo controlador, solo apunta al método
            url: '/Administrador/ConfiguracionBase/guardarConfiguracion',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                // 4. Manejo de la respuesta del Controlador
                if (response.success) {
                    toastr.success(response.message, 'Configuración Guardada');
                    // Opcional: podrías resetear el formulario si lo deseas
                } else {
                    toastr.error(response.message, 'No se pudo guardar');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error de comunicación con el servidor. Intente nuevamente.', 'Error del Sistema');
            },
            complete: function() {
                // 5. Restauración del botón (Unblock)
                bloqueoBtn('btnGuardarConfig', 2);
            }
        });
    });
</script>