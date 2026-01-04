<?php
$debug = 0;

if ($debug == 0) {
    echo 'Contenido de Data:' . PHP_EOL;
    var_dump($data);
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
            if ($data['dataCompra']['success'] == false) {
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

            $urlPDF = base64_encode($data['dataCompra']['data']['urlPDF']);
            $urlXML = base64_encode($data['dataCompra']['data']['urlXML']);

            if ($data['dataCompra']['data']['estatus'] == '3') {
                $datetime = new DateTime($data['dataCompra']['data']['fechaVal']); // Crear objeto DateTime
                $fechaRechazo = $datetime->format('d/m/Y'); // Formato deseado
?>
    <div class="card border-bottom border-left border-orange comment-widgets">
        <!-- Comment Row -->
        <div class="d-flex flex-row comment-row m-t-0">
            <div class="p-2">
                <img src="../../assets/images/favicon.png" alt="user" width="50" class="rounded-circle">
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
?>

<div>
    <div class="row show-grid">
        <div class="col-xs-12 col-md-8">
            <small class="text-muted">Proveedor </small>
            <h6><?= $data['dataCompra']['data']['idProveedor']; ?> - <?= $data['dataCompra']['data']['razonSocialEm']; ?></h6>
            <small class="text-muted">UUID </small>
            <h6><?= $data['dataCompra']['data']['uuid']; ?></h6>
            
            <p class="m-t-15">
                <b>Fecha Factura :</b> <?=$data['dataCompra']['data']['fechaFac'];?><br> 
                <b>Fecha Recepción :</b> <?=$data['dataCompra']['data']['fechaReg'];?>
            </p> 
        </div>
        <div class="col-xs-6 col-md-4">
            <div class="text-right">
                <br>
                Factura:
                <button data-fancybox type="button" data-type="pdf" data-preloader="true" data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDF; ?>/#toolbar=0" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                <button data-fancybox="xml" type="button" data-xml-url="<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlXML; ?>" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
            </div>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-6 col-md-4">
            <small class="text-muted">Serie y Folio </small>
            <h6><?= $data['dataCompra']['data']['serie'] . $data['dataCompra']['data']['folio']; ?></h6>
            <small class="text-muted p-t-30 db">Orden de Compra</small>
            <h6><?= $data['dataCompra']['data']['ordenCompra']; ?></h6>
        </div>
        <div class="col-xs-6 col-md-4">
            <small class="text-muted">Forma y Metodo de Pago </small>
            <h6><?= $data['dataCompra']['data']['idCatFormaPago']; ?> - <?= $data['dataCompra']['data']['idCatMetodoPago']; ?></h6>
            <small class="text-muted">Uso de CFDI </small>
            <h6><?= $data['dataCompra']['data']['usoCfdi']; ?></h6>
        </div>
        <div class="col-xs-6 col-md-4">
            <small class="text-muted">Subtotal </small>
            <h6><?= (empty($data['dataCompra']['data']['subTotal'])) ? 0 : number_format(abs($data['dataCompra']['data']['subTotal']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
            <small class="text-muted">Total </small>
            <h6><?= (empty($data['dataCompra']['data']['monto'])) ? 0 : number_format(abs($data['dataCompra']['data']['monto']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
        </div>
    </div>
    <div class="row show-grid"></div>

    <small class="text-muted p-t-30 db">No. de Recepción</small>
    <h6><?= $data['dataCompra']['data']['noRecepcion']; ?></h6>

    <hr>

    <?php
    // Obtener todas las notas de crédito relacionadas
    $notasCredito = $data['dataCompra']['data']['notasCredito'] ?? [];
    $contNotaCredito = count($notasCredito);
    
    if ($contNotaCredito > 0) {
    ?>
    <div class="m-t-30">
        <h4 class="m-b-20"><b>Notas de Crédito</b></h4>
        
        <div class="comment-widgets scrollable" style="max-height: 600px; overflow-y: auto;">
            <?php 
            $contadorNC = 1;
            foreach ($notasCredito as $nota) {
                // Determinar el badge de estatus
                $estatusBadge = '';
                $estatusClass = '';
                $borderClass = ''; // Clase para el borde izquierdo
                switch ($nota['estatus'] ?? 1) {
                    case 0:
                        $estatusBadge = 'Cancelada';
                        $estatusClass = 'label-danger';
                        break;
                    case 1:
                        $estatusBadge = 'Pendiente';
                        $estatusClass = 'label-info';
                        break;
                    case 2:
                        $estatusBadge = 'Aceptada';
                        $estatusClass = 'label-success';
                        $borderClass = 'border-left border-success';
                        break;
                    case 3:
                        $estatusBadge = 'Rechazada';
                        $estatusClass = 'label-danger';
                        $borderClass = 'border-left border-danger';
                        break;
                    default:
                        $estatusBadge = 'Pendiente';
                        $estatusClass = 'label-info';
                }
                
                $urlPDF = base64_encode($nota['urlPDF'] ?? '');
                $urlXML = base64_encode($nota['urlXML'] ?? '');
                $uuid = $nota['uuid'] ?? 'N/A';
                $serie = $nota['serie'] ?? '';
                $folio = $nota['folio'] ?? '';
                $fechaReg = isset($nota['fechaReg']) ? date('d/m/Y', strtotime($nota['fechaReg'])) : 'N/A';
                $total = isset($nota['total']) ? number_format(abs($nota['total']), 2, '.', ',') : '0.00';
                $moneda = $nota['moneda'] ?? 'MXN';
            ?>
            <!-- Comment Row - Nota de Crédito -->
            <div class="d-flex flex-row comment-row <?= $contadorNC === 1 ? 'm-t-0' : ''; ?>">
                <div class="comment-text active w-100 <?= $borderClass; ?>">
                    <div class="d-flex align-items-center p-b-15">
                        <div>
                            <h4 class="font-medium mb-0">
                                <i class="fas fa-file-invoice text-info mr-2"></i>Nota de Crédito #<?= $contadorNC; ?>
                            </h4>
                        </div>
                    </div>
                    <div class="m-b-15">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-fingerprint text-primary" style="font-size: 10px;"></i> UUID:
                                </small>
                                <div style="font-size: 12px; font-weight: 500; word-break: break-all;"><?= htmlspecialchars($uuid); ?></div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-hashtag text-info" style="font-size: 10px;"></i> Serie/Folio:
                                </small>
                                <div style="font-size: 12px; font-weight: 500;"><?= htmlspecialchars($serie . $folio); ?></div>
                            </div>
                        </div>
                        <div class="row mt-2" style="border-top: 1px solid #e0e0e0; padding-top: 8px;">
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">
                                    <i class="far fa-calendar-alt text-success" style="font-size: 10px;"></i> Fecha:
                                </small>
                                <div style="font-size: 12px; font-weight: 500;"><?= $fechaReg; ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-dollar-sign text-warning" style="font-size: 10px;"></i> Total:
                                </small>
                                <div style="font-size: 13px; font-weight: 600; color: #333;">$ <?= $total; ?> <?= $moneda; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="comment-footer">
                        <span class="text-muted float-right"><?= $fechaReg; ?></span>
                        <span class="label label-rounded <?= $estatusClass; ?>"><?= $estatusBadge; ?></span>
                        <span class="action-icons active">
                            <a href="javascript:void(0)" 
                               data-fancybox 
                               data-type="pdf" 
                               data-preloader="true" 
                               data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDF; ?>/#toolbar=0" 
                               class="text-danger">
                                <i class="far fa-file-pdf"></i> Ver PDF
                            </a>
                            <a href="javascript:void(0)" 
                               data-fancybox="xml" 
                               data-xml-url="<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlXML; ?>"
                               class="text-info">
                                <i class="far fa-file-code"></i> Ver XML
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            <?php 
                $contadorNC++;
            } 
            ?>
        </div>
    </div>
    <?php } ?>

</div>

</div>
</div>
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

        // Configurar manejadores para botones XML
        $("[data-fancybox='xml']").on("click", function(e) {
            e.preventDefault();
            const xmlUrl = $(this).attr('data-xml-url');
            const button = $(this);
            
            if (xmlUrl) {
                // Mostrar indicador de carga
                Fancybox.show([{
                    src: '<div style="padding: 40px; text-align: center;"><div class="loading text-center"><img src="/assets/images/loading.gif" alt="loading" /><br/>Cargando XML...</div></div>',
                    type: 'html'
                }], {
                    dragToClose: false,
                    click: "close"
                });

                // Cargar el XML
                fetch(xmlUrl)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Error al cargar el XML");
                        }
                        return response.text();
                    })
                    .then((xmlContent) => {
                        // Crear contenido HTML formateado para el XML
                        const formattedXML = `
                            <div style="padding: 20px; max-width: 100%; overflow: auto;">
                                <h4 style="margin-bottom: 15px; color: #333; font-weight: bold;">Contenido XML</h4>
                                <pre style="white-space: pre-wrap; word-wrap: break-word; background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.5; max-height: 70vh; overflow: auto; color: #212529;">${xmlContent.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</pre>
                            </div>
                        `;
                        
                        // Cerrar el modal de carga y abrir el modal con el contenido
                        Fancybox.close();
                        Fancybox.show([{
                            src: formattedXML,
                            type: 'html'
                        }], {
                            dragToClose: false,
                            click: "close"
                        });
                    })
                    .catch((error) => {
                        const errorHTML = `
                            <div style="padding: 40px; text-align: center;">
                                <p style="color: #dc3545; font-size: 16px; margin-bottom: 20px;">
                                    <i class="fas fa-exclamation-triangle"></i><br/>
                                    Error al cargar el XML: ${error.message}
                                </p>
                                <button onclick="Fancybox.close()" class="btn btn-primary">Cerrar</button>
                            </div>
                        `;
                        Fancybox.close();
                        Fancybox.show([{
                            src: errorHTML,
                            type: 'html'
                        }], {
                            dragToClose: false,
                            click: "close"
                        });
                    });
            }
        });
    });
</script>