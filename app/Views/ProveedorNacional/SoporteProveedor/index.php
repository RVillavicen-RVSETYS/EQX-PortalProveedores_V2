<?php
$debug = 0;

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
    echo '<br><br>Valores pasados del Controller Data:';
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

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

<![endif]-->
    <style>
        /* 1. Todas las letras e iconos de las pestañas SIEMPRE en blanco */
        .note-has-grid .nav-pills .nav-link {
            color: #ffffff !important;
        }

        /* 2. El botón que esté activo (.active) tendrá fondo verde y letras blancas (¡adiós azul!) */
        .note-has-grid .nav-pills .nav-link.active {
            background-color: #28a745 !important;
            /* Verde success */
            color: #ffffff !important;
        }

        /* 3. Efecto suave al pasar el cursor sobre las pestañas inactivas */
        .note-has-grid .nav-pills .nav-link:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff !important;
        }
    </style>
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

                <div class="container-fluid note-has-grid">
                    <ul class="nav nav-pills p-3 bg-pyme-primary mb-3 rounded-pill align-items-center">
                        <li class="nav-item"> <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center active px-2 px-md-3 mr-0 mr-md-2" id="Manuales">
                                <i class="icon-book-open mr-1"></i><span class="d-none d-md-block">Manual de Usuario</span></a>
                        </li>
                        <!--<li class="nav-item"> <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-business">
                                <i class="icon-briefcase mr-1"></i><span class="d-none d-md-block">Business</span></a>
                        </li>
                        <li class="nav-item"> <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-social">
                                                    <i class="icon-share-alt mr-1"></i><span class="d-none d-md-block">Social</span></a>
                                            </li>
                                            <li class="nav-item"> <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-important">
                                                    <i class="icon-tag mr-1"></i><span class="d-none d-md-block">Important</span></a>
                                            </li>
                                            <li class="nav-item ml-auto"> <a href="javascript:void(0)" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                                                    <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">Add Notes</span></a>
                                            </li>-->
                    </ul>
                    <div class="tab-content">
                        <!-- Carga de los contenidos -->
                    </div>
                    <!-- ============================================================== -->
                    <!-- footer -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- End footer -->
                    <!-- ============================================================== -->
                    <!-- Modal Add notes -->
                    <div class="modal fade" id="addnotesmodal" tabindex="-1" role="dialog" aria-labelledby="addnotesmodalTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title text-white">Add Notes</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="notes-box">
                                        <div class="notes-content">
                                            <form action="javascript:void(0);" id="addnotesmodalTitle">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <div class="note-title">
                                                            <label>Note Title</label>
                                                            <input type="text" id="note-has-title" class="form-control" minlength="25" placeholder="Title">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="note-description">
                                                            <label>Note Description</label>
                                                            <textarea id="note-has-description" class="form-control" minlength="60" placeholder="Description" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button id="btn-n-save" class="float-left btn btn-success">Save</button>
                                    <button class="btn btn-danger" data-dismiss="modal"> Discard</button>
                                    <button id="btn-n-add" class="btn btn-info" disabled>Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->

                <!--Tarjeta Listado de Filtro-->
                <!--<div class="row">
                    <div class="col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header bg-pyme-primary headerFiltros">
                                <h4 class="m-b-0 text-white">Filtros de búsqueda</h4>
                                <!-- <button class="btn btn-sm btn-pyme px-0" onclick="actualizarFiltros();"><i class="fas fa-undo text-white"></i> </button>-->
            </div>
            <div class="card-body bg-light">
            </div>
        </div>
    </div>
    </div>-->

    <!--Tarjeta Listado de Tabla-->
    <!--<div class="row">
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
                                                <button class="btn bg-pyme-primary btn-md" id="btnCargaComplementoPago" onclick="cargaComplementoPago()">Cargar Complemento de Pago</button>
                                            </div>' : ''; ?>
                                        </div>
                                    </div>
                                    <div class="table-responsive" id="divTablePro"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->


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
    <script src="/assets/extra-libs/prism/prism.js"></script>
    <script src="/dist/js/basicFuctions.js"></script>
    <script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="/dist/js/pages/forms/select2/select2.init.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/jquery.dataTables.min-ESP.js"></script>
    <script src="/dist/js/pages/datatable/datatable-basic.init.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/jszip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/dataTables.buttons.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.flash.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.html5.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.print.min.js"></script>
    <script src="/assets/libs/moment/moment.js"></script>
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/pdfmake.min.js"></script>
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="/assets/libs/sweetalert2/sweet-alert.init.js"></script>

    <script>
        $(document).ready(function() {
            // 1. Cargar los manuales automáticamente al entrar a la página
            cargarManuales();
            // 2. Asociar el evento clic al botón/pestaña "Manual de Usuario"
            $('#Manuales').on('click', function(e) {
                e.preventDefault();
                $('.note-link').removeClass('active');
                $(this).addClass('active');

                cargarManuales();
            });
        });

        function cargarManuales() {
            $.ajax({
                type: 'POST',
                url: 'SoporteProveedor/contenedorManuales',
                beforeSend: function() {
                    // Animación de carga estándar del proyecto
                    $('.tab-content').html('<div class="loading text-center py-4"><img src="/assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                },
                success: function(respuesta) {
                    // Inyecta el HTML de contenedorManuales.php dentro del div
                    $('.tab-content').html(respuesta);
                },
                error: function() {
                    notificaBad('Problemas al cargar el contenido de ayuda.');
                }
            });
        }
    </script>
</body>

</html>