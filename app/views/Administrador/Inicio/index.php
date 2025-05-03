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
    echo '<br><br>Request-URI: '.$_SERVER['REQUEST_URI'].'<br>Contenido de piezasURL:';
    var_dump($piezasURL);
    echo '<br><br>Ruta del MenuActual: '.$rutaMenu.'<br><br>Contenido de datosPagina:';
    var_dump($datosPagina);
    echo '<br><br>Valores pasados del Controller Data:';
    echo var_dump($data["datosIniciales"]);
}

$InsolutosPendientes = $data["datosIniciales"]['InsolutosPendientes'];
$ComplementosPendientes = $data["datosIniciales"]['ComplementosPendientes'];
$PendientesPorPagar = $data["datosIniciales"]['PendientesPorPagar'];
$PendientesPorProcesar = $data["datosIniciales"]['PendientesPorProcesar'];
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
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="fas fa-file font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">Facturas por Procesar</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?=$PendientesPorProcesar;?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 75%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="fas fa-dollar-sign font-20  text-muted"></i>
                                            <p class="font-16 m-b-5">Pendientes por Pagar</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?=$PendientesPorPagar;?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="fas fa-copy font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">Complementos Pendientes</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?= $ComplementosPendientes;?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-purple" role="progressbar" style="width: 65%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="fas fa-donate font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">Insolutos Pendientes</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right"><?= $InsolutosPendientes;?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 70%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-sm-12 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Estatus Facturas <?=date('Y');?></h4>
                                <div class="status m-t-30" style="height:300px; width:100%"></div>
                            
                                <div class="row">
                                    <div class="col-4 border-right">
                                        <i class="fa fa-circle text-primary"></i>
                                        <h4 class="mb-0 font-medium">5489</h4>
                                        <span>Success</span>
                                    </div>
                                    <div class="col-4 border-right p-l-20">
                                        <i class="fa fa-circle text-info"></i>
                                        <h4 class="mb-0 font-medium">954</h4>
                                        <span>Pending</span>
                                    </div>
                                    <div class="col-4 p-l-20">
                                        <i class="fa fa-circle text-success"></i>
                                        <h4 class="mb-0 font-medium">736</h4>
                                        <span>Failed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h4 class="card-title">Comparasión Anual</h4>
                                    </div>
                                    <div class="ml-auto">
                                        <div class="dl m-b-10">
                                            <select class="custom-select border-0 text-muted">
                                                <option value="0" selected="">2018</option>
                                                <option value="1">2015</option>
                                                <option value="2">2016</option>
                                                <option value="3">2017</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="chart1 m-t-40" style="position: relative; height:250px;"></div>
                                <ul class="list-inline m-t-30 text-center font-12">
                                    <li class="list-inline-item text-muted"><i class="fa fa-circle text-info m-r-5"></i> Pagado <br>$0000</li>
                                    <li class="list-inline-item text-muted"><i class="fa fa-circle text-light m-r-5"></i> Facturado <br> $0000</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Table -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body scrollable">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h4 class="card-title">Top 10 de Proveedores en Seguimiento.</h4>
                                    </div>
                                    <div class="ml-auto">
                                        <div class="dl m-b-10">
                                            <select class="custom-select border-0 text-muted" onchange="getProveedoresSeguimiento(this.value);">
                                                <option value="ComplementosMasViejos" selected="">Complementos mas viejos</option>
                                                <option value="MasComplementos">Cantidad de Complementos</option>
                                                <option value="InsolutosPendientes">Insolutos Pendientes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive" id="tablaProveedoresSeguimiento">
                                    <table class="table no-wrap v-middle">
                                        <thead>
                                            <tr>
                                                <th class="border-0 text-muted">Team Lead</th>
                                                <th class="border-0 text-muted">Project</th>
                                                <th class="border-0 text-muted">Team</th>
                                                <th class="border-0 text-muted text-center">Status</th>
                                                <th class="border-0 text-muted text-center">Weeks</th>
                                                <th class="border-0 text-muted text-center">Budget</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex no-block align-items-center">
                                                        <div class="m-r-10"><img src="../../assets/images/users/d1.jpg" alt="user" class="rounded-circle" width="45" /></div>
                                                        <div class="">
                                                            <h5 class="m-b-0 font-16 font-medium">Hanna Gover</h5><span class="text-muted">hgover@gmail.com</span></div>
                                                    </div>
                                                </td>
                                                <td>Elite Admin</td>
                                                <td>
                                                    <div class="popover-icon">
                                                        <a class="btn-circle btn btn-info" href="javascript:void(0)">SS</a>
                                                        <a class="btn-circle btn btn-purple text-white popover-item" href="javascript:void(0)">DS</a>
                                                        <a class="btn-circle btn btn-danger text-white popover-item" href="javascript:void(0)">RP</a>
                                                        <a class="btn-circle btn btn-outline-secondary" href="javascript:void(0)">+</a>
                                                    </div>
                                                </td>
                                                <td class="text-center"><i class="fa fa-circle text-danger" data-toggle="tooltip" data-placement="top" title="In Progress"></i></td>
                                                <td class="text-center">35</td>
                                                <td class="font-medium text-center">$96K</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex no-block align-items-center">
                                                        <div class="m-r-10"><img src="../../assets/images/users/d2.jpg" alt="user" class="rounded-circle" width="45" /></div>
                                                        <div class="">
                                                            <h5 class="m-b-0 font-16 font-medium">Daniel Kristeen</h5><span class="text-muted">Kristeen@gmail.com</span></div>
                                                    </div>
                                                </td>
                                                <td>Real Homes WP Theme</td>
                                                <td>
                                                    <div class="popover-icon">
                                                        <a class="btn-circle btn btn-info" href="javascript:void(0)">SS</a>
                                                        <a class="btn-circle btn btn-primary text-white popover-item" href="javascript:void(0)">DS</a>
                                                        <a class="btn-circle btn btn-outline-secondary" href="javascript:void(0)">+</a>
                                                    </div>
                                                </td>
                                                <td class="text-center"><i class="fa fa-circle text-success" data-toggle="tooltip" data-placement="top" title="Active"></i></td>
                                                <td class="text-center">35</td>
                                                <td class="font-medium text-center">$96K</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex no-block align-items-center">
                                                        <div class="m-r-10"><img src="../../assets/images/users/d3.jpg" alt="user" class="rounded-circle" width="45" /></div>
                                                        <div class="">
                                                            <h5 class="m-b-0 font-16 font-medium">Julian Josephs</h5><span class="text-muted">Josephs@gmail.com</span></div>
                                                    </div>
                                                </td>
                                                <td>MedicalPro WP Theme</td>
                                                <td>
                                                    <div class="popover-icon">
                                                        <a class="btn-circle btn btn-info" href="javascript:void(0)">SS</a>
                                                        <a class="btn-circle btn btn-purple text-white popover-item" href="javascript:void(0)">DS</a>
                                                        <a class="btn-circle btn btn-danger text-white popover-item" href="javascript:void(0)">RP</a>
                                                        <a class="btn-circle btn btn-outline-secondary" href="javascript:void(0)">+</a>
                                                    </div>
                                                </td>
                                                <td class="text-center"><i class="fa fa-circle text-success" data-toggle="tooltip" data-placement="top" title="Active"></i></td>
                                                <td class="text-center">35</td>
                                                <td class="font-medium text-center">$96K</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex no-block align-items-center">
                                                        <div class="m-r-10"><img src="../../assets/images/users/2.jpg" alt="user" class="rounded-circle" width="45" /></div>
                                                        <div class="">
                                                            <h5 class="m-b-0 font-16 font-medium">Jan Petrovic</h5><span class="text-muted">hgover@gmail.com</span></div>
                                                    </div>
                                                </td>
                                                <td>Hosting Press HTML</td>
                                                <td>
                                                    <div class="popover-icon">
                                                        <a class="btn-circle btn btn-purple text-white" href="javascript:void(0)">RP</a>
                                                        <a class="btn-circle btn btn-outline-secondary" href="javascript:void(0)">+</a>
                                                    </div>
                                                </td>
                                                <td class="text-center"><i class="fa fa-circle text-info" data-toggle="tooltip" data-placement="top" title="Pending"></i></td>
                                                <td class="text-center">35</td>
                                                <td class="font-medium text-center">$96K</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Table -->
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
    <!--This page JavaScript -->
    <!--chartis chart-->
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <!--c3 charts -->
    <script src="/assets/extra-libs/c3/d3.min.js"></script>
    <script src="/assets/extra-libs/c3/c3.min.js"></script>
    <script src="/assets/extra-libs/jvector/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="/assets/extra-libs/jvector/jquery-jvectormap-world-mill-en.js"></script>
    <script src="/dist/js/pages/dashboards/dashboard1.js"></script>
</body>
<script>
    $(document).ready(function() {
        getProveedoresSeguimiento('ComplementosMasViejos'); // Cargar la tabla al inicio con el primer tipo de seguimiento
    });

    function getProveedoresSeguimiento($tipoSeguimiento) {
        $.ajax({
            url: 'Inicio/tablaProveedoresSeguimiento',
            type: 'POST',
            data: { tipoSeguimiento: $tipoSeguimiento },
            success: function(response) {
                // Manejar la respuesta del servidor
                $('#tablaProveedoresSeguimiento').html(response);
            },
            error: function(xhr, status, error) {
                // Manejar el error
                console.error(error);
            }
        });
    }
</script>
</html>

       