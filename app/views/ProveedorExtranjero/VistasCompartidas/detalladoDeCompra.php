<?php
$debug = 0;

if ($debug == 1) {
    echo 'Contenido de Data:' . PHP_EOL;
    var_dump($data);
    echo '<br><br>';
}

?>
<div class="card border">
    <div class="card-header bg-pyme-primary">
        <div class="row">
            <div class="col-8 col-sm-10">
                <h4 class="m-b-0 text-white">
                    <a class="btn-close hide-panel-toggle"><i class="fas fa-arrow-circle-left"></i></a> &nbsp;Acuse de Recepción: <?= $acuse; ?>
                </h4>
            </div>
            <div class="col-4 col-sm-2">
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="DetalleCompra" class="jsgrid" style="position: relative; height: auto; width: 100%;">
            <?php
            if (!$data['dataCompra']['success']) {
            ?>
                <div class="alert alert-warning">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                    <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Los sentimos...</h3>
                    Hay un problema con este registro por favor notifica a tu administrador.
                    <br><b>Detalle:</b><?= $data['dataCompra']['message']; ?>
                </div>
        </div>
    </div>
</div>
<script src="/dist/js/custom.js"></script>
<?php
                exit(0);
            }

            //Preparando variables para mostrar la información de la compra
            $urlFacPDF = base64_encode($data['dataCompra']['data']['FacUrlPDF']);
            $urlFacXML = base64_encode($data['dataCompra']['data']['FacUrlXML']);

            if ($data['dataCompra']['data']['CpaEstatus'] == '3') {
                $datetime = new DateTime($data['dataCompra']['data']['fechaVal']); // Crear objeto DateTime
                $fechaRechazo = $datetime->format('d/m/Y'); // Formato deseado


            ?>
                <div class="card border-bottom border-left border-orange comment-widgets">
                    <!-- Comment Row -->
                    <div class="d-flex flex-row comment-row m-t-0">
                        <div class="p-2">
                            <img src="/assets/images/favicon.png" alt="user" width="50" class="rounded-circle">
                        </div>
                        <div class="comment-text w-100">
                            <h6 class="font-medium">Corrige y carga de nuevo.</h6>
                            <span class="m-b-15 d-block"><?= $data['dataCompra']['data']['comentRegresa']; ?> </span>
                            <div class="comment-footer">
                                <span class="text-muted float-right"><?= $fechaRechazo; ?></span>
                                <span class="label label-rounded label-danger">Rechazada</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
                
            $contNotaCredito = $data['dataCompra']['data']['notaCredito'];
            $requiereComplementoPago = ($data['dataCompra']['data']['idPago'] >= 1 AND $data['dataCompra']['data']['FacMetodoPago'] == 'PPD') ? 1 : 0 ;
            ?>
            <ul class="nav customizer-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="factura-tab" data-toggle="pill" href="#factura" role="tab" aria-controls="factura"
                        aria-selected="true">
                        <img src="/assets/images/icon/Factura.png" width="30px" alt="">
                    </a>
                </li>
                <?php if ($contNotaCredito > 0) { ?>
                    <li class="nav-item">
                        <a class="nav-link" id="nota-credito-tab" data-toggle="pill" href="#notaCredito" role="tab" aria-controls="notaCredito" aria-selected="false">
                            <img src="/assets/images/icon/NotaCredito.png" width="30px" alt="">
                        </a>
                    </li>
                <?php 
                } 
                
                if ($requiereComplementoPago > 0) { ?>
                    <li class="nav-item">
                        <a class="nav-link" id="complemento-pago-tab" data-toggle="pill" href="#complemento-pago" role="tab" aria-controls="complemento-pago"
                            aria-selected="false">
                            <img src="/assets/images/icon/ComplementoPago.png" width="30px" alt="">
                        </a>
                    </li>
                <?php } ?>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Tab 1 -->
                <div class="tab-pane fade show active" id="factura" role="tabpanel" aria-labelledby="factura-tab">
                    <h4 class="m-t-20 m-b-20"><b>Datos de Factura</b></h4>
                    <?php

                    ?>
                    <div class="row show-grid">
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
                    <div class="row show-grid">
                        <div class="col-xs-6 col-md-4">
                            <span class="text-muted">Serie y Folio </span>
                            <h6><?= $data['dataCompra']['data']['FacSerie'] . $data['dataCompra']['data']['FacFolio']; ?></h6>
                            <span class="text-muted p-t-30 db">Orden de Compra</span>
                            <h6><?= $data['dataCompra']['data']['ordenCompra']; ?></h6>
                        </div>
                        <div class="col-xs-6 col-md-4">
                            <span class="text-muted">Forma y Metodo de Pago </span>
                            <h6><?= $data['dataCompra']['data']['FacFormaPago']; ?> - <?= $data['dataCompra']['data']['FacMetodoPago']; ?></h6>
                            <span class="text-muted">Uso de CFDI </span>
                            <h6><?= $data['dataCompra']['data']['FacUsoCfdi'] . ' - ' . $data['dataCompra']['data']['nameUsoCfdi']; ?></h6>
                        </div>
                        <div class="col-xs-6 col-md-4">
                            <span class="text-muted">Subtotal </span>
                            <h6><?= (empty($data['dataCompra']['data']['FacSubtotal'])) ? 0 : number_format(abs($data['dataCompra']['data']['FacSubtotal']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
                            <span class="text-muted">Total </span>
                            <h6><?= (empty($data['dataCompra']['data']['FacMonto'])) ? 0 : number_format(abs($data['dataCompra']['data']['FacMonto']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
                        </div>
                    </div>
                    <div class="row show-grid"></div>

                    <span class="text-muted p-t-30 db">No. de Recepción</span>
                    <h6><?= $data['dataCompra']['data']['noRecepcion']; ?></h6>
                </div>
                <!-- End Tab 1 -->
                
                <?php if ($contNotaCredito > 0) { ?>
                <div class="tab-pane fade" id="notaCredito" role="tabpanel" aria-labelledby="nota-credito-tab">
                    <h4 class="m-t-20 m-b-20"><b>Nota de Credito</b></h4>
                    
                </div>
                <?php 
                } 
                if ($requiereComplementoPago > 0) { ?>
                <div class="tab-pane fade p-15" id="complemento-pago" role="tabpanel" aria-labelledby="complemento-pago-tab">
                    <h4 class="m-t-20 m-b-20"><b>Complemento de Pago</b></h4>
                    
                    <?php
                    if ($requiereComplementoPago > 0) {
                    echo '
                        <div class="alert alert-warning">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                            <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Complemento de Pago Pendiente...</h3>
                            Le solicitamos subir su complemento de pago para evitar bloqueos.
                        </div> ';
                    } else {?>
                        <div class="row show-grid">
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
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

        </div>
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
                    preload: false
                }
            }
        });
        
    });

    // Botón para cargar el XML
    function verFacturaXML(xmlUrl) {
        verCFDI = document.getElementById("verCFDI");
        verCFDI.innerHTML = '<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>';
        fetch(xmlUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Error al cargar el XML");
                }
                return response.text();
            })
            .then((xmlContent) => {
                verCFDI.innerHTML = `
                <pre style="white-space: pre-wrap; word-wrap: break-word; background: #f8f8f8; padding: 10px; border-radius: 5px;">
                    ${xmlContent.replace(/</g, "&lt;").replace(/>/g, "&gt;")}
                </pre>
            `;
            })
            .catch((error) => {
                verCFDI.innerHTML = `<p style="color: red;">Error al cargar el XML: ${error.message}</p>`;
            });
    }

</script>