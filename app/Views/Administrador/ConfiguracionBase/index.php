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
    echo '<br><br>Contenido de listaEmpresas <br><br>';
    var_dump($listaEmpresas);
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
                                <h4 class="card-title">Configuración Base</h4>
                            </div>
                            <div id="configuraciones" class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Home</span></a> </li>
                                    <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#proveedores" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Proveedores</span></a> </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content tabcontent-border">
                                    <div class="tab-pane active" id="home" role="tabpanel">
                                        <div class="p-20">
                                            <h3>Best Clean Tab ever</h3>
                                            <h4>you can use it with the small code</h4>
                                            <p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a.</p>
                                        </div>
                                    </div>
                                    <div class="tab-pane  p-20" id="proveedores" role="tabpanel">
                                        <form class="form-horizontal" id="formConfiguracionGral" method="post" action="ConfiguracionBase/guardarConfiguracionGral">
                                            <div class="card-body">
                                                <h4 class="card-title">Empresas</h4>
                                                <div class="row">
                                                    <div class="col-sm-12 col-lg-6">
                                                        <div class="form-group">
                                                            <label for="empresa" class="control-label col-form-label">Empresas:</label>
                                                            <div class="input-group mb-3" data-select2-id="8">
                                                                <select name="idEmpresa" id="empresa" class="select2 form-control custom-select select2-hidden-accessible" style="width: 100%;" data-select2-id="empresas" tabindex="-1" aria-hidden="true">
                                                                 <option selected="" value="">Selecione una Empresa</option>
                                                                 <?php
                                                                 foreach ($listaEmpresas['data'] as $empresa) {
                                                                     echo '<option value="' . $empresa['id'] . '">' . $empresa['nombre'] . '</option>';
                                                                 }
                                                                 ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-lg-6">
                                                        <div class="form-group">
                                                            <label for="cantComplemento" class="control-label col-form-label">Limite de Complementos:</label>
                                                            <input type="number" name="maxComplementosPendientes" class="form-control" id="cantComplemento" placeholder="Cant. Maxima">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="card-body">
                                                <h4 class="card-title">General</h4>
                                                <div class="row">
                                                    <div class="col-sm-12 col-lg-6">
                                                        <div class="form-group row">
                                                            
                                                                <div class="card-body">
                                                                        <h4 class="card-title">Seleccione los días de Pago</h4>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="1" class="custom-control-input" id="lunes">
                                                                                <label class="custom-control-label" for="lunes">Lunes</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="2" class="custom-control-input" id="martes">
                                                                                <label class="custom-control-label" for="martes">Martes</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="3" class="custom-control-input" id="miercoles">
                                                                                <label class="custom-control-label" for="miercoles">Miércoles</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="4" class="custom-control-input" id="jueves">
                                                                                <label class="custom-control-label" for="jueves">Jueves</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="5" class="custom-control-input" id="viernes">
                                                                                <label class="custom-control-label" for="viernes">Viernes</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="6" class="custom-control-input" id="sabado">
                                                                                <label class="custom-control-label" for="sabado">Sábado</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox" name="diasPago[]" value="7" class="custom-control-input" id="domingo">
                                                                                <label class="custom-control-label" for="domingo">Domingo</label>
                                                                            </div>
                                                                        </div>
                                                                    
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <hr>
                                            <div class="card-body">
                                                <div class="form-group m-b-0 text-right">
                                                    <button type="submit" id="btnGuardar" class="btn waves-effect waves-light btn-success">Guardar</button>
                                                    <!-- <button type="submit" class="btn btn-dark waves-effect waves-light">Cancelar</button> -->  
                                                </div>
                                            </div>
                                        </form>
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
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.all.min.js"></script>
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