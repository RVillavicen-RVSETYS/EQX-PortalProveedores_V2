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
                <button type="button" onclick="verFacturaPDF('<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDF; ?>')" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                <button type="button" onclick="verFacturaXML('<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlXML; ?>')" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
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

</div>

<div id="verCFDI" style="padding: 15px; min-height: 200px;">
</div>

</div>
</div>
</div>
<script src="/dist/js/custom.js"></script>
<script>
    // Botón para cargar el PDF
    function verFacturaPDF(pdfUrl) {
        verCFDI = document.getElementById("verCFDI");
        verCFDI.innerHTML = '<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>';
        verCFDI.innerHTML = '<iframe src="' + pdfUrl + '#toolbar=0" width="100%" height="500px" style="border:none;"></iframe>';
    }

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