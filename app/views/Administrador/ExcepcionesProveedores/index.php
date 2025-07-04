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
}

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

    <style>
        .tooltip-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .tooltip-text {
            visibility: hidden;
            width: 220px;
            background: linear-gradient(135deg, #f2e9e6, rgb(244, 244, 244));
            color: #fff;
            text-align: left;
            border-radius: 8px;
            padding: 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        .tooltip-text strong {
            display: block;
            font-size: 14px;
            color: rgb(0, 0, 0);
            /* Amarillo dorado para destacar el título */
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.3);
            /* Línea divisoria */
        }

        .tooltip-text p {
            font-size: 13px;
            margin: 0;
            line-height: 1.4;
            color: rgb(0, 0, 0);
            /* Cambia este color */
        }

        /* Flecha del tooltip */
        .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 8px;
            border-style: solid;
            border-color: #222 transparent transparent transparent;
        }

        .tooltip-container:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
            transform: translateX(-50%) translateY(-5px);
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
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header bg-pyme-primary text-white">
                                <h4 class="card-title">Tipos De Excepciones</h4>
                            </div>
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs customtab" role="tablist">

                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#ignoraDesc" role="tab" onclick="cargarIgnoraDescuento();">
                                            <span class="hidden-xs-down">Ignorar Descuento</span>
                                            <span class="hidden-sm-up">
                                                <div class="tooltip-container">
                                                    <span><i class="fas fa-info-circle font-12"></i></span>
                                                    <div class="tooltip-text">
                                                        <strong>Ignorar Descuento</strong>
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#añoFisc" role="tab" onclick="cargarExento();">
                                            <span class="hidden-xs-down">Exento Año Fiscal</span>
                                            <span class="hidden-sm-up">
                                                <div class="tooltip-container">
                                                    <span><i class="fas fa-info-circle font-12"></i></span>
                                                    <div class="tooltip-text">
                                                        <strong>Exento Año Fiscal</strong>
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#fechaEm" role="tab" onclick="cargarFechaEmision();">
                                            <span class="hidden-xs-down">Exento Tiempo Emisión</span>
                                            <span class="hidden-sm-up">
                                                <div class="tooltip-container">
                                                    <span><i class="fas fa-info-circle font-12"></i></span>
                                                    <div class="tooltip-text">
                                                        <strong>Exento Tiempo Emisión</strong>
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#usoCFDI" role="tab" onclick="cargarCfdiDistinto();">
                                            <span class="hidden-xs-down">Uso CFDI Distinto</span>
                                            <span class="hidden-sm-up">
                                                <div class="tooltip-container">
                                                    <span><i class="fas fa-info-circle font-12"></i></span>
                                                    <div class="tooltip-text">
                                                        <strong>Uso CFDI Distinto</strong>
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#bloqueoDif" role="tab" onclick="cargarBloqueoDiferencias();">
                                            <span class="hidden-xs-down">Bloquear Diferencias</span>
                                            <span class="hidden-sm-up">
                                                <div class="tooltip-container">
                                                    <span><i class="fas fa-info-circle font-12"></i></span>
                                                    <div class="tooltip-text">
                                                        <strong>Bloquear Diferencias</strong>
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div class="tab-pane p-20 active" id="ignoraDesc" role="tabpanel"></div>
                                    <div class="tab-pane p-20" id="añoFisc" role="tabpanel"></div>
                                    <div class="tab-pane p-20" id="fechaEm" role="tabpanel"></div>
                                    <div class="tab-pane p-20" id="usoCFDI" role="tabpanel"></div>
                                    <div class="tab-pane p-20" id="bloqueoDif" role="tabpanel"></div>
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

    <div class="chat-windows"></div>
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
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