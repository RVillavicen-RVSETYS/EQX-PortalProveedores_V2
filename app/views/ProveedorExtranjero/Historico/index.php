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
    echo '<br><br>Valores pasados del Controller Data:';
    var_dump($data['datosIniciales']);
}
$cantComplementos = $data['datosIniciales']['cantComplementos'];
$oldComplementos = $data['datosIniciales']['oldComplementos'];
$maxComplementosPendientes = $data['datosIniciales']['maxComplementosPendientes'];
$cantCompras = $data['datosIniciales']['cantCompras'];

?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>
    <?php include '../app/Views/Layout/header.php'; ?>
    <!-- Custom CSS -->

    <!-- Vendor -->
    <link href="/assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/fancybox.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/carousel/carousel.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/carousel/carousel.thumbs.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.toolbar.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.pins.css" rel="stylesheet">

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
                <div class="card-group">
                    <!-- Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="mdi mdi-poll font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">Complementos de Pago Pendientes</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?= $cantComplementos; ?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= ($cantComplementos > 0) ? ($cantComplementos * 100 / $maxComplementosPendientes) : 0; ?>%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="mdi mdi-image font-20  text-muted"></i>
                                            <p class="font-16 m-b-5">Limite de Complementos</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?= $maxComplementosPendientes ?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="mdi mdi-emoticon font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">Facturas en <?= date('Y'); ?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?= $cantCompras; ?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= (intval(date('m')) * 100 / 12) ?>%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Tarjeta Listado de Filtro-->
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header bg-pyme-primary headerFiltros">
                                <h4 class="m-b-0 text-white"><?= $menuModel->txt('Filtro_Busqueda'); ?></h4>
                                <!-- <button class="btn btn-sm btn-pyme px-0" onclick="actualizarFiltros();"><i class="fas fa-undo text-white"></i> -->
                                </button>
                            </div>
                            <div class="card-body">
                                <form id="filtrado" method="POST" role="form" autocomplete="off">
                                    <div class="container-fluid">
                                        <div class="row ">
                                            <div class="col-12 col-sm-12 col-md-2 col-lg-3">
                                                <label class="col-form-label"><?= $menuModel->txt('Rango_Fechas'); ?></label>
                                                <div class="input-group input-daterange mb-3" id="date-range">
                                                    <div class="input-group-addon">
                                                        <span class="input-group-text pyme b-0 text-white bg-pyme-primary"><?= $menuModel->txt('Desde'); ?> </span>
                                                    </div>
                                                    <input type="text" class="form-control" name="fechaInicial" id="fechaInicial" />
                                                    <div class="input-group-addon">
                                                        <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> <?= $menuModel->txt('Hasta'); ?> </span>
                                                    </div>
                                                    <input type="text" class="form-control " name="fechaFinal" id="fechaFinal" />
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-3 col-lg-3">
                                                <label for="estatusFactura" class="col-form-label">Estado de Factura</label>
                                                <select class="form-control" name="estatusFactura" id="estatusFactura">
                                                    <option value="">Todos las Facturas</option>
                                                    <option value="1">En Proceso</option>
                                                    <option value="2">Rechazadas</option>
                                                    <option value="3">Pagadas</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-12 col-md-3 col-lg-3">
                                                <label for="estatusComplemento" class="col-form-label">Estado del Complemento</label>
                                                <select class="form-control" name="estatusComplemento" id="estatusComplemento">
                                                    <option value="">Todos los estatus</option>
                                                    <option value="1">Pendientes</option>
                                                    <option value="2">Cargados</option>
                                                    <option value="3">No requerido</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-2 col-md-2 col-lg-2 mt-2 pt-4">
                                                <button type="submit" class="btn bg-pyme-primary waves-effect waves-light">Filtrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Tarjeta Listado de Tabla-->
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <div class="card border">
                            <div class="card-header bg-pyme-primary">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="m-b-0 text-white" id="titleProd">Listado de Facturas</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="validation" class="jsgrid" style="position: relative; height: auto; width: 100%;">
                                    <div class="col-md-12" id="verificaComplementosPago">
                                        <div class="row">
                                            <?= ($cantComplementos > 0) ? '
                                            <div class="col">
                                                <h4 class="text-info"><i class="fa fa-exclamation-circle"></i> ¡Atención!</h4>
                                                <p>Se han detectado <b>' . $cantComplementos . '</b> facturas pendientes de complemento de pago desde ' . $oldComplementos . ', por favor carguelos a la brevedad.</p>
                                            </div>
                                            <div class="col text-right" id="cargaComplementoPago">
                                                <button class="btn bg-pyme-primary btn-md" id="btnCargaComplementoPago">Cargar Complemento de Pago</button>
                                            </div>' : ''; ?>
                                        </div>
                                    </div>
                                    <div class="table-responsive" id="divTablePro"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- ============================================================== 
                <button
                    class="btn btn-success btn-lg mrb50"
                    data-iframe="true"
                    id="open-pdf"
                    data-src="/ProveedorNacional/Inicio/verDocumento/PDF/XERvY3VtZW50b3NcMVxGYWN0dXJhc1wyMDI1XDEwMDAxMVwyMDI1LTAzXDEwMDAxMV9GQUNUXzFfMjAyNTAzMTIwMjM4MDMuUERG/#toolbar=0">
                    Open PDF file
                </button> 
                -->
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
        <div class="customizer-body" id="customizer_body" aria-hidden="true">
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
    <script src="/assets/libs/fancybox/l10n/es.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/panzoom/l10n/es.umd.js"></script>
    <script src="/assets/libs/fancybox/dist/carousel/l10n/es.umd.js"></script>

    <!-- Bootstrap tether Core JavaScript -->
    <script src="/assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Menu -->
    <script type="text/javascript" src="/assets/menu/webslidemenu/webslidemenu.js"></script>
    <!-- apps -->
    <script src="/dist/js/app.min.js"></script>
    <script src="/dist/js/app.init.horizontalSilme.js"></script>
    <script src="/dist/js/app-style-switcher.horizontal.js"></script>
    <script src="/dist/js/app-style-switcher.js"></script>

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
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.js"></script>
    <!--c3 charts -->
    <script src="/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="/assets/libs/moment/moment.js"></script>
    <script src="/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
    <script src="/assets/libs/bootstrap-datepicker/dist/locales/bootstrap-datepicker.es.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            $("#fechaInicial").val(moment().format('01/MM/YYYY'));
            $("#fechaFinal").val(moment().format('DD/MM/YYYY'));
            jQuery('.mydatepicker, #datepicker, .input-group.date').datepicker();
            jQuery('#date-range').datepicker({
                toggleActive: true,
                orientation: "bottom",
                language: 'es'
            });
        });

        $("#filtrado").submit(function(e) {
            e.preventDefault();
            var formData = $("#filtrado").serialize();
            $.ajax({
                type: 'POST',
                url: 'Historico/HistorialFacturasRecibidas',
                data: formData,
                success: function(respuesta) {
                    $('#divTablePro').html(respuesta);
                },
                error: function() {
                    notificaBad('Problemas al consultar tus Facturas. Notifica a tu administrador');
                },
                beforeSend: function() {
                    $('#divTablePro').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                }
            });
        });

        function detalleCompra(acuse) {
            $('#customizer_body').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            $(".customizer").toggleClass('show-service-panel');
            $(".service-panel-toggle").toggle();
            $.post("Historico/detalladoDeCompra", {
                    acuse: acuse
                },
                function(respuesta) {
                    $("#customizer_body").html(respuesta);
                });
        }
    </script>
</body>

</html>