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
    echo '<br><br>Lista De Proveedores <br><br>Contenido de datosPagina:';
    var_dump($listaProveedores['data']);
}

?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>
    <?php include '../app/Views/Layout/header.php'; ?>
    <!-- Custom CSS -->

    <link href="/assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/fancybox/fancybox.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/carousel/carousel.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/carousel/carousel.thumbs.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.toolbar.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.pins.css" rel="stylesheet">
    <link href="/assets/libs/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

<![endif]-->
    <style>
        #btnActualizaProveedores {
            display: inline-block;
            padding: 8px 15px;
            border: 2px solid white;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
            color: white;
        }

        #btnActualizaProveedores:hover {
            background-color: white;
            color: black;
            border-color: #ddd;
        }

        #resetPass {
            display: inline-block;
            padding: 8px 15px;
            border: 2px solid rgb(69, 157, 52);
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
            color: rgb(69, 157, 52);
        }

        #resetPass:hover {
            background-color: rgb(69, 157, 52);
            color: white;
            border-color: #ddd;
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
                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-pyme-primary text-white d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Selecciona Un Proveedor</h4>
                                <a href="#" id="btnActualizaProveedores">Actualizar Proveedores</a>
                            </div>
                            <div class="card-body">
                                <form id="consultarProveedor">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="proveedor">Lista De Proveedores</label>
                                            <div class="input-group mb-3">

                                                <select name="proveedor" id="proveedor" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
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
                                        <div class="col-md-1 align-self-center">
                                            <button type="submit" class="btn btn-success mt-3" name="btnBuscarProveedor" id="btnBuscarProveedor">Consultar</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-pyme-primary text-white">
                                <h4 class="card-title">Detalle Del Proveedor</h4>
                            </div>
                            <div id="tarjetaProveedor" class="card-body">

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
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 20px;">
            <div class="loading text-center"><img src="/assets/images/loading.gif" alt="loading" /><br />Actualizando Proveedores, Esto Puede Tardar Unos Minutos</div>

        </div>
    </div>

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

    <script src="/assets/libs/jquery/dist/jquery.min.js"></script>
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
    <script src="/assets/libs/block-ui/jquery.blockUI.js"></script>
    <script src="/assets/extra-libs/block-ui/block-ui.js"></script>
    <script src="/assets/libs/moment/moment.js"></script>
    <script src="/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
    <script src="/assets/libs/bootstrap-datepicker/dist/locales/bootstrap-datepicker.es.min.js"></script>

    <script src="/assets/extra-libs/datatables.net/js/jquery.dataTables.min-ESP.js"></script>
    <script src="/dist/js/pages/datatable/datatable-basic.init.js"></script>

    <script src="/assets/extra-libs/datatables.net/js/jszip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>

    <script src="/assets/extra-libs/datatables.net/js/dataTables.buttons.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.flash.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.html5.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/buttons.print.min.js"></script>
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/pdfmake.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>

    <?php include 'index_js.php'; ?>

</body>

</html>