<script>
    $(document).on('submit', '#consultarProveedor', function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: 'Validador/validarFacturas',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#divDataValidacion').html(response);
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
</script>