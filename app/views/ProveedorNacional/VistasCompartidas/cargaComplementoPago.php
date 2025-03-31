<?php
$debug = 0;

if ($debug == 1) {
    echo 'Contenido de Data:' . PHP_EOL;
    var_dump($data);
    echo '<br><br>';
}

?>


<style>
    .custom-file-input~.custom-file-label::after {
        content: "Buscar";
        /* Cambia el texto "Browse" por "Buscar" */
    }
</style>

<div class="card">
    <div class="card-header bg-pyme-primary">
        <div class="row">
            <div class="col-8 col-sm-10">
                <h4 class="m-b-0 text-white">
                    <a class="btn-close hide-panel-toggle"><i class="fas fa-arrow-circle-left"></i></a> &nbsp;Carga Complementos de Pago
                </h4>
            </div>
            <div class="col-4 col-sm-2">
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="DetalleCompra" class="jsgrid" style="position: relative; height: auto; width: 100%;">
            <div class="row">
                <h4 class="m-t-20 m-b-20"><b>Registra tus Complementos de Pago</b></h4>

                <div class="col-12">
                    <form class="form" id="Form_CargaComplemento" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="complementoPDF"> Complemento de Pago</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="complementoPDF" name="complementoPDF" accept=".pdf" required>
                                    <label class="custom-file-label" for="complementoPDF"> Elegir PDF del Complemento..</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-file-code text-info"></i> &nbsp;XML</span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="complementoXML" name="complementoXML" accept=".xml" required>
                                    <label class="custom-file-label" for="complementoXML">Elegir XML del Complemento..</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="desbloquear-btn5" class="col-12 text-right">
                                <button type="submit" class="btn btn-success waves-effect waves-light">Cargar Complemento</button>
                            </div>
                            <div id="desbloquear-btn5" class="col-12 text-right" style="display: none;">
                                <img class="text-right" src="/assets/images/pointsLoad.gif" height="50px" alt="Cargando..." />
                            </div>
                        </div>
                        <hr>
                    </form>
                </div>
                <div class="col-12" id="respuestaCargaComplemento"></div>

                <!--    <div class="row show-grid">
                    <div class="col-xs-12 col-md-8">
                        <span class="text-muted">Proveedor </span>
                        <h6><?= $data['dataCompra']['data']['idProveedor']; ?> - <?= $data['dataCompra']['data']['razonSocialEm']; ?></h6>
                        <span class="text-muted">UUID </span>
                        <h6><?= $data['dataCompra']['data']['FacUUID']; ?></h6>

                        <p class="m-t-15">
                            <b>Fecha Factura :</b> <?= $data['dataCompra']['data']['fechaFac']; ?><br>
                            <b>Fecha Recepción :</b> <?= $data['dataCompra']['data']['fechaReg']; ?>
                        </p>
                    </div>
                    <div class="col-xs-6 col-md-4">
                        <div class="text-right">
                            <br>
                            <?php
                            $msjStatus = '';
                            switch ($data['dataCompra']['data']['CpaEstatus']) {
                                case '3':
                                    $msjStatus = '<span class="label label-danger label-rounded" style="font-size: 1.5em;">RECHAZADA</span>';
                                    break;

                                case '2':
                                    $msjStatus = '<span class="label label-success label-rounded" style="font-size: 1.5em;">APROBADA</span>';
                                    break;

                                case '1':
                                    $msjStatus = '<span class="label label-warning label-rounded" style="font-size: 1.5em;">EN REVISION</span>';
                                    break;

                                default:
                                    $msjStatus = '<span class="label label-warning label-rounded" style="font-size: 1.5em;">CANCELADA</span>';
                                    break;
                            }
                            if ($data['dataCompra']['data']['idPago'] > 0) {
                                $msjStatus = '<span class="label label-success label-rounded" style="font-size: 1.5em;">PAGADA</span>';
                            }
                            echo $msjStatus;
                            ?>
                            <br><br>
                            <button data-fancybox type="button" data-type="pdf" data-preloader="true" data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlFacPDF; ?>/#toolbar=0" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                            <button type="button" onclick="verFacturaXML('<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlFacXML; ?>')" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
                        </div>
                    </div>
                </div>

            </div> -->
            </div>
        </div>

        <div id="verCFDI" style="padding: 15px; min-height: 200px;">
        </div>

        <script src="/dist/js/custom.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Fancybox
        Fancybox.bind("[data-fancybox]", {
            // Opciones generales
            dragToClose: false,
            click: "close",
            // Opciones para PDF
            pdf: {
                iframe: {
                    // Opciones de iframe
                    preload: true
                }
            }
        });

        

        $("#Form_CargaComplemento").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            bloquearBtnComplemento('btn5');
            $.ajax({
                type: 'POST',
                url: 'Historico/registraComplementoPago',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {                            
                        resetFormulario("Form_CargaComplemento");
                        notificaSuc(response.message); // Muestra el mensaje OK
                    } else {
                        notificaBad(response.message); // Muestra el mensaje de error
                    }
                    desbloquearBtnComplemento('btn5');
                },
                error: function() {
                    notificaBad('Error al querer cargar factura. Consulta a tu administrador');
                    desbloquearBtnComplemento('btn5');
                },
                complete: function() {
                    // Rehabilitar el botón
                    desbloquearBtnComplemento('btn5');

                    // Limpiar los campos Inputs
                    //resetFormulario("Form_CargaComplemento");
                }
            });
        });

    });

    function resetFormulario(idForm) {
        $('#'+idForm)[0].reset();
        $(".custom-file-input").each(function() {
            $(this).val(''); // Restablece el input
            $(this).next('.custom-file-label').text('Elegir archivo...'); // Restablece el label
        });
    }
    

    function bloquearBtnComplemento(btn) {
        $('button[type="submit"]').prop('disabled', true);
        $('#desbloquear-' + btn).hide();
        $('#bloquear-' + btn).show();
    }

    function desbloquearBtnComplemento(btn) {
        $('button[type="submit"]').prop('disabled', false);
        $('#desbloquear-' + btn).show();
        $('#bloquear-' + btn).hide();
    }
</script>