<?php
$debug = 0;

if ($debug == 1) {
    echo 'Contenido de Data:' . PHP_EOL;
    var_dump($data);
    echo '<br><br>';
}

$puedeAutorizar = (isset($data['puedeAutorizar']) && $data['dataCompra']['data']['CpaEstatus'] == '1') ? $data['puedeAutorizar'] : 0;
$puedeRechazar = (isset($data['puedeRechazar']) && $data['dataCompra']['data']['totalComplementos'] == 0 && $data['dataCompra']['data']['totalPagos'] == 0) ? $data['puedeRechazar'] : 0;
$fechaMin = date('Y-m-d', strtotime('-1 day'));
?>
<div class="card border">
    <div class="card-header bg-pyme-primary">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="d-flex align-items-center">
                <a class="btn-close hide-panel-toggle">
                    <i class="fas fa-arrow-circle-left"></i>
                </a>
                <h4 class="m-b-0 text-white ml-2">Acuse de Recepción: <?= $acuse; ?></h4>
            </div>

            <div class="btn-group text-white" role="group">
                <?php
                if($puedeAutorizar == 1 || $puedeRechazar == 1) {
                ?>                
                <button id="btnValidacion"
                    type="button"
                    class="btn btn-outline-primary dropdown-toggle text-white"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">
                    <i class="mdi mdi-label font-18"></i>
                    FechaPago: <?= $data['dataCompra']['data']['FechaProbablePago']; ?>
                </button>
                <div class="dropdown-menu" aria-labelledby="btnValidacion">
                    <?php
                    if($puedeAutorizar == 1) {
                    ?>
                    <a class="dropdown-item"
                        href="javascript:aceptarFactura(<?= $data['dataCompra']['data']['acuse'] ?>);">
                        <i class="fas fa-check-circle text-success"></i> Aceptar Factura
                    </a>
                    <?php
                    }
                    if($puedeRechazar == 1) {
                    ?>
                    <a class="dropdown-item"
                        href="javascript:rechazarFactura(<?= $data['dataCompra']['data']['acuse'] ?>);">
                        <i class="fas fa-times-circle text-danger"></i> Rechazar Factura
                    </a>
                    <?php
                    }
                    if($puedeAutorizar == 1) {
                    ?>
                    <a class="dropdown-item"
                        href="javascript:cambiarFecha(<?= $data['dataCompra']['data']['acuse'] ?>);">
                        <i class="fas fa-undo-alt text-info"></i> Nueva Fecha Pago
                    </a>
                    <?php
                    }
                    ?>
                </div>
                <?php
                } ?>
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
                <img src="/assets/images/SilmeAgro.ico" alt="user" width="50" class="rounded-circle">
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

            // Obtener el array de notas de crédito (ahora viene del modelo)
            $notasCreditoArray = $data['dataCompra']['data']['notasCredito'] ?? [];
            $contNotaCredito = count($notasCreditoArray); // Contar las notas de crédito reales
            $requiereComplementoPago = ($data['dataCompra']['data']['totalPagos'] > $data['dataCompra']['data']['totalPagos'] and $data['dataCompra']['data']['FacMetodoPago'] == 'PPD') ? 1 : 0;
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
                    <b>Fecha Vence :</b> <?= $data['dataCompra']['data']['FechaVence']; ?><br>
                    <b>Fecha de Pago :</b> <?= $data['dataCompra']['data']['FechaProbablePago']; ?><br>
                    <b>Fecha Recepción :</b> <?= $data['dataCompra']['data']['fechaReg']; ?><br>
                    <b>Fecha Factura :</b> <?= $data['dataCompra']['data']['fechaFac']; ?><br>
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
                    if ($data['dataCompra']['data']['totalPagos'] > 0) {
                        $msjStatus = '<span class="label label-success label-rounded" style="font-size: 1.5em;">PAGADA</span>';
                    }
                    echo $msjStatus;
                    ?>
                    <br><br>
                    <button data-fancybox type="button" data-type="pdf" data-preloader="true" data-src="<?= '/Administrador/FacturasNacionales/verDocumento/PDF/' . $urlFacPDF; ?>/#toolbar=0" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                    <button data-fancybox="xml" type="button" data-xml-url="<?= '/Administrador/FacturasNacionales/verDocumento/XML/' . $urlFacXML; ?>" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
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
                <h6><?= (empty($data['dataCompra']['data']['FacSubtotal'])) ? 0 : '$ ' . number_format(abs($data['dataCompra']['data']['FacSubtotal']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['FacTipoMoneda']; ?></h6>
                <span class="text-muted">Total </span>
                <h6><?= (empty($data['dataCompra']['data']['FacMonto'])) ? 0 : '$ ' . number_format(abs($data['dataCompra']['data']['FacMonto']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['FacTipoMoneda']; ?></h6>
            </div>
        </div>
        <div class="row show-grid"></div>

        <span class="text-muted p-t-30 db">No. de Recepción</span>
        <h6><?= $data['dataCompra']['data']['noRecepcion']; ?></h6>
    </div>
    <!-- End Tab 1 -->

    <?php if ($contNotaCredito > 0) { ?>
        <div class="tab-pane fade" id="notaCredito" role="tabpanel" aria-labelledby="nota-credito-tab">
            <h4 class="m-t-20 m-b-20"><b>Notas de Crédito</b></h4>
            
            <?php
            // Obtener todas las notas de crédito relacionadas (ahora viene del modelo)
            $notasCredito = $data['dataCompra']['data']['notasCredito'] ?? [];
            
            if (empty($notasCredito)) {
                echo '<div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se encontraron notas de crédito registradas para esta factura.
                </div>';
            } else {
            ?>
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
                            <div class="ml-auto">
                                <div class="dl">
                                    <?php 
                                    $estatusActual = $nota['estatus'] ?? 1;
                                    ?>
                                    <select class="custom-select border-0 text-muted cambiarEstatusNC" data-id-nc="<?= $nota['id'] ?? ''; ?>" data-uuid-nc="<?= htmlspecialchars($uuid); ?>" data-estatus-inicial="<?= $estatusActual; ?>">
                                        <option value="1" <?= $estatusActual == 1 ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="2" <?= $estatusActual == 2 ? 'selected' : ''; ?>>Aceptada</option>
                                        <option value="3" <?= $estatusActual == 3 ? 'selected' : ''; ?>>Rechazada</option>
                                    </select>
                                </div>
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
                                   data-src="<?= '/Administrador/FacturasNacionales/verDocumento/PDF/' . $urlPDF; ?>/#toolbar=0" 
                                   class="text-danger">
                                    <i class="far fa-file-pdf"></i> Ver PDF
                                </a>
                                <a href="javascript:void(0)" 
                                   data-fancybox="xml" 
                                   data-xml-url="<?= '/Administrador/FacturasNacionales/verDocumento/XML/' . $urlXML; ?>"
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
            <?php } ?>
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
                            Favor de Solicitar El Complemento de Pago al Proveedor.
                        </div> ';
            } else { ?>
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
                            <button data-fancybox type="button" data-type="pdf" data-preloader="true" data-src="<?= '/Administrador/FacturasNacionales/verDocumento/PDF/' . $urlFacPDF; ?>/#toolbar=0" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                            <button data-fancybox="xml" type="button" data-xml-url="<?= '/Administrador/FacturasNacionales/verDocumento/XML/' . $urlFacXML; ?>" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
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

    function aceptarFactura(acuse) {
        Swal.fire({
            title: '¿Aceptar?',
            text: "¿Estas seguro de aceptar esta facura?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'FacturasNacionales/aceptarFactura',
                    type: 'POST',
                    data: {
                        acuse: acuse,
                    },
                    success: function(response) {
                        const respuesta = JSON.parse(response);
                        if (respuesta.success) {
                            Swal.fire(
                                'Correcto',
                                'Factura Aceptada Correctamente.',
                                'success'
                            )
                            $(".customizer").toggleClass('show-service-panel');
                            $(".service-panel-toggle").toggle();
                            cargarFacturasNa();
                        } else {
                            Swal.fire(
                                'Error!',
                                'Error al recibir factura del proveedor, comunicate con tu administrador.',
                                'error'
                            )
                        }
                    }
                });
            }
        })
    }

    function cambiarFecha(acuse) {
        Swal.fire({
            title: 'Cambiar Fecha De Pago',
            html: `<div>Por favor, ingresa la nueva fecha de pago:<br><br> 
            <input class="form-control" type="date" min="<?= $fechaMin; ?>" name="nuevaFecha" id="nuevaFecha" style="width: 70%; margin: 0 auto;"></div>`,
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#3085d6',
            cancelButtonText: 'Cancelar',
            cancelButtonColor: '#d33',
            preConfirm: () => {
                const fecha = document.getElementById('nuevaFecha').value;
                if (!fecha) {
                    Swal.showValidationMessage('Debes ingresar una nueva fecha de pago.');
                }
                return fecha;
            }
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'FacturasNacionales/cambiarFechaPago',
                    type: 'POST',
                    data: {
                        acuse: acuse,
                        nuevaFecha: result.value
                    },
                    success: function(response) {
                        const respuesta = JSON.parse(response);
                        if (respuesta.success) {
                            notificaSuc(respuesta.message);
                            $(".customizer").toggleClass('show-service-panel');
                            $(".service-panel-toggle").toggle();
                            cargarFacturasNa();
                        } else {
                            notificaBad(respuesta.message);
                        }
                    }
                });
            }
        });
    }

    function rechazarFactura(acuse) {
        Swal.fire({
            title: '¿Estás seguro de rechazar la factura?',
            text: 'Por favor, ingresa el motivo del rechazo:',
            input: 'text',
            inputPlaceholder: 'Escribe el motivo aquí...',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#3085d6',
            cancelButtonText: 'Cancelar',
            cancelButtonColor: '#d33',
            inputValidator: (value) => {
                if (!value.trim()) {
                    return 'Debes ingresar un motivo para rechazar la factura.';
                }
            }
        }).then((result) => {
            if (result.value && result.value.trim()) {
                $.ajax({
                    url: 'FacturasNacionales/rechazarFactura',
                    type: 'POST',
                    data: {
                        acuse: acuse,
                        motivo: result.value.trim()
                    },
                    success: function(response) {
                        const respuesta = JSON.parse(response);
                        if (respuesta.success) {
                            notificaSuc(respuesta.message);
                            $(".customizer").toggleClass('show-service-panel');
                            $(".service-panel-toggle").toggle();
                            cargarFacturasNa();
                        } else {
                            notificaBad(respuesta.message);
                        }
                    }
                });
            }
        });
    }

    // Manejar cambio de estatus de Nota de Crédito
    $(document).on('change', '.cambiarEstatusNC', function() {
        const select = $(this);
        const idNC = select.attr('data-id-nc');
        const uuidNC = select.attr('data-uuid-nc');
        const nuevoEstatus = select.val();
        
        // Obtener el estatus inicial desde el atributo data
        if (!select.data('estatus-anterior')) {
            const estatusInicial = select.attr('data-estatus-inicial') || select.val();
            select.data('estatus-anterior', estatusInicial);
        }
        const estatusAnterior = select.data('estatus-anterior');

        // Si es rechazada (estatus 3), mostrar input para motivo
        if (nuevoEstatus == '3') {
            Swal.fire({
                title: '¿Rechazar Nota de Crédito?',
                text: 'Por favor, ingresa el motivo del rechazo:',
                input: 'text',
                inputPlaceholder: 'Escribe el motivo aquí...',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value.trim()) {
                        return 'Debes ingresar un motivo para rechazar la nota de crédito.';
                    }
                }
            }).then((result) => {
                if (result.value && result.value.trim()) {
                    // Enviar petición con motivo
                    $.ajax({
                        url: 'FacturasNacionales/actualizarEstatusNotaCredito',
                        type: 'POST',
                        data: {
                            idNC: idNC,
                            uuidNC: uuidNC,
                            estatus: nuevoEstatus,
                            motivoRechazo: result.value.trim()
                        },
                        success: function(response) {
                            const respuesta = JSON.parse(response);
                            if (respuesta.success) {
                                notificaSuc(respuesta.message);
                                // Recargar el panel para actualizar los datos
                                location.reload();
                            } else {
                                notificaBad(respuesta.message);
                                // Revertir el select al valor anterior
                                select.val(estatusAnterior);
                            }
                        },
                        error: function() {
                            notificaBad('Error al actualizar el estatus de la nota de crédito.');
                            // Revertir el select al valor anterior
                            select.val(estatusAnterior);
                        }
                    });
                } else {
                    // Si cancela o no ingresa motivo, revertir al valor anterior
                    select.val(estatusAnterior);
                }
            });
            return;
        }

        // Para otros estatus (1, 2), mostrar confirmación simple
        let titulo = '';
        let texto = '';
        let tipo = 'warning';
        let confirmColor = '#3085d6';

        switch(nuevoEstatus) {
            case '1':
                titulo = '¿Marcar como Pendiente?';
                texto = '¿Estás seguro de marcar esta nota de crédito como pendiente?';
                tipo = 'warning';
                break;
            case '2':
                titulo = '¿Aceptar Nota de Crédito?';
                texto = '¿Estás seguro de aceptar esta nota de crédito?';
                tipo = 'question';
                break;
        }

        Swal.fire({
            title: titulo,
            text: texto,
            type: tipo,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'FacturasNacionales/actualizarEstatusNotaCredito',
                    type: 'POST',
                    data: {
                        idNC: idNC,
                        uuidNC: uuidNC,
                        estatus: nuevoEstatus
                    },
                    success: function(response) {
                        const respuesta = JSON.parse(response);
                        if (respuesta.success) {
                            notificaSuc(respuesta.message);
                            // Recargar el panel para actualizar los datos
                            location.reload();
                        } else {
                            notificaBad(respuesta.message);
                            // Revertir el select al valor anterior
                            select.val(estatusAnterior);
                        }
                    },
                    error: function() {
                        notificaBad('Error al actualizar el estatus de la nota de crédito.');
                        // Revertir el select al valor anterior
                        select.val(estatusAnterior);
                    }
                });
            } else {
                // Si cancela, revertir al valor anterior
                select.val(estatusAnterior);
            }
        });
    });

</script>