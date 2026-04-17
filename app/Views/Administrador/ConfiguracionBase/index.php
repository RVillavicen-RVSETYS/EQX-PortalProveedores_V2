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
    echo '<br><br>Contenido de tiposMoneda:';
    var_dump($tiposMoneda);
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
                                <h4 class="card-title">Configuración General</h4>
                            </div>
                            <!--  Aqui va el contenido nuevo  -->
                            <div id="configuraciones" class="card-body">

                            <!-- Pestañas de -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#Home" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Configuración de Precios</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Profile</span></a> </li>
                                <!--
                                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#messages" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down">Messages</span></a> </li>
                                -->
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content tabcontent-border">
                                <!-- Primer pestaña Configuracion de precios de Parral -->
                                <div class="tab-pane active" id="Home" role="tabpanel">
                                    <div class="p-20">
                                        <h4 class="card-title mb-4"><i class="fas fa-building mr-2"></i> Configuración de Diferencia de Montos</h4>
                                        <p class="text-muted">Define los umbrales de tolerancia permitidos entre el XML y la Hoja de Entrada (HES).</p>
                                        <hr>
                                        <form id="formConfiguracionPrecios">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="idEmpresa">Empresa:</label>
                                                        <select required name="idEmpresa" id="idEmpresa" class="select2 form-control custom-select" style="width: 100%;">
                                                            <option value="">Selecciona una Empresa</option>
                                                            <!-- Aquí iterarás tus empresas -->
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="idMoneda">Tipo de Moneda:</label>
                                                        <select required name="idMoneda" id="idMoneda" class="select2 form-control custom-select" style="width: 100%;">
                                                            <option value="">Selecciona una Moneda</option>
                                                            <?php

                                                            foreach ($tiposMoneda['data'] as $moneda) {
                                                                echo '<option value="' . $moneda['id'] . '">' . $moneda['descripcion'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="tipoRegla">Tipo de Aplicación de Regla:</label>
                                                        <select required name="tipoRegla" id="tipoRegla" class="select2 form-control custom-select" style="width: 100%;" onchange="gestionarInputsTolerancia()">
                                                            <option value="" selected disabled>Elige la Aplicación de la Regla</option>
                                                            <option value="1">Solo aplica por Monto</option>
                                                            <option value="2">Solo aplica por Porcentaje</option>
                                                            <option value="3">Aplica Monto y Porcentaje</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <!-- Contenedor para Monto -->
                                                <div class="col-md-6" id="containerMonto" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="montoTolerancia">Monto de Tolerancia (±):</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                            <input type="number" step="0.01" class="form-control" name="montoTolerancia" id="montoTolerancia" placeholder="0.00">
                                                        </div>
                                                        <small class="text-muted">Este valor se aplicará como límite superior e inferior.</small>
                                                    </div>
                                                </div>
                                                <!-- Contenedor para Porcentaje -->
                                                <div class="col-md-6" id="containerPorcentaje" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="porcentajeTolerancia">Porcentaje de Tolerancia (±):</label>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" class="form-control" name="porcentajeTolerancia" id="porcentajeTolerancia" placeholder="0.00">
                                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                        </div>
                                                        <small class="text-muted">Este porcentaje se aplicará como límite superior e inferior.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-4">
                                                <div class="col-12 text-right">
                                                    <div id="bloquear-btnGuardarConfig" style="display:none;">
                                                        <button class="btn btn-primary btn-md" type="button" disabled>
                                                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> Guardando...
                                                        </button>
                                                    </div>
                                                    <div id="desbloquear-btnGuardarConfig">
                                                        <button type="submit" class="btn btn-md btn-outline-primary"><i class="fas fa-save mr-1"></i> Guardar Configuración</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- Segunda pestaña del Barcenas Peña-->
                                <div class="tab-pane  p-20" id="profile" role="tabpanel">2</div>
                                <!--
                                <div class="tab-pane p-20" id="messages" role="tabpanel">3</div>
                                -->
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
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/pdfmake.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>

    <?php include 'index_js.php'; ?>

</body>

</html>