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
        // Lógica AJAX para enviar al controlador (se implementará en el siguiente paso)
        console.log("Formulario serializado:", $(this).serialize());
    });
</script>