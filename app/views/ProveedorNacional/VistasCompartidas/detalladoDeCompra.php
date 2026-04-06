<?php
$debug = 0;

if ($debug == 1) {
    echo 'Contenido de Data:' . PHP_EOL;
    var_dump($data);
    echo '<br><br>';
}

$totalImpuestos = 0;
$totalImpuestos = $data['dataCompra']['data']['totalImpuestosTrasladados'] + $data['dataCompra']['data']['totalImpuestosRetenidos'];

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

            // Obtener el array de notas de crédito y complementos de pago
            $notasCreditoArray = $data['dataCompra']['data']['notasCredito'] ?? [];
            $contNotaCredito = count($notasCreditoArray); // Contar las notas de crédito reales
            $facturasPendientes = ($data['dataCompra']['data']['CpaEstatus'] ?? '') === '1' ? 1 : 0;
            $notasCreditoPendientes = 0;
            foreach ($notasCreditoArray as $notaCredito) {
                if (($notaCredito['estatus'] ?? '') === '1') {
                    $notasCreditoPendientes++;
                }
            }
            $complementosPago = $data['dataCompra']['data']['complementosPago'] ?? [];
            $contComplementosPago = count($complementosPago);
            $complementosPendientes = 0;
            foreach ($complementosPago as $complemento) {
                if (($complemento['estatus'] ?? '') === '1') {
                    $complementosPendientes++;
                }
            }
            $requiereComplementoPago = ($data['dataCompra']['data']['totalPagos'] > $data['dataCompra']['data']['totalComplementos'] && $data['dataCompra']['data']['FacMetodoPago'] == 'PPD') ? 1 : 0;
            $mostrarComplementoPago = ($contComplementosPago > 0 || $requiereComplementoPago > 0) ? 1 : 0;
?>

<ul class="nav customizer-tab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="factura-tab" data-toggle="pill" href="#factura" role="tab" aria-controls="factura"
            aria-selected="true" style="position: relative;">
            <img src="/assets/images/icon/Factura.png" width="30px" alt="">
            <?php if ($facturasPendientes > 0) { ?>
                <span class="badge badge-danger" style="position: absolute; top: -6px; right: -6px; font-size: 10px; padding: 2px 5px;"><?= $facturasPendientes; ?></span>
            <?php } ?>
        </a>
    </li>
    <?php if ($contNotaCredito > 0) { ?>
        <li class="nav-item">
            <a class="nav-link" id="nota-credito-tab" data-toggle="pill" href="#notaCredito" role="tab" aria-controls="notaCredito" aria-selected="false" style="position: relative;">
                <img src="/assets/images/icon/NotaCredito.png" width="30px" alt="">
                <?php if ($notasCreditoPendientes > 0) { ?>
                    <span class="badge badge-danger" style="position: absolute; top: -6px; right: -6px; font-size: 10px; padding: 2px 5px;"><?= $notasCreditoPendientes; ?></span>
                <?php } ?>
            </a>
        </li>
    <?php
    }

    if ($mostrarComplementoPago > 0) { ?>
        <li class="nav-item">
            <a class="nav-link" id="complemento-pago-tab" data-toggle="pill" href="#complemento-pago" role="tab" aria-controls="complemento-pago"
                aria-selected="false" style="position: relative;">
                <img src="/assets/images/icon/ComplementoPago.png" width="30px" alt="">
                <?php if ($complementosPendientes > 0) { ?>
                    <span class="badge badge-danger" style="position: absolute; top: -6px; right: -6px; font-size: 10px; padding: 2px 5px;"><?= $complementosPendientes; ?></span>
                <?php } ?>
            </a>
        </li>
    <?php } ?>
</ul>

<div class="tab-content" id="pills-tabContent">
    <!-- Tab 1 -->
    <div class="tab-pane fade show active" id="factura" role="tabpanel" aria-labelledby="factura-tab">
        <h4 class="m-t-20 m-b-20"><b>Datos de Factura</b></h4>
        <div class="row show-grid">
            <div class="col-xs-12 col-md-8">
                <span class="text-muted"><i class="fas fa-user text-primary mr-1"></i>Proveedor</span>
                <h6><?= $data['dataCompra']['data']['idProveedor']; ?> - <?= $data['dataCompra']['data']['razonSocialEm']; ?></h6>
                <span class="text-muted"><i class="fas fa-fingerprint text-primary mr-1"></i>UUID</span>
                <h6><?= $data['dataCompra']['data']['FacUUID']; ?></h6>

                <p class="m-t-15">
                    <b><i class="far fa-calendar-alt text-success mr-1"></i>Fecha Vence :</b> <?= $data['dataCompra']['data']['FechaVence']; ?><br>
                    <b><i class="fas fa-calendar-check text-info mr-1"></i>Fecha de Pago :</b> <?= $data['dataCompra']['data']['FechaProbablePago']; ?><br>
                    <b><i class="fas fa-inbox text-secondary mr-1"></i>Fecha Recepción :</b> <?= $data['dataCompra']['data']['fechaReg']; ?><br>
                    <b><i class="far fa-calendar text-warning mr-1"></i>Fecha Factura :</b> <?= $data['dataCompra']['data']['fechaFac']; ?><br>
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
                    <button data-fancybox type="button" data-type="pdf" data-preloader="true" data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlFacPDF; ?>/#toolbar=1" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                    <button data-fancybox="xml" type="button" data-xml-url="<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlFacXML; ?>" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-6 col-md-4">
                <span class="text-muted"><i class="fas fa-hashtag text-info mr-1"></i>Serie y Folio</span>
                <h6><?= $data['dataCompra']['data']['FacSerie'] . $data['dataCompra']['data']['FacFolio']; ?></h6>
                <span class="text-muted p-t-30 db"><i class="fas fa-file-alt text-secondary mr-1"></i>Orden de Compra</span>
                <h6><?= $data['dataCompra']['data']['ordenCompra']; ?></h6>
            </div>
            <div class="col-xs-6 col-md-4">
                <span class="text-muted"><i class="fas fa-money-check-alt text-success mr-1"></i>Forma y Metodo de Pago</span>
                <h6><?= $data['dataCompra']['data']['FacFormaPago']; ?> - <?= $data['dataCompra']['data']['FacMetodoPago']; ?></h6>
                <span class="text-muted"><i class="fas fa-tags text-warning mr-1"></i>Uso de CFDI</span>
                <h6><?= $data['dataCompra']['data']['FacUsoCfdi'] . ' - ' . $data['dataCompra']['data']['nameUsoCfdi']; ?></h6>
            </div>
            <div class="col-xs-6 col-md-4">
                <span class="text-muted"><i class="fas fa-file-invoice-dollar text-primary mr-1"></i>Subtotal</span>
                <h6><?= (empty($data['dataCompra']['data']['FacSubtotal'])) ? 0 : '$ ' . number_format(abs($data['dataCompra']['data']['FacSubtotal']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['FacTipoMoneda']; ?></h6>
                <span class="text-muted"><i class="fas fa-percentage text-info mr-1"></i>Impuestos</span>
                <h6>$ <?= number_format($totalImpuestos, 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
                <span class="text-muted"><i class="fas fa-dollar-sign text-warning mr-1"></i>Total</span>
                <h6><?= (empty($data['dataCompra']['data']['FacMonto'])) ? 0 : '$ ' . number_format(abs($data['dataCompra']['data']['FacMonto']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['FacTipoMoneda']; ?></h6>
            </div>
        </div>
        <div class="row show-grid"></div>

        <span class="text-muted p-t-30 db"><i class="fas fa-box text-secondary mr-1"></i>No. de Recepción</span>
        <h6><?= $data['dataCompra']['data']['noRecepcion']; ?></h6>
    </div>
    <!-- End Tab 1 -->

    <?php if ($contNotaCredito > 0) { ?>
        <div class="tab-pane fade" id="notaCredito" role="tabpanel" aria-labelledby="nota-credito-tab">
            <h4 class="m-t-20 m-b-20"><b>Notas de Crédito</b></h4>

            <?php
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
                        $estatusBadge = '';
                        $estatusClass = '';
                        $borderClass = '';
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
                                            <h6 class="mb-0" style="word-break: break-all;"><?= htmlspecialchars($uuid); ?></h6>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-hashtag text-info" style="font-size: 10px;"></i> Serie/Folio:
                                            </small>
                                            <h6 class="mb-0"><?= htmlspecialchars($serie . $folio); ?></h6>
                                        </div>
                                    </div>
                                    <div class="row mt-2" style="border-top: 1px solid #e0e0e0; padding-top: 8px;">
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">
                                                <i class="far fa-calendar-alt text-success" style="font-size: 10px;"></i> Fecha:
                                            </small>
                                            <h6 class="mb-0"><?= $fechaReg; ?></h6>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-dollar-sign text-warning" style="font-size: 10px;"></i> Total:
                                            </small>
                                            <h6 class="mb-0">$ <?= $total; ?> <?= $moneda; ?></h6>
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
                                            data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDF; ?>/#toolbar=1"
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
            <?php } ?>
        </div>
    <?php
    }
    if ($mostrarComplementoPago > 0) { ?>
        <div class="tab-pane fade p-15" id="complemento-pago" role="tabpanel" aria-labelledby="complemento-pago-tab">
            <h4 class="m-t-20 m-b-20"><b>Complemento de Pago</b></h4>

            <?php
            if ($contComplementosPago === 0) {
                echo '
                        <div class="alert alert-warning">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                            <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Complemento de Pago Pendiente...</h3>
                            Favor de cargar el Complemento de Pago para evitar retrasos.
                        </div> ';
            } else { ?>
                <div class="comment-widgets scrollable" style="max-height: 600px; overflow-y: auto;">
                    <?php
                    $contadorCP = 1;
                    foreach ($complementosPago as $complemento) {
                        $estatusBadge = '';
                        $estatusClass = '';
                        $borderClass = '';
                        switch ($complemento['estatus'] ?? 1) {
                            case 0:
                                $estatusBadge = 'Cancelado';
                                $estatusClass = 'label-danger';
                                break;
                            case 1:
                                $estatusBadge = 'Pendiente';
                                $estatusClass = 'label-info';
                                break;
                            case 2:
                                $estatusBadge = 'Aceptado';
                                $estatusClass = 'label-success';
                                $borderClass = 'border-left border-success';
                                break;
                            case 3:
                                $estatusBadge = 'Rechazado';
                                $estatusClass = 'label-danger';
                                $borderClass = 'border-left border-danger';
                                break;
                            default:
                                $estatusBadge = 'Pendiente';
                                $estatusClass = 'label-info';
                        }

                        $urlPDFComp = base64_encode($complemento['urlPDF'] ?? '');
                        $urlXMLComp = base64_encode($complemento['urlXML'] ?? '');
                        $uuidComp = $complemento['uuid'] ?? 'N/A';
                        $serieComp = $complemento['serie'] ?? '';
                        $folioComp = $complemento['folio'] ?? '';
                        $fechaRegComp = isset($complemento['fechaReg']) ? date('d/m/Y', strtotime($complemento['fechaReg'])) : 'N/A';
                        $fechaComp = isset($complemento['fecha']) ? date('d/m/Y', strtotime($complemento['fecha'])) : 'N/A';
                        $totalComp = isset($complemento['montoTotalPagos']) ? number_format(abs($complemento['montoTotalPagos']), 2, '.', ',') : '0.00';
                        $monedaComp = $complemento['moneda'] ?? 'MXN';
                    ?>
                        <div class="d-flex flex-row comment-row <?= $contadorCP === 1 ? 'm-t-0' : ''; ?>">
                            <div class="comment-text active w-100 <?= $borderClass; ?>">
                                <div class="d-flex align-items-center p-b-15">
                                    <div>
                                        <h4 class="font-medium mb-0">
                                            <i class="fas fa-hand-holding-usd text-success mr-2"></i>Complemento #<?= $contadorCP; ?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="m-b-15">
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-fingerprint text-primary" style="font-size: 10px;"></i> UUID:
                                            </small>
                                            <h6 class="mb-0" style="word-break: break-all;"><?= htmlspecialchars($uuidComp); ?></h6>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-hashtag text-info" style="font-size: 10px;"></i> Serie/Folio:
                                            </small>
                                            <h6 class="mb-0"><?= htmlspecialchars($serieComp . $folioComp); ?></h6>
                                        </div>
                                    </div>
                                    <div class="row mt-2" style="border-top: 1px solid #e0e0e0; padding-top: 8px;">
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">
                                                <i class="far fa-calendar-alt text-success" style="font-size: 10px;"></i> Fecha CFDI:
                                            </small>
                                            <h6 class="mb-0"><?= $fechaComp; ?></h6>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-dollar-sign text-warning" style="font-size: 10px;"></i> Total Pagos:
                                            </small>
                                            <h6 class="mb-0">$ <?= $totalComp; ?> <?= $monedaComp; ?></h6>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($complemento['detalles'])) { ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Parcialidad</th>
                                                    <th>Fecha Pago</th>
                                                    <th>Forma</th>
                                                    <th>Importe</th>
                                                    <th>Saldo Insoluto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($complemento['detalles'] as $det) {
                                                    $fechaPago = isset($det['fechaPago']) ? date('d/m/Y', strtotime($det['fechaPago'])) : 'N/A';
                                                    $importePagado = isset($det['importePagado']) ? number_format(abs($det['importePagado']), 2, '.', ',') : '0.00';
                                                    $saldoInsoluto = isset($det['saldoInsoluto']) ? number_format(abs($det['saldoInsoluto']), 2, '.', ',') : '0.00';
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($det['noParcialidad'] ?? ''); ?></td>
                                                        <td><?= $fechaPago; ?></td>
                                                        <td><?= htmlspecialchars($det['formaPago'] ?? ''); ?></td>
                                                        <td>$ <?= $importePagado; ?></td>
                                                        <td>$ <?= $saldoInsoluto; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                <div class="comment-footer">
                                    <span class="text-muted float-right"><?= $fechaRegComp; ?></span>
                                    <span class="label label-rounded <?= $estatusClass; ?>"><?= $estatusBadge; ?></span>
                                    <span class="action-icons active">
                                        <a href="javascript:void(0)" data-fancybox data-type="pdf" data-preloader="true" data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDFComp; ?>/#toolbar=0" class="text-danger">
                                            <i class="far fa-file-pdf"></i> Ver PDF
                                        </a>
                                        <a href="javascript:void(0)" data-fancybox="xml" data-xml-url="<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlXMLComp; ?>" class="text-info">
                                            <i class="far fa-file-code"></i> Ver XML
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php
                        $contadorCP++;
                    }
                    ?>
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
</script>