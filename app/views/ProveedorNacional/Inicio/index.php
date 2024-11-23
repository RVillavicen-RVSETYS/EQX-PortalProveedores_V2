<?php
$debug = 0;

$requestUri = $_SERVER['REQUEST_URI'];
$cleanUri = parse_url($requestUri, PHP_URL_PATH);
$piezasURL = explode('/', trim($cleanUri, '/'));
$paginaLink = $piezasURL[1];
$urlIdioma = '../app/views/Layout/Idiomas/' . $piezasURL[0] . '/' . $_SESSION['EQXidioma'] . '.php';
require_once($urlIdioma);
$menuModel = new Idiomas($piezasURL[1]);
include '../app/views/Layout/funciones.php';

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
    <?php include '../app/views/Layout/header.php'; ?>
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
        <?php include '../app/views/Layout/menu.php'; ?>
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
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <i class="mdi mdi-emoticon font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">New Clients</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right">23</h1>
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
                                            <i class="mdi mdi-image font-20  text-muted"></i>
                                            <p class="font-16 m-b-5">New Projects</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right">169</h1>
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
                                            <i class="mdi mdi-currency-eur font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">New Invoices</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right">157</h1>
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
                                            <i class="mdi mdi-poll font-20 text-muted"></i>
                                            <p class="font-16 m-b-5">New Sales</p>
                                        </div>
                                        <div class="ml-auto">
                                            <h1 class="font-light text-right">236</h1>
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
                <!-- Termina Grupo de Tarjetas -->
                <!-- ============================================================== -->

                <!-- Inicia Espacio para Notificaciones a Proveedores -->
                <div class="card-group">
                    <?= $noti; ?>
                </div>
                <!-- Termina Espacio para Notificaciones a Proveedores -->
                <!-- ============================================================== -->

                <!-- Inicia Espacio para Tabla y Recibe Facturas -->
                <div class="row">
                    <!--Tarjeta Listado de Ultimas Facturas-->
                    <div class="col-md-8 col-lg-8">
                        <div class="card border">
                            <div class="card-header bg-Equinoxgold">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="m-b-0 text-white"><?= $menuModel->txt('Ultimas_Facturas'); ?></h4>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body">


                                <div id="validation" class="m-t-40 jsgrid" style="position: relative; height: auto; width: 100%;">
                                    <div class="table-responsive" id="divAspectos"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Tarjeta Carga de Facturas-->
                    <div class="col-md-4 col-lg-4">
                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-Equinoxgold">
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
                                                    <form class="form">
                                                        <div class="form-group row">
                                                            <span class="col-3 col-form-label"><b><?= $menuModel->txt('No_Proveedor'); ?></b></span>
                                                            <span class="col-3 col-form-label text-success"><b><?= $_SESSION['EQXnoProveedor']; ?></b></span>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="ordenCompra" class="col-3 col-form-label"><?= $menuModel->txt('OC'); ?></label>
                                                            <div class="col-9">
                                                                <input class="form-control" type="search" value="" id="ordenCompra" maxlength="14" onchange="validaOrdCompra(this.value);" required>
                                                                <div class="invalid-feedback" id="invalid_ordenCompra"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="listaHES" class="col-12 col-form-label" required><?= $menuModel->txt('HES'); ?></label>
                                                            <div class="col-12">
                                                                <textarea class="form-control" id="listaHES" rows="3"></textarea>
                                                                <div class="invalid-feedback" id="invalid_listaHES"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Facturas</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf"></i> PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="facturaPDF" required>
                                                                    <label class="custom-file-label" for="facturaPDF">Elegir PDF de Factura..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-code"></i> XML</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="facturaXML" required>
                                                                    <label class="custom-file-label" for="facturaXML">Elegir XML de Factura..</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6 text-right"></div>

                                                            <div id="desbloquear-btn1">
                                                                <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> <?= $menuModel->txt('Limpiar'); ?></button>

                                                                <button type="submit" class="btn btn-success waves-effect waves-light"><?= $menuModel->txt('Carga_Factura'); ?></button>
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
                                    <div class="card-header bg-Equinoxgold">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white"><?= $menuModel->txt('Carga_Factura_por_anticipo'); ?></h4>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <form id="formAddAspecto">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="descripcion">Aspecto:</label>
                                                    <textarea class="form-control mayusculas" name="descripcion" id="descripcion" rows="5"></textarea>

                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>
                                                        <div class="col-md-6 text-right">

                                                            <div id="bloquear-btn1" style="display:none;">
                                                                <button class="btn btn-<?= $pyme ?> float-left" type="button" disabled>

                                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                                    <span class="sr-only">Loading...</span>
                                                                </button>
                                                                <button class="btn btn-<?= $pyme ?> float-left" type="button" disabled>
                                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                                    <span class="sr-only">Loading...</span>
                                                                </button>
                                                                <button class="btn btn-<?= $pyme ?> float-left" type="button" disabled>
                                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                                    Loading...
                                                                </button>
                                                            </div>
                                                            <div id="desbloquear-btn1">
                                                                <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> Limpiar</button>

                                                                <button type="submit" class="btn btn-success waves-effect waves-light">Guardar</button>
                                                            </div>
                                                        </div>

                                                    </div>

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
            <!-- Termina Espacio para Tabla y Recibe Facturas -->
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
    <aside class="customizer">
        <a href="javascript:void(0)" class="service-panel-toggle">
            <i class="fa fa-spin fa-cog"></i>
        </a>
    </aside>
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
    <script src="/assets/scripts/basicFuctions.js"></script>
    <!--This page JavaScript -->
    <!--chartis chart-->
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script>
        $(document).ready(function() {});

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
                    url: 'Inicio/validaOrdenCompra',
                    data: {
                        ordenCompra: oc.valor
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $("#ordenCompra").addClass("is-valid");
                        } else {
                            $("#ordenCompra").addClass("is-invalid");
                            $("#invalid_ordenCompra").html(response.message);
                        }
                    },
                    error: function() {
                        $('#loginMessage').html('Error al validar la Orden de Compra. Consulta a tu administrador');
                    }
                });
            } else {
                $("#ordenCompra").addClass("is-invalid");
                $("#invalid_ordenCompra").html("Estructura: COM-XXX-######");
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
    </script>

</body>

</html>