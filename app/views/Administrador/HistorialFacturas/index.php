<?php
$debug = 0;

$requestUri = $_SERVER['REQUEST_URI'];
$cleanUri = parse_url($requestUri, PHP_URL_PATH);
$piezasURL = explode('/', trim($cleanUri, '/'));
$paginaLink = $piezasURL[1];
include '../app/views/Layout/funciones.php';

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
    echo '<br><br>Contenido de listaProveedores:';
    var_dump($listaProveedores['data']);
}

$fechaInicial = date("Y-m-01");
$fechaFinal = date("Y-m-t");

?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>
    <?php include '../app/views/Layout/header.php'; ?>
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

        #btnBuscaPagos {
            display: inline-block;
            padding: 8px 15px;
            border: 2px solid white;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
            color: white;
        }

        #btnBuscaPagos:hover {
            background-color: white;
            color: black;
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
        <?php include '../app/views/Layout/menu.php'; ?>
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
            <?php include '../app/views/Layout/title.php'; ?>
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
                            <div class="card-header bg-pyme-primary text-white">
                                <h4 class="card-title">Filtros De Búsqueda</h4>
                            </div>
                            <div class="card-body">
                                <form id="filtroHistorial">
                                    <div class="row">

                                        <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                                            <label class="col-form-label">Rango de Fechas</label>
                                            <div class="input-group input-daterange mb-3" id="date-range">
                                                <div class="input-group-addon">
                                                    <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> Desde </span>
                                                </div>
                                                <input type="date" class="form-control" name="fechaInicial" id="fechaInicial" value="<?= $fechaInicial; ?>" />
                                                <div class="input-group-addon">
                                                    <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> Hasta </span>
                                                </div>
                                                <input type="date" class="form-control " name="fechaFinal" id="fechaFinal" value="<?= $fechaFinal; ?>" />
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="idProveedor">Proveedores</label>
                                            <div class="input-group mb-3">
                                                <select name="idProveedor" id="idProveedor" class="select2 form-control custom-select" style="width: 100%;">
                                                    <option value="">Selecciona Un Proveedor</option>
                                                    <?php
                                                    foreach ($listaProveedores['data'] as $proveedor) {
                                                    ?>
                                                        <option value="<?= $proveedor['IdProveedor']; ?>"><?= $proveedor['IdProveedor']; ?> - <?= $proveedor['Proveedor']; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="nacionalidad">Nacionalidad</label>
                                            <div class="input-group mb-3">
                                                <select name="nacionalidad" id="nacionalidad" class="select2 form-control custom-select" style="width: 100%;">
                                                    <option value="">Todas</option>
                                                    <option value="MX">Nacionales</option>
                                                    <option value="XX">Extranjeras</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="estatusCont">Estatus</label>
                                            <div class="input-group mb-3">
                                                <select name="estatusCont" id="estatusCont" class="select2 form-control custom-select" style="width: 100%;">
                                                    <option value="">Todas</option>
                                                    <option value="1">Esperando Autorización</option>
                                                    <option value="2">Autorizada</option>
                                                    <option value="3">Rechazadas</option>
                                                    <option value="4">Borradas Por Usuario</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!--<div class="col-md-1">
                                            <label for="estatusComp">Complemento</label>
                                            <div class="input-group mb-3">
                                                <select name="estatusComp" id="estatusComp" class="select2 form-control custom-select" style="width: 100%;">
                                                    <option value="">Todos</option>
                                                    <option value="1">Pendiente</option>
                                                    <option value="2">Cargado Completo</option>
                                                    <option value="3">No Rquiere</option>
                                                </select>
                                            </div>
                                        </div>-->

                                        <div class="col-md-1 align-self-center">
                                            <button type="submit" class="btn btn-success mt-3" name="btnFiltros" id="btnFiltros">Consultar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header bg-pyme-primary text-white d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Historial De Facturas</h4>
                                <a href="#" id="btnBuscaPagos" onclick="$('#modalFechasPagos').modal('show');">Buscar Pagos</a>
                            </div>
                            <div id="tarjetaListaFacturas" class="card-body">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL PARA BUSCAR PAGOS EN SILME -->
                <div class="modal fade" id="modalFechasPagos" role="dialog" aria-labelledby="modalFechasPagosLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md modalFechasPagos" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-pyme-primary">
                                <h5 class="modal-title text-white" id="modalFechasPagosLabel">Seleccione Rango De Fechas</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalFechasPagos').modal('hide');">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="formRangoBuscaPago">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <label class="col-form-label">Rango de Fechas</label>
                                        <div class="input-group input-daterange mb-3" id="date-range">
                                            <div class="input-group-addon">
                                                <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> Desde </span>
                                            </div>
                                            <input type="text" class="form-control" name="fechaInicialBus" id="fechaInicialBus" value="<?= $fechaInicial; ?>" />
                                            <div class="input-group-addon">
                                                <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> Hasta </span>
                                            </div>
                                            <input type="text" class="form-control " name="fechaFinalBus" id="fechaFinalBus" value="<?= $fechaFinal; ?>" />
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <!--<div id="bloquear-btnCerrarModal" style="display:none;">
                                    <button class="btn btn-danger btn-md" type="button" disabled="" style="height: 100%;">
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div id="desbloquear-btnCerrarModal">
                                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalFechasPagos').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                                </div>-->

                                <div id="bloquear-btnActualizaPagos" style="display:none;">
                                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div id="desbloquear-btnActualizaPagos">
                                    <button form="formRangoBuscaPago" id="btnActualizaPagos" class="btn btn-md btn-outline-primary mx-2 mt-3">Actualizar</button>
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
            <?php include '../app/views/Layout/footer.php'; ?>
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
            <div class="loading text-center"><img src="/assets/images/loading.gif" alt="loading" /><br />Buscando Nuevos Pagos, Esto Puede Tardar Unos Minutos</div>

        </div>
    </div>
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
    <script src="/assets/scripts/basicFuctions.js"></script>
    <script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="/dist/js/pages/forms/select2/select2.init.js"></script>
    <script src="/assets/extra-libs/prism/prism.js"></script>

    <?php include 'index_js.php'; ?>
</body>

</html>