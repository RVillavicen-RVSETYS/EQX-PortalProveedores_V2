<?php
$debug = 0; // Vista admin carga facturas: trazas en pantalla

$requestUri = $_SERVER['REQUEST_URI'];
$cleanUri = parse_url($requestUri, PHP_URL_PATH);
$piezasURL = explode('/', trim($cleanUri, '/'));
$paginaLink = $piezasURL[1];
include '../app/Views/Layout/funciones.php';

$funcionMenu = generarMenu($menuData['data'], $paginaLink);
$datosPagina = $funcionMenu['datosPagina'];
$rutaMenu = $funcionMenu['rutaMenu'];

if ($debug == 1) {
    echo 'Contenido de menuData:';
    var_dump($menuData);
    echo '<br><br>Contenido de areaData:';
    var_dump($areaData);
    echo '<br><br>Contenido de areaLink:';
    var_dump($areaLink);
    echo '<br><br>Contenido de _SESSION:';
    var_dump($_SESSION);
    echo '<br><br>Request-URI: ' . $_SERVER['REQUEST_URI'] . '<br>Contenido de piezasURL:';
    var_dump($piezasURL);
    echo '<br><br>Ruta del MenuActual: ' . $rutaMenu . '<br><br>Contenido de datosPagina:';
    var_dump($datosPagina);
}

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];
$mesActual = (int)date('n');
$nombreMesActual = $meses[$mesActual] ?? '';

?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>
    <?php include '../app/Views/Layout/header.php'; ?>
    <!-- Custom CSS -->

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
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <?php include '../app/Views/Layout/title.php'; ?>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->

            <!-- ============================================================== -->
            <!-- Inicia Contenido fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->

                <div class="row">

                    <div class="col-md-8 col-lg-8">
                        <div class="card border">
                            <div class="card-header bg-pyme-primary">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="m-b-0 text-white" id="tituloFacturasMes">Facturas Cargadas en <?= $nombreMesActual; ?></h4>
                                    </div>
                                    <div class="ml-auto">
                                        <select id="mesFacturas" class="custom-select border-0 text-muted  bg-pyme-primary">
                                            <?php foreach ($meses as $numeroMes => $nombreMes) { ?>
                                                <option value="<?= $numeroMes; ?>" data-mes="<?= $nombreMes; ?>" <?= $numeroMes === $mesActual ? 'selected' : ''; ?>><?= $nombreMes; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="TablaFacturasCargadasPorAdmin" class="jsgrid" style="position: relative; height: auto; width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-4">

                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white">Carga Factura</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form" id="Form_CargaFactura" method="post" enctype="multipart/form-data" action="CargarFacturas/registraNuevaFactura">

                                                    <div class="form-group row">
                                                        <label for="noProveedor" class="col-3 col-form-label">Proveedor</label>
                                                        <div class="col-9 input-group mb-3">
                                                            <select name="noProveedor" id="noProveedor" class="select2 form-control custom-select" style="width: 100%;height: 36px;" required>
                                                                <option value="">Selecciona Un Proveedor</option>
                                                                <?php
                                                                foreach ($listaProveedores['data'] as $proveedor) {
                                                                ?>
                                                                    <option value="<?= $proveedor['IdProveedor']; ?>"> <?= $proveedor['IdProveedor']; ?> - <?= $proveedor['RazonSocial']; ?> (<?= $proveedor['Proveedor']; ?>) </option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="ordenCompra" class="col-3 col-form-label">Orden Compra</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="ordenCompra" name="ordenCompra" maxlength="14" onchange="validaOrdCompra(this.value,'FACT');" required>
                                                            <div class="invalid-feedback" id="invalid_ordenCompra">
                                                                <img src="../assets/images/barLoadign.gif" alt="" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="listaHES" class="col-12 col-form-label">Numeros Recepción</label>
                                                        <div class="col-12">
                                                            <textarea class="form-control" id="listaHES" name="listaHES" rows="3" onblur="validaHojaEntrada(this.value);" required></textarea>
                                                            <div class="invalid-feedback" id="invalid_listaHES"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="facturaPDF">Facturas</label>
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
                                                        <button type="button" class="btn btn-success mt-2" onclick="cargarFormNotaCredito(lastNotasCredito, 'FACT')"><i class="fas fa-plus"></i> Nota de Crédito</button>
                                                    </div>

                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>

                                                        <div id="desbloquear-btn1">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> Limpiar</button>
                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Factura</button>
                                                        </div>
                                                        <div id="bloquear-btn1" style="display: none;">
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
                                                <h4 class="m-b-0 text-white">Carga Complementos de Pago</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form" id="Form_CargaComplementoPago" method="post" enctype="multipart/form-data" action="CargarFacturas/registraNuevoComplementoPago">

                                                    <div class="form-group row">
                                                        <label for="noProveedorCP" class="col-3 col-form-label">Proveedor</label>
                                                        <div class="col-9 input-group mb-3">
                                                            <select name="noProveedorCP" id="noProveedorCP" class="select2 form-control custom-select" style="width: 100%;height: 36px;" required>
                                                                <option value="">Selecciona un Proveedor</option>
                                                                <?php
                                                                foreach ($listaProveedores['data'] as $proveedor) {
                                                                ?>
                                                                    <option value="<?= $proveedor['IdProveedor']; ?>"> <?= $proveedor['IdProveedor']; ?> - <?= $proveedor['RazonSocial']; ?> (<?= $proveedor['Proveedor']; ?>) </option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

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
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> Limpiar</button>
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

                                                <form class="form" id="Form_CargaNotaCredito" method="post" enctype="multipart/form-data" action="CargarFacturas/registraNotaCredito">

                                                    <div class="form-group row">
                                                        <label for="noProveedorNC" class="col-3 col-form-label">Proveedor</label>
                                                        <div class="col-9 input-group mb-3">
                                                            <select name="noProveedorNC" id="noProveedorNC" class="select2 form-control custom-select" style="width: 100%;height: 36px;" required>
                                                                <option value="">Selecciona Un Proveedor</option>
                                                                <?php
                                                                foreach ($listaProveedores['data'] as $proveedor) {
                                                                ?>
                                                                    <option value="<?= $proveedor['IdProveedor']; ?>"> <?= $proveedor['IdProveedor']; ?> - <?= $proveedor['RazonSocial']; ?> (<?= $proveedor['Proveedor']; ?>) </option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="ordenCompraNC" class="col-3 col-form-label">Orden Compra</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="ordenCompraNC" name="ordenCompraNC" maxlength="14" onchange="validaOrdCompra(this.value,'NC');" required>
                                                            <div class="invalid-feedback" id="invalid_ordenCompraNC">
                                                                <img src="../assets/images/barLoadign.gif" alt="" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Factura Ingresada (Nuevo campo) -->
                                                    <div class="form-group row" id="campoFacturaIngresada" style="display: none;">
                                                        <label for="idCompraNC" class="col-3 col-form-label">Factura Ingresada</label>
                                                        <div class="col-9">
                                                            <select class="form-control custom-select" id="idCompraNC" name="idCompraNC" style="width: 100%; height:36px;" required>
                                                                <option value="">Seleccione una factura...</option>
                                                            </select>
                                                            <div class="invalid-feedback" id="invalid_idCompraNC"></div>
                                                        </div>
                                                    </div>

                                                    <div id="contentNotaCreditoNC">

                                                    </div>
                                                    <div class="justify-content-end d-none" id="btnNotaCreditoNC">
                                                        <button type="button" class="btn btn-success mt-2" onclick="cargarFormNotaCredito(lastNotasCredito,'NC')"><i class="fas fa-plus"></i> Nota de Crédito</button>
                                                    </div>

                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>

                                                        <div id="desbloquear-btnNC">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> Limpiar</button>
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
                                                <h4 class="m-b-0 text-white">Carga Factura por Anticipo</h4>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form">
                                                    <div class="form-group row">
                                                        <label for="noProveedorANT" class="col-3 col-form-label">Proveedor</label>
                                                        <div class="col-9 input-group mb-3">
                                                            <select name="noProveedorANT" id="noProveedorANT" class="select2 form-control custom-select" style="width: 100%;height: 36px;" required>
                                                                <option value="">Selecciona Un Proveedor</option>
                                                                <?php
                                                                foreach ($listaProveedores['data'] as $proveedor) {
                                                                ?>
                                                                    <option value="<?= $proveedor['IdProveedor']; ?>"> <?= $proveedor['IdProveedor']; ?> - <?= $proveedor['RazonSocial']; ?> (<?= $proveedor['Proveedor']; ?>) </option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="codAnticipo" class="col-3 col-form-label">Código Anticipo</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="codAnticipo" name="codAnticipo" maxlength="14" onchange="validaAnticipo(this.value);" required>
                                                            <div class="invalid-feedback" id="invalid_codAnticipo"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="anticipoPDF">Facturas</label>
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

                                                        <div id="desbloquear-btn1">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i>Limpiar</button>

                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Factura</button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- ============================================================== -->
                <!-- Sales chart -->
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
    <div class="chat-windows"></div>
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

        function obtenerFacturas(ordenCompra, politicasNC = []) {
            const $campoFactura = $('#campoFacturaIngresada');
            const $selectFactura = $('#idCompraNC');
            const $invalidMsg = $('#invalid_idCompraNC');

            console.log('=== obtenerFacturas ===');
            console.log('ordenCompra:', ordenCompra);
            console.log('politicasNC:', politicasNC);

            $selectFactura.empty().append('<option value="">Cargando...</option>');
            $invalidMsg.html('');

            // Extraer los IdNotaCredito de las políticas disponibles
            const idsNotaCredito = politicasNC.map(nc => nc.IdNotaCredito || nc.idNotaCredito).filter(id => id);
            console.log('idsNotaCredito extraídos:', idsNotaCredito);

            $.ajax({
                type: 'POST',
                url: 'CargarFacturas/obtenerFacturasPorOC',
                data: {
                    ordenCompra: ordenCompra,
                    idsNotaCredito: idsNotaCredito
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta de obtenerFacturasPorOC:', response);
                    $selectFactura.empty(); // Limpiar opciones
                    if (response.success && response.data && response.data.length > 0) {
                        console.log('Facturas encontradas:', response.data.length);
                        $selectFactura.append('<option value="">Seleccione una factura...</option>');
                        response.data.forEach(function(factura) {
                            // Formato: Acuse: 123 - Folio: A-456
                            const texto = `Acuse: ${factura.idCompra} - Folio: ${factura.serie || ''}${factura.folio}`;
                            $selectFactura.append(new Option(texto, factura.idCompra));
                        });
                        $campoFactura.show(); // Mostrar el campo
                        console.log('Campo de factura mostrado');
                    } else {
                        console.warn('No se encontraron facturas o respuesta no exitosa');
                        $selectFactura.append('<option value="">No se encontraron facturas</option>');
                        $invalidMsg.html(response.message || 'No hay facturas para esta OC.');
                        $campoFactura.show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en obtenerFacturasPorOC:', status, error);
                    console.error('Response:', xhr.responseText);
                    $selectFactura.empty().append('<option value="">Error al cargar</option>');
                    $invalidMsg.html('Error de comunicación con el servidor.');
                    $campoFactura.hide();
                }
            });
        }

        function validaOrdCompra(ordenCompra, tipo) {
            // Si la orden de compra está vacía o es null, no validar (probablemente se está limpiando el formulario)
            if (!ordenCompra || ordenCompra.trim() === '') {
                return;
            }

            const isFactura = tipo === 'FACT';
            const prefix = isFactura ? '' : 'NC';

            const noProveedor = $(`#noProveedor${prefix}`).val();
            if (!noProveedor) {
                notificaBad('Selecciona un proveedor');
                return; // Salir temprano si no hay proveedor
            }

            const $inputOC = $(`#ordenCompra${prefix}`);
            const $invalidMsg = $(`#invalid_ordenCompra${prefix}`);
            const $contentNota = $(`#contentNotaCredito${prefix}`);
            const $btnNota = $(`#btnNotaCredito${prefix}`);

            $inputOC.removeClass("is-invalid is-valid");
            $invalidMsg.html("");

            const oc = validarEstructuraOC(ordenCompra);
            console.log(validarEstructuraOC(ordenCompra));
            console.log(oc.valor);

            $inputOC.val(oc.valor);

            if (oc.valido) {
                // Determinar la URL según el tipo de flujo
                let urlAjax = '';
                if (tipo === 'NC') {
                    urlAjax = 'CargarFacturas/verificaOrdenCompraNotaCredito';
                } else {
                    urlAjax = 'CargarFacturas/verificaOrdenCompraFactura';
                }

                $.ajax({
                    type: 'POST',
                    url: urlAjax,
                    data: {
                        ordenCompra: oc.valor,
                        noProveedor: noProveedor
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            validOC = true;
                            $inputOC.addClass("is-valid");
                            $contentNota.empty();
                            $btnNota.addClass("d-none");
                            contadorFormNotas = 0;
                            selectedNoteCreditIds.clear();

                            if (tipo === 'NC') {
                                // FLUJO: Carga Notas De Crédito (independiente)
                                const cantNC = response.cantNC ?? 0;
                                console.log('=== FLUJO Carga Notas De Crédito ===');
                                console.log('cantNC:', cantNC);
                                console.log('response completa:', response);

                                if (cantNC === 0) {
                                    // No hay notas de crédito pendientes
                                    console.log('No hay notas de crédito pendientes (cantNC = 0)');
                                    $invalidMsg.html(response.messageNC || 'La Orden de Compra registrada no tiene Notas de Credito Pendientes');
                                    $('#campoFacturaIngresada').hide();
                                    lastNotasCredito = [];
                                } else {
                                    // Hay notas de crédito pendientes
                                    console.log('Hay notas de crédito pendientes (cantNC > 0):', cantNC);
                                    // Guardamos las notas de crédito disponibles
                                    if (response.NC) {
                                        lastNotasCredito = response.NC;
                                        console.log('lastNotasCredito cargado:', lastNotasCredito);
                                    } else {
                                        console.error('ERROR: response.NC no existe pero cantNC > 0');
                                        lastNotasCredito = [];
                                    }
                                    // Limpiar mensaje de error si existía
                                    $invalidMsg.html('');
                                    // Obtenemos las facturas que tienen notas de crédito pendientes
                                    console.log('Llamando a obtenerFacturas con OC:', oc.valor, 'y lastNotasCredito:', lastNotasCredito);
                                    obtenerFacturas(oc.valor, lastNotasCredito);
                                }
                            } else {
                                // FLUJO: Carga Factura (tipo === 'FACT')
                                console.log('=== FLUJO Carga Factura ===');
                                console.log('cantHES:', response.cantHES);
                                console.log('cantNC:', response.cantNC);
                                console.log('messageHES:', response.messageHES);
                                console.log('messageNC:', response.messageNC);

                                // Verificar si hay HES pendientes
                                const cantHES = response.cantHES ?? 0;
                                const cantNC = response.cantNC ?? 0;

                                if (cantHES > 0) {
                                    // Hay HES pendientes, puede cargar factura
                                    // Si hay NC pendientes, cargar el formulario de NC
                                    if (cantNC > 0 && response.NC) {
                                        console.log('Hay notas de crédito pendientes:', response.messageNC);
                                        // Guardar las políticas de NC disponibles
                                        lastNotasCredito = response.NC;
                                        console.log('lastNotasCredito cargado:', lastNotasCredito);
                                        // Cargar el formulario de notas de crédito
                                        cargarFormNotaCredito(lastNotasCredito, tipo);
                                        $btnNota.removeClass("d-none");
                                    }
                                } else {
                                    // No hay HES pendientes
                                    $invalidMsg.html(response.messageHES || 'No hay HES pendientes para esta Orden de Compra');
                                }

                            }
                        } else {
                            validOC = false;
                            $inputOC.addClass("is-invalid");
                            $invalidMsg.html(response.message || 'Error al validar la Orden de Compra');
                            if (tipo === 'NC') {
                                $('#campoFacturaIngresada').hide();
                            }
                            lastNotasCredito = [];
                        }
                    },
                    error: function() {
                        notificaBad('Error al validar la Orden de Compra. Consulta a tu administrador');
                        validOC = false;
                    }
                });
            } else {
                $inputOC.addClass("is-invalid");
                $invalidMsg.html("Estructura: COM-XXX-######");
                validOC = false;
            }
        }

        let contadorFormNotas = 0;

        function cargarFormNotaCredito(arrayNotasCredito, tipo) {
            contadorFormNotas++; // Incrementa el contador para el nuevo bloque de formulario

            // URL y contenedor de destino para la solicitud AJAX
            const ajaxUrl = 'CargarFacturas/cargaFormNotaCredito';
            let targetContentId = '';
            let targetButtonId = '';

            switch (tipo) {
                case 'FACT':
                    targetContentId = '#contentNotaCredito';
                    targetButtonId = '#btnNotaCredito';
                    break;
                case 'NC':
                    targetContentId = '#contentNotaCreditoNC';
                    targetButtonId = '#btnNotaCreditoNC';
                    break;
                default:
                    console.error('Tipo de documento no válido:', tipo);
                    return; // Sale de la función si el tipo no es reconocido
            }

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: {
                    id: contadorFormNotas,
                    // Enviar el array de notas de crédito al servidor, si el PHP lo necesita
                    // Aunque para esta funcionalidad, el lado del cliente tiene la lista completa.
                    arrayNotasCredito: arrayNotasCredito
                },
                success: function(response) {
                    // Reemplaza el placeholder {{id}} en el HTML recibido con el contador único
                    const formHtml = response.replace(/{{id}}/g, contadorFormNotas);
                    // Añade el nuevo bloque de formulario al contenedor correspondiente
                    $(targetContentId).append(formHtml);

                    // Obtiene la referencia al elemento <select> que acaba de ser añadido
                    const $select = $(`#notaCredito_${contadorFormNotas}`);

                    // Itera sobre el array de notas de crédito para poblar el select
                    arrayNotasCredito.forEach(nc => {
                        // Solo añade la opción si NO ha sido seleccionada ya en otro select
                        if (!selectedNoteCreditIds.has(nc.IdNotaCredito)) {
                            const esObligatoria = nc.Obligatoria == "1";
                            const optionText = esObligatoria ? `🔴 ${nc.Descripcion}` : nc.Descripcion;
                            const option = new Option(optionText, nc.IdNotaCredito, false, false);
                            $(option).attr('data-obligatoria', nc.Obligatoria);
                            $select.append(option);
                        }
                    });

                    // Inicializa select2 en el nuevo select (ya con las opciones filtradas)
                    $select.select2();

                    // Muestra el botón de guardar si estaba oculto
                    $(targetButtonId).removeClass('d-none');
                },
                error: function() {
                    // Manejo de errores en caso de que la carga del formulario falle
                    $(targetContentId).append('<div class="text-danger">Error cargando formulario de Nota de Crédito.</div>');
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

        $(document).on('change', '#idCompraNC', function() {
            const selectedInvoice = $(this).val();
            const $contentNota = $('#contentNotaCreditoNC');
            const $btnNota = $('#btnNotaCreditoNC');

            console.log('=== Evento change #idCompraNC ===');
            console.log('Factura seleccionada:', selectedInvoice);
            console.log('lastNotasCredito actual:', lastNotasCredito);

            // Limpiar formularios de NC previos
            $contentNota.empty();
            $btnNota.addClass("d-none");
            contadorFormNotas = 0;
            selectedNoteCreditIds.clear();

            if (selectedInvoice) {
                // lastNotasCredito debería estar cargado desde validaOrdCompra cuando cantData > 0
                // Si está vacío, significa que hubo un problema en el flujo
                if (lastNotasCredito && lastNotasCredito.length > 0) {
                    console.log('Cargando formulario de notas de crédito con', lastNotasCredito.length, 'políticas disponibles');
                    // Cargar el formulario de notas de crédito directamente
                    cargarFormNotaCredito(lastNotasCredito, 'NC');
                    $btnNota.removeClass("d-none");
                    console.log('Formulario de notas de crédito cargado correctamente');
                } else {
                    console.error('ERROR: lastNotasCredito está vacío al seleccionar factura. Esto no debería pasar.');
                    console.error('lastNotasCredito:', lastNotasCredito);
                    notificaBad('Error: No hay notas de crédito disponibles. Por favor, valida nuevamente la Orden de Compra.');
                }
            } else {
                console.log('No se seleccionó ninguna factura');
            }
        });

        $(document).ready(function() {
            $('.select2').select2();
            const $mesFacturas = $('#mesFacturas');
            const $tituloFacturasMes = $('#tituloFacturasMes');
            const actualizarTituloMes = () => {
                const mesSeleccionado = $mesFacturas.find('option:selected').data('mes') || '';
                $tituloFacturasMes.text('Facturas Cargadas en ' + mesSeleccionado);
            };
            actualizarTituloMes();
            $mesFacturas.on('change', actualizarTituloMes);

            const cargarFacturasCargadas = () => {
                const mes = parseInt($mesFacturas.val(), 10);
                const anio = new Date().getFullYear();
                const fechaInicial = `${anio}-${String(mes).padStart(2, '0')}-01`;
                const ultimoDia = new Date(anio, mes, 0).getDate();
                const fechaFinal = `${anio}-${String(mes).padStart(2, '0')}-${String(ultimoDia).padStart(2, '0')}`;

                $.ajax({
                    type: 'POST',
                    url: 'FacturasNacionales/listaAprobacionesNa',
                    data: {
                        fechaInicial: fechaInicial,
                        fechaFinal: fechaFinal
                    },
                    success: function(response) {
                        $('#TablaFacturasCargadasPorAdmin').html(response);
                    },
                    error: function() {
                        $('#TablaFacturasCargadasPorAdmin').html('Error al cargar la lista de CFDIs. Consulta a tu administrador.');
                    },
                    beforeSend: function() {
                        $('#TablaFacturasCargadasPorAdmin').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                    }
                });
            };

            cargarFacturasCargadas();
            $mesFacturas.on('change', cargarFacturasCargadas);

            let validOC = false;
            let validHES = false;
            let reqAnticipo = false;

            $("#Form_CargaFactura").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                bloquearBtn('btn1');
                $.ajax({
                    type: 'POST',
                    url: 'CargarFacturas/registraNuevaFactura',
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
                            $('#contentNotaCreditoNC').empty();
                            $('#btnNotaCredito').addClass("d-none");
                            $('#btnNotaCreditoNC').addClass("d-none");
                            // Limpiar select2 del proveedor y actualizar visualización
                            $('#noProveedor').val(null).trigger('change.select2');
                            // Limpiar otros select2 si existen
                            $('#idCompraNC').val(null).trigger('change.select2');
                            // Limpiar campos de texto sin disparar eventos de validación
                            $('#ordenCompra').val(null);
                            $('#listaHES').val(null);
                            // Remover clases de validación
                            $('#ordenCompra').removeClass("is-invalid is-valid");
                            $('#listaHES').removeClass("is-invalid is-valid");
                            $('#invalid_ordenCompra').html('');
                            $('#invalid_listaHES').html('');
                            // Resetear variables globales
                            contadorFormNotas = 0;
                            selectedNoteCreditIds.clear();
                            validOC = false;
                            validHES = false;
                            reqAnticipo = false;
                            lastNotasCredito = [];
                            notificaSucSweet("Excelente!!", response.message);
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

            // Traere datos iniciales
            //cargarDatosIniciales();
            //cargaTablaUltimasFacturas();
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

        $("#Form_CargaNotaCredito").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            bloquearBtn('btnNC');
            $.ajax({
                type: 'POST',
                url: 'CargarFacturas/registraNuevaNotaCredito',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    desbloquearBtn('btnNC');
                    if (response.success) {
                        notificaSucSweet("Excelente!!", response.message);

                        // 1. Limpiar formulario estándar
                        resetFormulario("Form_CargaNotaCredito");

                        // 2. Ocultar y vaciar contenedores dinámicos
                        $('#campoFacturaIngresada').hide();
                        $('#contentNotaCreditoNC').empty();
                        $('#btnNotaCreditoNC').addClass('d-none');

                        // 3. Resetear Select2 y otros campos manualmente
                        $('#noProveedorNC').val(null).trigger('change.select2');
                        $('#idCompraNC').val(null).trigger('change.select2');
                        $('#idCompraNC').empty().append('<option value="">Seleccione una factura...</option>');

                    } else {
                        // Cuando hay error, NO limpiar el formulario para que el usuario pueda corregir
                        notificaBadSweet("Lo sentimos!!", response.message);
                    }
                },
                error: function() {
                    notificaBad('Error al querer cargar factura. Consulta a tu administrador');
                    desbloquearBtn('btnNC');
                }
            });
        });

        // Handler para el formulario de Complementos de Pago
        $("#Form_CargaComplementoPago").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            bloquearBtn('btnCP');
            $.ajax({
                type: 'POST',
                url: 'CargarFacturas/registraNuevoComplementoPago',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    desbloquearBtn('btnCP');
                    if (response.success) {
                        notificaSucSweet("Excelente!!", response.message);

                        // Limpiar formulario
                        resetFormulario("Form_CargaComplementoPago");

                        // Ocultar contenedores y resetear campos
                        $('#contenedorInputsComplementoPago').hide();
                        $('#mensajeComplementoPago').hide();
                        $('#textoMensajeComplementoPago').html('');
                        $('#noProveedorCP').val(null).trigger('change.select2');
                        $('#Form_CargaComplementoPago button[type="submit"]').prop('disabled', true);
                    } else {
                        notificaBadSweet("Lo sentimos!!", response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la petición:', {
                        xhr: xhr,
                        status: status,
                        error: error
                    });
                    notificaBad('Error al querer cargar complemento de pago. Consulta a tu administrador. Estado: ' + xhr.status);
                    desbloquearBtn('btnCP');
                }
            });
        });

        // Evento para el botón de reset del formulario de Complementos de Pago
        $(document).on('click', '#Form_CargaComplementoPago button[type="reset"]', function() {
            // Ocultar contenedores y limpiar mensajes
            $('#contenedorInputsComplementoPago').hide();
            $('#mensajeComplementoPago').hide();
            $('#textoMensajeComplementoPago').html('');
            $('#Form_CargaComplementoPago button[type="submit"]').prop('disabled', true);
        });

        // Función para verificar si el proveedor debe complementos de pago
        function VerificaSiDebeComplementosPago(noProveedor) {
            const $mensajeDiv = $('#mensajeComplementoPago');
            const $textoMensaje = $('#textoMensajeComplementoPago');
            const $contenedorInputs = $('#contenedorInputsComplementoPago');
            const $btnSubmit = $('#Form_CargaComplementoPago button[type="submit"]');

            // Ocultar elementos inicialmente
            $mensajeDiv.hide();
            $contenedorInputs.hide();
            $btnSubmit.prop('disabled', true);

            // Limpiar mensajes previos
            $textoMensaje.html('');

            if (!noProveedor || noProveedor === '') {
                $btnSubmit.prop('disabled', true);
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'CargarFacturas/VerificaSiDebeComplementosPago',
                data: {
                    noProveedor: noProveedor
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success === false) {
                        // Mostrar error usando notificaBad si success es false
                        notificaBad(response.message || 'Error al verificar complementos de pago');
                        $mensajeDiv.hide();
                        $contenedorInputs.hide();
                        // Deshabilitar campos requeridos y botón
                        $('#complementoPagoPDF, #complementoPagoXML').prop('required', false);
                        $btnSubmit.prop('disabled', true);
                    } else if (response.cantData === 0) {
                        // Si cantData es 0, mostrar mensaje en rojo y ocultar inputs
                        $textoMensaje.html(response.message || 'No hay facturas que requieran complementos de pago');
                        $textoMensaje.removeClass('alert-info alert-success').addClass('alert-danger');
                        $mensajeDiv.show();
                        $contenedorInputs.hide();
                        // Deshabilitar campos requeridos y botón
                        $('#complementoPagoPDF, #complementoPagoXML').prop('required', false);
                        $btnSubmit.prop('disabled', true);
                    } else if (response.cantData >= 1) {
                        // Si cantData >= 1, mostrar inputs
                        $mensajeDiv.hide();
                        $contenedorInputs.show();
                        // Habilitar campos requeridos y botón
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

        // Evento change para el select de proveedor en Complementos de Pago
        $(document).on('change', '#noProveedorCP', function() {
            const noProveedor = $(this).val();
            VerificaSiDebeComplementosPago(noProveedor);
        });

        function cargaTablaUltimasFacturas() {
            $('#cajaResultados').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            $('#cajaResultados').load('Inicio/tablaUltimas50Facturas');
        }

        function validaHojaEntrada(hojaEntrada) {
            $("#listaHES").removeClass("is-invalid is-valid");
            $("#invalid_listaHES").html("");

            if (hojaEntrada.length === 0) {
                $("#listaHES").addClass("is-invalid");
                $("#invalid_listaHES").html("No puede estar vacío");
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
                            url: 'CargarFacturas/validaHojaEntrada',
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
            const noProveedor = $("#noProveedorANT").val();
            if (!noProveedor) {
                notificaBad('Selecciona un proveedor');
                return;
            }

            $("#codAnticipo").removeClass("is-invalid is-valid");
            $("#invalid_codAnticipo").html("");

            ant = validarEstructuraANT(ordenCompra);
            console.log(validarEstructuraANT(ordenCompra));
            console.log(ant.valor);

            $("#codAnticipo").val(ant.valor);
            if (ant.valido) {
                $.ajax({
                    type: 'POST',
                    url: 'CargarFacturas/validaCodigoAnticipo',
                    data: {
                        anticipo: ant.valor,
                        noProveedor: noProveedor
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

        function detalleCompra(acuse, idProveedor) {
            $('#customizer_body').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            $(".customizer").toggleClass('show-service-panel');
            $(".service-panel-toggle").toggle();
            $.post("FacturasNacionales/detalladoDeCompra", {
                    acuse: acuse,
                    idProveedor: idProveedor,
                    soloVer: 1
                },
                function(respuesta) {
                    $("#customizer_body").html(respuesta);
                });
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