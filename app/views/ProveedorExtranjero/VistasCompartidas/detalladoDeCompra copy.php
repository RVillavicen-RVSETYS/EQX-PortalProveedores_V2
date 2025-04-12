<?php
$debug = 0;

if ($debug == 0) {
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

            //Preparando variables para mostrar la información de la compra
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
            $requiereComplementoPago = 0;
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
                    <div class="row show-grid">
                        <div class="col-xs-12 col-md-8">
                            <span class="text-muted">Proveedor </span>
                            <h6><?= $data['dataCompra']['data']['idProveedor']; ?> - <?= $data['dataCompra']['data']['razonSocialEm']; ?></h6>
                            <span class="text-muted">UUID </span>
                            <h6><?= $data['dataCompra']['data']['uuid']; ?></h6>

                            <p class="m-t-15">
                                <b>Fecha Factura :</b> <?= $data['dataCompra']['data']['fechaFac']; ?><br>
                                <b>Fecha Recepción :</b> <?= $data['dataCompra']['data']['fechaReg']; ?>
                            </p>
                        </div>
                        <div class="col-xs-6 col-md-4">
                            <div class="text-right">
                                <br>
                                <span class="label label-success label-rounded" style="font-size: 1.5em;">PAGADO</span>
                                <br><br>
                                <button type="button" onclick="verFacturaPDF('<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDF; ?>')" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i> Ver PDF</button>
                                <button type="button" onclick="verFacturaXML('<?= '/ProveedorNacional/Inicio/verDocumento/XML/' . $urlXML; ?>')" class="btn btn-outline-info"><i class="far fa-file-code"></i> Ver XML</button>
                            </div>
                        </div>
                    </div>
                    <div class="row show-grid">
                        <div class="col-xs-6 col-md-4">
                            <span class="text-muted">Serie y Folio </span>
                            <h6><?= $data['dataCompra']['data']['serie'] . $data['dataCompra']['data']['folio']; ?></h6>
                            <span class="text-muted p-t-30 db">Orden de Compra</span>
                            <h6><?= $data['dataCompra']['data']['ordenCompra']; ?></h6>
                        </div>
                        <div class="col-xs-6 col-md-4">
                            <span class="text-muted">Forma y Metodo de Pago </span>
                            <h6><?= $data['dataCompra']['data']['idCatFormaPago']; ?> - <?= $data['dataCompra']['data']['idCatMetodoPago']; ?></h6>
                            <span class="text-muted">Uso de CFDI </span>
                            <h6><?= $data['dataCompra']['data']['usoCfdi']; ?></h6>
                        </div>
                        <div class="col-xs-6 col-md-4">
                            <span class="text-muted">Subtotal </span>
                            <h6><?= (empty($data['dataCompra']['data']['subTotal'])) ? 0 : number_format(abs($data['dataCompra']['data']['subTotal']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
                            <span class="text-muted">Total </span>
                            <h6><?= (empty($data['dataCompra']['data']['monto'])) ? 0 : number_format(abs($data['dataCompra']['data']['monto']), 2, '.', ','); ?> <?= $data['dataCompra']['data']['idCatTipoMoneda']; ?></h6>
                        </div>
                    </div>
                    <div class="row show-grid"></div>

                    <span class="text-muted p-t-30 db">No. de Recepción</span>
                    <h6><?= $data['dataCompra']['data']['noRecepcion']; ?></h6>
                    
                    <div>
                        <a
                            data-fancybox="gallery"
                            data-src="https://lipsum.app/id/2/1024x768"
                            data-caption="Optional caption,&lt;br /&gt;that can contain &lt;em&gt;HTML&lt;/em&gt; code">
                            <img src="https://lipsum.app/id/2/200x150" />
                        </a>

                        <a data-fancybox="gallery" data-src="https://lipsum.app/id/3/1024x768">
                            <img src="https://lipsum.app/id/3/200x150" />
                        </a>

                        <a data-fancybox="gallery" data-src="https://lipsum.app/id/4/1024x768">
                            <img src="https://lipsum.app/id/4/200x150" />
                        </a>
                    </div>
                </div>
                <!-- End Tab 1 -->
                
                <?php if ($contNotaCredito > 0) { ?>
                <div class="tab-pane fade" id="notaCredito" role="tabpanel" aria-labelledby="nota-credito-tab">
                    <ul class="mailbox list-style-none m-t-20">
                        <li>
                            <div class="message-center chat-scroll">
                                <a href="javascript:void(0)" class="message-item" id='chat_user_1' data-user-id='1'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/1.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status online pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Pavan kumar</h5>
                                        <span class="mail-desc">Just see the my admin!</span>
                                        <span class="time">9:30 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_2' data-user-id='2'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/2.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status busy pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Sonu Nigam</h5>
                                        <span class="mail-desc">I've sung a song! See you at</span>
                                        <span class="time">9:10 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_3' data-user-id='3'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/3.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status away pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Arijit Sinh</h5>
                                        <span class="mail-desc">I am a singer!</span>
                                        <span class="time">9:08 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_4' data-user-id='4'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/4.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status offline pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Nirav Joshi</h5>
                                        <span class="mail-desc">Just see the my admin!</span>
                                        <span class="time">9:02 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_5' data-user-id='5'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/5.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status offline pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Sunil Joshi</h5>
                                        <span class="mail-desc">Just see the my admin!</span>
                                        <span class="time">9:02 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_6' data-user-id='6'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/6.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status offline pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Akshay Kumar</h5>
                                        <span class="mail-desc">Just see the my admin!</span>
                                        <span class="time">9:02 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_7' data-user-id='7'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/7.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status offline pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Pavan kumar</h5>
                                        <span class="mail-desc">Just see the my admin!</span>
                                        <span class="time">9:02 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                                <!-- Message -->
                                <a href="javascript:void(0)" class="message-item" id='chat_user_8' data-user-id='8'>
                                    <span class="user-img">
                                        <img src="../../assets/images/users/8.jpg" alt="user" class="rounded-circle">
                                        <span class="profile-status offline pull-right"></span>
                                    </span>
                                    <div class="mail-contnet">
                                        <h5 class="message-title">Varun Dhavan</h5>
                                        <span class="mail-desc">Just see the my admin!</span>
                                        <span class="time">9:02 AM</span>
                                    </div>
                                </a>
                                <!-- Message -->
                            </div>
                        </li>
                    </ul>
                </div>
                <?php 
                } 
                if ($requiereComplementoPago > 0) { ?>
                <div class="tab-pane fade p-15" id="complemento-pago" role="tabpanel" aria-labelledby="complemento-pago-tab">
                    <h6 class="m-t-20 m-b-20">Activity Timeline</h6>
                    <div class="steamline">
                        <div class="sl-item">
                            <div class="sl-left bg-success">
                                <i class="ti-user"></i>
                            </div>
                            <div class="sl-right">
                                <div class="font-medium">Meeting today
                                    <span class="sl-date"> 5pm</span>
                                </div>
                                <div class="desc">you can write anything </div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left bg-info">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="sl-right">
                                <div class="font-medium">Send documents to Clark</div>
                                <div class="desc">Lorem Ipsum is simply </div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left">
                                <img class="rounded-circle" alt="user" src="../../assets/images/users/2.jpg">
                            </div>
                            <div class="sl-right">
                                <div class="font-medium">Go to the Doctor
                                    <span class="sl-date">5 minutes ago</span>
                                </div>
                                <div class="desc">Contrary to popular belief</div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left">
                                <img class="rounded-circle" alt="user" src="../../assets/images/users/1.jpg">
                            </div>
                            <div class="sl-right">
                                <div>
                                    <a href="javascript:void(0)">Stephen</a>
                                    <span class="sl-date">5 minutes ago</span>
                                </div>
                                <div class="desc">Approve meeting with tiger</div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left bg-primary">
                                <i class="ti-user"></i>
                            </div>
                            <div class="sl-right">
                                <div class="font-medium">Meeting today
                                    <span class="sl-date"> 5pm</span>
                                </div>
                                <div class="desc">you can write anything </div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left bg-info">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="sl-right">
                                <div class="font-medium">Send documents to Clark</div>
                                <div class="desc">Lorem Ipsum is simply </div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left">
                                <img class="rounded-circle" alt="user" src="../../assets/images/users/4.jpg">
                            </div>
                            <div class="sl-right">
                                <div class="font-medium">Go to the Doctor
                                    <span class="sl-date">5 minutes ago</span>
                                </div>
                                <div class="desc">Contrary to popular belief</div>
                            </div>
                        </div>
                        <div class="sl-item">
                            <div class="sl-left">
                                <img class="rounded-circle" alt="user" src="/assets/images/users/6.jpg">
                            </div>
                            <div class="sl-right">
                                <div>
                                    <a href="javascript:void(0)">Stephen</a>
                                    <span class="sl-date">5 minutes ago</span>
                                </div>
                                <div class="desc">Approve meeting with tiger</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>

<button
    class="btn btn-success btn-lg mrb50"
    data-iframe="true"
    id="open-pdf"
    data-src="/disel.pdf">
    Open PDF file
</button>

<button id="showXmlButton" data-iframe="true" class="pdf-button" data-src="<?= '/ProveedorNacional/Inicio/verDocumento/PDF/' . $urlPDF; ?>">Mostrar XML</button>


    <!-- Contenedor para LightGallery -->
    <div id="lightgallery-container" class="" data-iframe="true" ></div>
                
<div id="verCFDI" style="padding: 15px; min-height: 200px;">
</div>

<script src="/dist/js/custom.js"></script>
<script>
    $(document).ready(function() {
        // Seleccionar todos los botones con la clase 'pdf-button'
        $('.pdf-button').on('click', function() {
            var pdfUrl = $(this).data('src');
            $('#open-pdf').attr('data-src', pdfUrl);
                        
            $('#open-pdf').trigger('click');

        });
    });

    lightGallery(document.getElementById('open-pdf'), {
                selector: 'this',
            });

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