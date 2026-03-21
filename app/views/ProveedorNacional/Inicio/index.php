<?php
$debug = 0;

$requestUri = $_SERVER['REQUEST_URI'];
$cleanUri = parse_url($requestUri, PHP_URL_PATH);
$piezasURL = explode('/', trim($cleanUri, '/'));
$paginaLink = $piezasURL[1];
$urlIdioma = '../app/Views/Layout/Idiomas/' . $piezasURL[0] . '/' . $_SESSION['EQXidioma'] . '.php';
require_once($urlIdioma);
$menuModel = new Idiomas($piezasURL[1]);
include '../app/Views/Layout/funciones.php';

$funcionMenu = generarMenu($menuData['data'], $paginaLink);
$datosPagina = $funcionMenu['datosPagina'];
$rutaMenu = $funcionMenu['rutaMenu'];

if ($debug == 1) {
    echo '<br>Ruta de Idioma: ' . $urlIdioma;
    echo '<br>Ruta Actual: ' . __DIR__;
    echo '<br><br>Contenido de menuData:';
    var_dump($menuData);
    echo '<br><br>Contenido de areaData:';
    var_dump($areaData);
    echo '<br><br>Contenido de areaLink:';
    var_dump($areaLink);
    echo '<br><br>Contenido de bloqueoCargaFactura:';
    var_dump($bloqueoCargaFactura);
    echo '<br><br>Contenido de notificaciones:';
    var_dump($notificaciones);
    echo '<br><br>Contenido de _SESSION:';
    var_dump($_SESSION);
    echo '<br><br>Request-URI: ' . $_SERVER['REQUEST_URI'] . '<br>Contenido de piezasURL:';
    var_dump($piezasURL);
    echo '<br><br>Ruta del MenuActual: ' . $rutaMenu . '<br><br>Contenido de datosPagina:';
    var_dump($datosPagina);
    echo '<br><br>Valores pasados del Controller Data:';
    var_dump($data['datosProveedor']);
}
$noti = '';
if ($notificaciones['success'] && !empty($notificaciones['data'])) {
    foreach ($notificaciones['data'] as $item) {
        $tipoMensaje = $item['tipoMensaje'];
        $titulo = $item['titulo'];
        $mensaje = $item['mensaje'];

        // Llama a la función con los datos actuales
        $noti .= generaNotificacionStatica($tipoMensaje, $titulo, $mensaje);
    }
}

?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>
    <?php include '../app/Views/Layout/header.php'; ?>
    <!-- Custom CSS -->

    <!-- Vendor -->
    <link href="/assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/fancybox/fancybox.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/carousel/carousel.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/carousel/carousel.thumbs.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.toolbar.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.pins.css" rel="stylesheet">

    <style>
        .custom-file-input~.custom-file-label::after {
            content: "Buscar";
            /* Cambia el texto "Browse" por "Buscar" */
        }
    </style>


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

<![endif]-->
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <?php include '../app/Views/Layout/menu.php'; ?>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->


        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">

            <!-- ============================================================== -->
            <!-- Inicia Contenido fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- Inicia Grupo de Tarjetas -->
                <div class="row">
                    <div class="col-lg-9 col-md-9">
                        <div class="card">
                            <h3 class="card-title m-t-10 m-l-15"><?= $data['datosProveedor']['RazonSocial']; ?></h3>
                            <p class="card-text m-l-15"><b><?= $data['datosProveedor']['RFC']; ?></b>.<br>
                                <?= $data['datosProveedor']['Correo']; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <i class="fas fa-file-alt font-20 text-danger"></i>
                                        <p class="font-16 m-b-5"><?= $menuModel->txt('Complementos_Pendientes'); ?></p>
                                    </div>
                                    <div class="col-5">
                                        <h1 class="font-light text-right mb-0" id="complementosPendientes">
                                            <div class="loader">
                                                <span class="bar"></span>
                                                <span class="bar"></span>
                                                <span class="bar"></span>
                                            </div>
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Termina Grupo de Tarjetas -->
                <!-- ============================================================== -->

                <!-- Inicia Espacio para Notificaciones a Proveedores -->
                <div>
                    <?= $noti; ?>
                </div>
                <!-- Termina Espacio para Notificaciones a Proveedores -->
                <!-- ============================================================== -->

                <!-- Inicia Espacio para Tabla y Recibe Facturas -->
                <div class="row">
                    <!--Tarjeta Listado de Ultimas Facturas-->
                    <div class="col-md-8 col-lg-8">
                        <div class="card border">
                            <div class="card-header bg-pyme-primary">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="m-b-0 text-white"><?= $menuModel->txt('Ultimas_Facturas'); ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="cajaResultados" class="jsgrid" style="position: relative; height: auto; width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Tarjeta Carga de Facturas-->
                    <div class="col-md-4 col-lg-4">
                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white">Carga Complementos de Pago</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form" id="Form_CargaComplementoPago" method="post" enctype="multipart/form-data" action="Inicio/registraNuevoComplementoPago">
                                                    <div id="mensajeComplementoPago" class="form-group" style="display: none;">
                                                        <div class="alert alert-danger" role="alert" id="textoMensajeComplementoPago"></div>
                                                    </div>

                                                    <div id="contenedorInputsComplementoPago" style="display: none;">
                                                        <div class="form-group">
                                                            <label for="complementoPagoPDF">Complemento de Pago</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="complementoPagoPDF" name="complementoPagoPDF" accept=".pdf" required>
                                                                    <label class="custom-file-label" for="complementoPagoPDF"> Elegir PDF de Complemento de Pago..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-code text-info"></i> &nbsp;XML</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="complementoPagoXML" name="complementoPagoXML" accept=".xml" required>
                                                                    <label class="custom-file-label" for="complementoPagoXML">Elegir XML de Complemento de Pago..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>
                                                        <div id="desbloquear-btnCP">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetFormulario('Form_CargaComplementoPago')"><i class="far fa-trash-alt text-white"></i> <?= $menuModel->txt('Limpiar'); ?></button>
                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Complemento de Pago</button>
                                                        </div>
                                                        <div id="bloquear-btnCP" style="display: none;">
                                                            <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white">Carga Notas De Crédito</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form" id="Form_CargaNotaCredito" method="post" enctype="multipart/form-data" action="Inicio/registraNuevaNotaCredito">
                                                    <div class="form-group row">
                                                        <label for="ordenCompraNC" class="col-3 col-form-label">Orden Compra</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="ordenCompraNC" name="ordenCompraNC" maxlength="14" onchange="validaOrdCompraNC(this.value);" required>
                                                            <div class="invalid-feedback" id="invalid_ordenCompraNC">
                                                                <img src="../assets/images/barLoadign.gif" alt="" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row" id="campoFacturaIngresada" style="display: none;">
                                                        <label for="idCompraNC" class="col-3 col-form-label">Factura Ingresada</label>
                                                        <div class="col-9">
                                                            <select class="form-control custom-select" id="idCompraNC" name="idCompraNC" style="width: 100%; height:36px;" required>
                                                                <option value="">Seleccione una factura...</option>
                                                            </select>
                                                            <div class="invalid-feedback" id="invalid_idCompraNC"></div>
                                                        </div>
                                                    </div>

                                                    <div id="contentNotaCreditoNC"></div>
                                                    <div class="justify-content-end d-none" id="btnNotaCreditoNC">
                                                        <button type="button" class="btn btn-success mt-2" onclick="cargarFormNotaCredito(lastNotasCredito, 'NC')"><i class="fas fa-plus"></i> Nota de Crédito</button>
                                                    </div>

                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>

                                                        <div id="desbloquear-btnNC">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetFormulario('Form_CargaNotaCredito')"><i class="far fa-trash-alt text-white"></i> <?= $menuModel->txt('Limpiar'); ?></button>
                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Nota de Credito</button>
                                                        </div>
                                                        <div id="bloquear-btnNC" style="display: none;">
                                                            <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white"><?= $menuModel->txt('Carga_Factura'); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <?php
                                                if ($bloqueoCargaFactura['success']) {
                                                    echo '<h4>' . $bloqueoCargaFactura['data']['mensajeCierre'] . '</h4><hr>';
                                                } else {
                                                ?>
                                                    <form class="form" id="Form_CargaFactura" method="post" enctype="multipart/form-data" action="Inicio/registraNuevaFactura">
                                                        <div class="form-group row">
                                                            <span class="col-3 col-form-label"><b><?= $menuModel->txt('No_Proveedor'); ?></b></span>
                                                            <span class="col-3 col-form-label text-success"><b><?= $_SESSION['EQXnoProveedor']; ?></b></span>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="ordenCompra" class="col-3 col-form-label"><?= $menuModel->txt('OC'); ?></label>
                                                            <div class="col-9">
                                                                <input class="form-control" type="search" value="" id="ordenCompra" name="ordenCompra" maxlength="14" onchange="validaOrdCompra(this.value);" required>
                                                                <div class="invalid-feedback" id="invalid_ordenCompra">
                                                                    <img src="../assets/images/barLoadign.gif" alt="" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="listaHES" class="col-12 col-form-label"><?= $menuModel->txt('HES'); ?></label>
                                                            <div class="col-12">
                                                                <textarea class="form-control" id="listaHES" name="listaHES" rows="3" onblur="validaHojaEntrada(this.value);" required></textarea>
                                                                <div class="invalid-feedback" id="invalid_listaHES"></div>
                                                            </div>
                                                        </div>



                                                        <div class="form-group">
                                                            <label for="facturaPDF"><?= $menuModel->txt('Facturas'); ?></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="facturaPDF" name="facturaPDF" accept=".pdf" required>
                                                                    <label class="custom-file-label" for="facturaPDF"> Elegir PDF de Factura..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-code text-info"></i> &nbsp;XML</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="facturaXML" name="facturaXML" accept=".xml" required>
                                                                    <label class="custom-file-label" for="facturaXML">Elegir XML de Factura..</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div id="contentNotaCredito">

                                                        </div>
                                                        <div class="justify-content-end d-none" id="btnNotaCredito">
                                                            <button type="button" class="btn btn-success mt-2" onclick="cargarFormNotaCredito(lastNotasCredito)"><i class="fas fa-plus"></i> Nota de Crédito</button>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6 text-right"></div>

                                                            <div id="desbloquear-btn1">
                                                                <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> <?= $menuModel->txt('Limpiar'); ?></button>
                                                                <button type="submit" class="btn btn-success waves-effect waves-light"><?= $menuModel->txt('Carga_Factura'); ?></button>
                                                            </div>
                                                            <div id="bloquear-btn1" style="display: none;">
                                                                <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white"><?= $menuModel->txt('Carga_Factura_por_anticipo'); ?></h4>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <?php
                                                if ($bloqueoCargaFactura['success']) {
                                                    echo '<h4>' . $bloqueoCargaFactura['data']['mensajeCierre'] . '</h4><hr>';
                                                } else {
                                                ?>
                                                    <form class="form" id="Form_CargaFacturaAnticipo" method="post" enctype="multipart/form-data">
                                                        <div class="form-group row">
                                                            <label for="codAnticipo" class="col-3 col-form-label"><?= $menuModel->txt('CodigoAnticipo'); ?></label>
                                                            <div class="col-9">
                                                                <input class="form-control" type="search" value="" id="codAnticipo" name="codAnticipo" maxlength="14" onchange="validaAnticipo(this.value);" required>
                                                                <div class="invalid-feedback" id="invalid_codAnticipo"></div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="anticipoPDF"><?= $menuModel->txt('Facturas'); ?></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="anticipoPDF" name="anticipoPDF" accept=".pdf" required>
                                                                    <label class="custom-file-label" for="anticipoPDF"> Elegir PDF de Factura..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-code text-info"></i> &nbsp;XML</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="anticipoXML" name="anticipoXML" accept=".xml" required>
                                                                    <label class="custom-file-label" for="anticipoXML">Elegir XML de Factura..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6 text-right"></div>

                                                            <div id="desbloquear-btn2">
                                                                <button type="reset" class="btn btn-danger waves-effect" onclick="resetFormulario('Form_CargaFacturaAnticipo')"><i class="far fa-trash-alt text-white"></i> <?= $menuModel->txt('Limpiar'); ?></button>

                                                                <button type="submit" class="btn btn-success waves-effect waves-light"><?= $menuModel->txt('Carga_Factura'); ?></button>
                                                            </div>
                                                            <div id="bloquear-btn2" style="display: none;">
                                                                <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Termina Espacio para Tabla y Recibe Facturas -->
            <!-- ============================================================== -->

        </div>
        <!-- ============================================================== -->
        <!-- Termina Contenido fluid  -->
        <!-- ============================================================== -->

        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <?php include '../app/Views/Layout/footer.php'; ?>
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->

    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- customizer Panel -->
    <!-- ============================================================== -->
    <aside class="customizer">

        <div class="customizer-body" id="customizer_body">

        </div>
    </aside>
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="/assets/libs/fancybox/dist/fancybox/fancybox.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/carousel/carousel.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/carousel/carousel.autoplay.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/carousel/carousel.thumbs.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/panzoom/panzoom.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/panzoom/panzoom.toolbar.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/panzoom/panzoom.pins.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/fancybox/l10n/es.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/panzoom/l10n/es.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/carousel/l10n/es.umd.js"></script>

    <!-- Bootstrap tether Core JavaScript -->
    <script src="/assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Menu -->
    <script type="text/javascript" src="/assets/menu/webslidemenu/webslidemenu.js"></script>
    <!-- apps -->
    <script src="/dist/js/app.min.js"></script>
    <script src="/dist/js/app.init.horizontalEquinox.js"></script>
    <script src="/dist/js/app-style-switcher.horizontal.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="/assets/extra-libs/sparkline/sparkline.js"></script>
    <!--Wave Effects -->
    <script src="/dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="/dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="/dist/js/custom.js"></script>
    <script src="/assets/libs/toastr/build/toastr.min.js"></script>
    <script src="/assets/libs/moment/moment.js"></script>
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.js"></script>
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.all.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/jquery.dataTables.min-ESP.js"></script>
    <script src="/dist/js/pages/datatable/datatable-basic.init.js"></script>

    <script src="/assets/extra-libs/datatables.net/js/jszip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>

    <script src="/assets/extra-libs/datatables.net/js/dataTables.buttons.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.flash.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.html5.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.print.min.js"></script>
    <!--This page JavaScript -->
    <!--chartis chart-->
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/pdfmake.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>
    <script src="/assets/libs/sweetalert2/sweet-alert.init.js"></script>
    <script src="/dist/js/basicFuctions.js"></script>

    <script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="/dist/js/pages/forms/select2/select2.init.js"></script>
    <script>
        let lastNotasCredito = [];

        function validaOrdCompra(ordenCompra) {
            $("#ordenCompra").removeClass("is-invalid is-valid");
            $("#invalid_ordenCompra").html("");

            oc = validarEstructuraOC(ordenCompra);
            console.log(validarEstructuraOC(ordenCompra));
            console.log(oc.valor);

            $("#ordenCompra").val(oc.valor);
            if (oc.valido) {
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/verificaOrdenCompraFactura',
                    data: {
                        ordenCompra: oc.valor
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            validOC = true;
                            $("#ordenCompra").addClass("is-valid");
                            $('#contentNotaCredito').empty();
                            $("#btnNotaCredito").addClass("d-none");
                            contadorFormNotas = 0;
                            
                            // Verificar si hay HES pendientes
                            const cantHES = response.cantHES ?? 0;
                            const cantNC = response.cantNC ?? 0;
                            
                            if (cantHES > 0) {
                                // Hay HES pendientes, puede cargar factura
                                // Si hay NC pendientes, mostrar información (opcional)
                                if (cantNC > 0) {
                                    lastNotasCredito = response.NC || [];
                                    console.log('Información: Hay notas de crédito pendientes:', response.messageNC);
                                } else {
                                    lastNotasCredito = [];
                                }
                                
                            } else {
                                $("#invalid_ordenCompra").html(response.messageHES || 'No hay HES pendientes para esta Orden de Compra');
                            }
                        } else {
                            validOC = false;
                            $("#ordenCompra").addClass("is-invalid");
                            $("#invalid_ordenCompra").html(response.message || 'Error al validar la Orden de Compra');
                        }
                    },
                    error: function() {
                        notificaBad('Error al validar la Orden de Compra. Consulta a tu administrador');
                        validOC = false;
                    }
                });
            } else {
                $("#ordenCompra").addClass("is-invalid");
                $("#invalid_ordenCompra").html("Estructura: COM-XXX-######");
                validOC = false;
            }
        }

        function validaOrdCompraNC(ordenCompra) {
            $("#ordenCompraNC").removeClass("is-invalid is-valid");
            $("#invalid_ordenCompraNC").html("");

            const oc = validarEstructuraOC(ordenCompra);
            $("#ordenCompraNC").val(oc.valor);

            if (!oc.valido) {
                $("#ordenCompraNC").addClass("is-invalid");
                $("#invalid_ordenCompraNC").html("Estructura: COM-XXX-######");
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'Inicio/verificaOrdenCompraNotaCredito',
                data: { ordenCompra: oc.valor },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $("#ordenCompraNC").addClass("is-valid");
                        lastNotasCredito = response.NC || [];
                        obtenerFacturasPorOC(oc.valor);
                    } else {
                        $("#ordenCompraNC").addClass("is-invalid");
                        $("#invalid_ordenCompraNC").html(response.message || 'Error al validar la Orden de Compra para Nota de Crédito');
                        $('#campoFacturaIngresada').hide();
                        $('#idCompraNC').empty().append('<option value="">Seleccione una factura...</option>');
                        $('#contentNotaCreditoNC').empty();
                        $('#btnNotaCreditoNC').addClass('d-none');
                    }
                },
                error: function() {
                    $("#ordenCompraNC").addClass("is-invalid");
                    $("#invalid_ordenCompraNC").html('Error al validar la Orden de Compra para Nota de Crédito');
                }
            });
        }

        function obtenerFacturasPorOC(ordenCompra) {
            const $selectFactura = $('#idCompraNC');
            const $campoFactura = $('#campoFacturaIngresada');

            $selectFactura.empty().append('<option value="">Seleccione una factura...</option>');
            $campoFactura.hide();
            $('#contentNotaCreditoNC').empty();
            $('#btnNotaCreditoNC').addClass('d-none');

            $.ajax({
                type: 'POST',
                url: 'Inicio/obtenerFacturasPorOC',
                data: { ordenCompra: ordenCompra },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(function(factura) {
                            const text = `${factura.acuse} | ${factura.uuid} | ${factura.fechaReg}`;
                            $selectFactura.append(new Option(text, factura.idCompra));
                        });
                        $campoFactura.show();
                    } else {
                        $("#invalid_ordenCompraNC").html(response.message || 'No se encontraron facturas para esta OC');
                    }
                },
                error: function() {
                    $("#invalid_ordenCompraNC").html('Error al cargar facturas de la OC');
                }
            });
        }

        let contadorFormNotas = 0;

        function cargarFormNotaCredito(arrayNotasCredito, tipo = '') {
            contadorFormNotas++;
            const targetContent = (tipo === 'NC') ? '#contentNotaCreditoNC' : '#contentNotaCredito';
            const targetBtn = (tipo === 'NC') ? '#btnNotaCreditoNC' : '#btnNotaCredito';

            $.ajax({
                type: 'POST',
                url: 'Inicio/cargaFormNotaCredito',
                data: {
                    id: contadorFormNotas,
                    arrayNotasCredito: arrayNotasCredito
                },
                success: function(response) {
                    // Reemplaza los IDs/for con el número único
                    const formHtml = response.replace(/{{id}}/g, contadorFormNotas);
                    $(targetContent).append(formHtml);

                    // Re-inicializa select2 después de insertar el HTML
                    const $select = $(`#notaCredito_${contadorFormNotas}`);
                    arrayNotasCredito.forEach(nc => {
                        const esObligatoria = nc.Obligatoria == "1";
                        const optionText = esObligatoria ? `🔴 ${nc.Descripcion}` : nc.Descripcion;
                        const option = new Option(optionText, nc.IdNotaCredito, false, false);
                        $(option).attr('data-obligatoria', nc.Obligatoria);
                        $select.append(option);
                    });

                    $select.select2(); // Inicializa select2 en el select ya con opciones

                    $(targetBtn).removeClass('d-none');
                },
                error: function() {
                    $(targetContent).append('<div class="text-danger">Error cargando formulario.</div>');
                }
            });
        }

        const selectedNoteCreditIds = new Map();

        // Al seleccionar una opción
        $(document).on('select2:select', '.formulario-nota .select2', function(e) {
            const selectedId = e.params.data.id;
            const selectedText = e.params.data.text;

            selectedNoteCreditIds.set(selectedId, selectedText);

            $('.formulario-nota .select2').not(this).each(function() {
                $(this).find(`option[value="${selectedId}"]`).prop('disabled', true);
            });

            // Actualizar UI
            $('.formulario-nota .select2').select2();
        });

        // Al deseleccionar una opción
        $(document).on('select2:unselect', '.formulario-nota .select2', function(e) {
            const deselectedId = e.params.data.id;

            selectedNoteCreditIds.delete(deselectedId);

            $('.formulario-nota .select2').not(this).each(function() {
                $(this).find(`option[value="${deselectedId}"]`).prop('disabled', false);
            });

            // Actualizar UI
            $('.formulario-nota .select2').select2();
        });

        $(document).on('change', '.custom-file-input', function() {
            let fileName = $(this).val().split('\\').pop(); // obtiene el nombre del archivo
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        $(document).on('click', '.btn-eliminar-nota', function() {
            // Verifica cuántos bloques hay antes de eliminar
            const totalBloques = $('.formulario-nota').length;

            const $bloque = $(this).closest('.formulario-nota');
            const $selectInBlock = $bloque.find('.select2');

            if (totalBloques <= 1) {

                let contieneObligatorias = false;

                $selectInBlock.find('option').each(function() {
                    if ($(this).data('obligatoria') == "1") {
                        contieneObligatorias = true;
                        return false; // break
                    }
                });

                // Evitar borrar el último bloque
                if (contieneObligatorias == true) {
                    Swal.fire({
                        type: 'warning',
                        title: 'No puedes eliminar esta Nota de Crédito',
                        text: 'Contiene al menos una nota de crédito obligatoria.',
                        confirmButtonText: 'Entendido'
                    });
                    return; // No se elimina
                }

                if (contieneObligatorias == false) {

                    Swal.fire({
                        title: 'Seguro que quieres eliminar esta Nota de Crédito?',
                        text: "Esta acción no se puede deshacer.",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.value) {
                            if ($selectInBlock.length > 0) {
                                const currentSelections = $selectInBlock.val();

                                if (currentSelections && currentSelections.length > 0) {
                                    currentSelections.forEach(id => {
                                        selectedNoteCreditIds.delete(id);

                                        $('.formulario-nota .select2').not($selectInBlock).each(function() {
                                            $(this).find(`option[value="${id}"]`).prop('disabled', false);
                                        });
                                    });

                                    $('.formulario-nota .select2').select2();
                                }
                            }

                            $bloque.remove();

                            Swal.fire(
                                'Nota de Crédito Eliminada',
                                'La Nota de Crédito ha sido eliminada correctamente.',
                                'success'
                            )
                        }
                    })

                }

            } else {
                if ($selectInBlock.length > 0) {
                    const currentSelections = $selectInBlock.val();

                    if (currentSelections && currentSelections.length > 0) {
                        currentSelections.forEach(id => {
                            selectedNoteCreditIds.delete(id);

                            $('.formulario-nota .select2').not($selectInBlock).each(function() {
                                $(this).find(`option[value="${id}"]`).prop('disabled', false);
                            });
                        });

                        $('.formulario-nota .select2').select2();
                    }
                }
                $bloque.remove();
            }
        });


        $(document).ready(function() {

            let validOC = false;
            let validHES = false;
            let reqAnticipo = false;
            let validAnt = false;

            $("#Form_CargaFacturaAnticipo").on('submit', function(e) {
                e.preventDefault();
                notificaBad('La carga de factura por anticipo aún no está enlazada al servidor en esta versión. Usa «Carga factura» con orden de compra y HES, o contacta a administración.');
            });

            $("#Form_CargaFactura").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                bloquearBtn('btn1');
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/registraNuevaFactura',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        desbloquearBtn('btn1');
                        if (response.success) {
                            // Si todo salió bien, limpiar el formulario
                            resetFormulario("Form_CargaFactura");
                            // Limpiar también los campos dinámicos de Notas de Crédito
                            $('#contentNotaCredito').empty();
                            $('#btnNotaCredito').addClass("d-none");
                            // Limpiar select2 y otros campos
                            $('#ordenCompra').val(null).trigger('change');
                            $('#listaHES').val('');
                            // Resetear variables globales
                            contadorFormNotas = 0;
                            selectedNoteCreditIds.clear();
                            validOC = false;
                            validHES = false;
                            reqAnticipo = false;
                            lastNotasCredito = [];
                            notificaSucSweet("Excelente!!", response.message);
                            // Cargar tabla solo si todo fue exitoso
                            cargaTablaUltimasFacturas();
                        } else {
                            // Si hubo error, NO limpiar el formulario para que el usuario pueda corregir
                            notificaBadSweet("Lo sentimos!!", response.message); // Muestra el mensaje de error
                        }
                    },
                    error: function() {
                        desbloquearBtn('btn1');
                        // Si hay error en la petición, NO limpiar el formulario
                        notificaBad('Error al querer cargar factura. Consulta a tu administrador');
                    }
                });
            });

            $(document).on('change', '#idCompraNC', function() {
                const idCompra = $(this).val();
                if (!idCompra) {
                    $('#contentNotaCreditoNC').empty();
                    $('#btnNotaCreditoNC').addClass('d-none');
                    return;
                }
                $('#contentNotaCreditoNC').empty();
                cargarFormNotaCredito(lastNotasCredito, 'NC');
            });

            $("#Form_CargaNotaCredito").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                bloquearBtn('btnNC');
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/registraNuevaNotaCredito',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        desbloquearBtn('btnNC');
                        if (response.success) {
                            notificaSucSweet("Excelente!!", response.message);
                            resetFormulario("Form_CargaNotaCredito");
                            $('#campoFacturaIngresada').hide();
                            $('#idCompraNC').empty().append('<option value="">Seleccione una factura...</option>');
                            $('#contentNotaCreditoNC').empty();
                            $('#btnNotaCreditoNC').addClass('d-none');
                        } else {
                            notificaBadSweet("Lo sentimos!!", response.message);
                        }
                    },
                    error: function() {
                        notificaBad('Error al querer cargar Nota de Crédito. Consulta a tu administrador');
                        desbloquearBtn('btnNC');
                    }
                });
            });

            $("#Form_CargaComplementoPago").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                bloquearBtn('btnCP');
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/registraNuevoComplementoPago',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        desbloquearBtn('btnCP');
                        if (response.success) {
                            notificaSucSweet("Excelente!!", response.message);
                            resetFormulario("Form_CargaComplementoPago");
                            $('#contenedorInputsComplementoPago').hide();
                            $('#mensajeComplementoPago').hide();
                            $('#textoMensajeComplementoPago').html('');
                            $('#Form_CargaComplementoPago button[type="submit"]').prop('disabled', true);
                        } else {
                            notificaBadSweet("Lo sentimos!!", response.message);
                        }
                    },
                    error: function() {
                        notificaBad('Error al querer cargar complemento de pago. Consulta a tu administrador');
                        desbloquearBtn('btnCP');
                    }
                });
            });

            $(document).on('click', '#Form_CargaComplementoPago button[type="reset"]', function() {
                $('#contenedorInputsComplementoPago').hide();
                $('#mensajeComplementoPago').hide();
                $('#textoMensajeComplementoPago').html('');
                $('#Form_CargaComplementoPago button[type="submit"]').prop('disabled', true);
            });

            // Traere datos iniciales
            cargarDatosIniciales();
            cargaTablaUltimasFacturas();
            VerificaSiDebeComplementosPago();
        });

        function cargarDatosIniciales() {
            $.ajax({
                type: 'POST',
                url: 'Inicio/datosIniciales',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#complementosPendientes').html(response.cantComplementos);
                    } else {
                        $('#complementosPendientes').html('ND');
                        notificaBad(response.message);
                    }
                },
                error: function() {
                    console.log('Error al cargar los datos iniciales. Consulta a tu administrador');
                }
            });
        }

        function VerificaSiDebeComplementosPago() {
            const $mensajeDiv = $('#mensajeComplementoPago');
            const $textoMensaje = $('#textoMensajeComplementoPago');
            const $contenedorInputs = $('#contenedorInputsComplementoPago');
            const $btnSubmit = $('#Form_CargaComplementoPago button[type="submit"]');

            $mensajeDiv.hide();
            $contenedorInputs.hide();
            $btnSubmit.prop('disabled', true);
            $textoMensaje.html('');

            $.ajax({
                type: 'POST',
                url: 'Inicio/VerificaSiDebeComplementosPago',
                dataType: 'json',
                success: function(response) {
                    if (response.success === false) {
                        notificaBad(response.message || 'Error al verificar complementos de pago');
                        $('#complementoPagoPDF, #complementoPagoXML').prop('required', false);
                        return;
                    }

                    if (response.cantData === 0) {
                        $textoMensaje.html(response.message || 'No hay facturas que requieran complementos de pago');
                        $textoMensaje.removeClass('alert-info alert-success').addClass('alert-danger');
                        $mensajeDiv.show();
                        $contenedorInputs.hide();
                        $('#complementoPagoPDF, #complementoPagoXML').prop('required', false);
                        $btnSubmit.prop('disabled', true);
                    } else {
                        $mensajeDiv.hide();
                        $contenedorInputs.show();
                        $('#complementoPagoPDF, #complementoPagoXML').prop('required', true);
                        $btnSubmit.prop('disabled', false);
                    }
                },
                error: function() {
                    notificaBad('Error al verificar complementos de pago. Consulta a tu administrador');
                    $mensajeDiv.hide();
                    $contenedorInputs.hide();
                    $('#complementoPagoPDF, #complementoPagoXML').prop('required', false);
                    $btnSubmit.prop('disabled', true);
                }
            });
        }

        function cargaTablaUltimasFacturas() {
            $('#cajaResultados').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            $('#cajaResultados').load('Inicio/tablaUltimas50Facturas');
        }

        function validaHojaEntrada(hojaEntrada) {
            $("#listaHES").removeClass("is-invalid is-valid");
            $("#invalid_listaHES").html("");

            if (hojaEntrada.length === 0) {
                $("#listaHES").addClass("is-invalid");
                $("#invalid_listaHES").html("<?= $menuModel->txt('No_Vacio', 1); ?>");
            } else {
                var estructura = validarEstructuraHES(hojaEntrada);
                console.log(estructura);
                if (estructura.cantidadValidas == 0 && estructura.cantidadInvalidas == 0) {
                    $("#listaHES").addClass("is-invalid");
                    $("#invalid_listaHES").html("Estructura incorrecta, coloca una o mas entradas separadas por comas: HES-XXX-######");
                    validHES = false;
                } else if (estructura.cantidadInvalidas > 0) {
                    $("#listaHES").addClass("is-invalid");
                    $("#invalid_listaHES").html(estructura.cantidadInvalidas + " incorrectas: " + estructura.invalidas);
                    validHES = false;
                } else if (estructura.cantidadValidas > 0) {
                    if (validOC) {
                        var oc = $("#ordenCompra").val();
                        $.ajax({
                            type: 'POST',
                            url: 'Inicio/validaHojaEntrada',
                            data: {
                                ordenCompra: oc,
                                listaHES: hojaEntrada
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    validHES = true;
                                    $("#listaHES").addClass("is-valid");
                                } else {
                                    validHES = false;
                                    $("#listaHES").addClass("is-invalid");
                                    $("#invalid_listaHES").html(response.message);
                                }
                            },
                            error: function() {
                                notificaBad('Problemas al consultar la Hoja de Entrada. Notifica a tu administrador');
                                validaOC = false;
                            }
                        });
                    } else {
                        $("#listaHES").addClass("is-invalid");
                        $("#invalid_listaHES").html("Coloca primero una Orden de Compra Valida.");
                        validHES = false;
                    }

                }
            }


        }

        function validaAnticipo(ordenCompra) {
            $("#codAnticipo").removeClass("is-invalid is-valid");
            $("#invalid_codAnticipo").html("");

            ant = validarEstructuraANT(ordenCompra);
            console.log(validarEstructuraANT(ordenCompra));
            console.log(ant.valor);

            $("#codAnticipo").val(ant.valor);
            if (ant.valido) {
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/validaCodigoAnticipo',
                    data: {
                        anticipo: ant.valor
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            validAnt = true;
                            $("#codAnticipo").addClass("is-valid");
                        } else {
                            validAnt = false;
                            $("#codAnticipo").addClass("is-invalid");
                            $("#invalid_codAnticipo").html(response.message);
                        }
                    },
                    error: function() {
                        notificaBad('Problemas al consultar el Anticipo. Notifica a tu administrador');
                        validAnt = false;
                    }
                });
            } else {
                $("#codAnticipo").addClass("is-invalid");
                $("#invalid_codAnticipo").html("Estructura: ANT-XXX-######");
                validAnt = false;
            }
        }

        function validarEstructuraOC(str) {
            // Expresión regular para validar COM-XXX-######
            var pattern = /^COM-[A-Z]{3}-\d{6}$/;

            // Convertir a mayúsculas
            var textoTransformado = str.toUpperCase();

            // Validar el texto transformado contra el patrón
            var esValido = pattern.test(textoTransformado);

            return {
                valido: esValido, // true si es válido, false si no
                valor: textoTransformado // el texto transformado en mayúsculas
            };
        }

        function validarEstructuraANT(str) {
            // Expresión regular para validar COM-XXX-######
            var pattern = /^ANT-[A-Z]{3}-\d{6}$/;

            // Convertir a mayúsculas
            var textoTransformado = str.toUpperCase();

            // Validar el texto transformado contra el patrón
            var esValido = pattern.test(textoTransformado);

            return {
                valido: esValido, // true si es válido, false si no
                valor: textoTransformado // el texto transformado en mayúsculas
            };
        }

        function validarEstructuraHES(textareaValue) {
            // Expresión regular para validar el formato HES-XXX-######
            const pattern = /^HES-[A-Z]{3}-\d{6}$/;

            // Dividir las entradas separadas por coma, eliminar espacios extra y convertir a mayúsculas
            const entradas = textareaValue.split(",").map(item => item.trim().toUpperCase());

            // Inicializar resultados
            const validas = [];
            const invalidas = [];

            // Validar cada entrada
            entradas.forEach(entrada => {
                if (pattern.test(entrada)) {
                    validas.push(entrada); // Si es válida, agregar a válidas
                } else {
                    invalidas.push(entrada + ' '); // Si no es válida, agregar a inválidas
                }
            });

            // Retornar resultados con cantidades y valores
            return {
                cantidadValidas: validas.length,
                cantidadInvalidas: invalidas.length,
                validas: validas,
                invalidas: invalidas
            };
        }

        function bloquearBtn(btn) {
            $('button[type="submit"]').prop('disabled', true);
            $('#desbloquear-' + btn).hide();
            $('#bloquear-' + btn).show();
        }

        function desbloquearBtn(btn) {
            $('button[type="submit"]').prop('disabled', false);
            $('#desbloquear-' + btn).show();
            $('#bloquear-' + btn).hide();
        }

        function resetFormulario(idForm) {
            $('#' + idForm)[0].reset();
            $(".custom-file-input").each(function() {
                $(this).val(''); // Restablece el input
                $(this).next('.custom-file-label').text('Elegir archivo...'); // Restablece el label
            });
        }
    </script>

</body>

</html>