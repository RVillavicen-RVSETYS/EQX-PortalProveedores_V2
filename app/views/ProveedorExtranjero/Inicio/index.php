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

    <style>
        .custom-file-input~.custom-file-label::after {
            content: "Search";
            /* Cambia el texto "Browse" por "Buscar" */
        }
    </style>


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
            <!-- Inicia Contenido fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- Inicia Grupo de Tarjetas -->
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="card">
                            <h3 class="card-title m-t-10 m-l-15"><?= $_SESSION['EQXrazonSocial']; ?></h3>
                            <p class="card-text m-l-15"><b><?= $_SESSION['EQXrfc']; ?></b>.<br>
                                <?= $_SESSION['EQXcorreo']; ?></p>
                        </div>
                    </div>
                    <!--<div class="col-lg-3 col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <i class="fas fa-file-alt font-20 text-danger"></i>
                                        <p class="font-16 m-b-5"><?= $menuModel->txt('Complementos_Pendientes'); ?></p>
                                    </div>
                                    <div class="col-5">
                                        <h1 class="font-light text-right mb-0" id="complementosPendientes">
                                            <div class="loader">
                                                <span class="bar"></span>
                                                <span class="bar"></span>
                                                <span class="bar"></span>
                                            </div>
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>-->
                </div>

                <!-- Termina Grupo de Tarjetas -->
                <!-- ============================================================== -->

                <!-- Inicia Espacio para Notificaciones a Proveedores -->
                <div>
                    <?= $noti; ?>
                </div>
                <!-- Termina Espacio para Notificaciones a Proveedores -->
                <!-- ============================================================== -->

                <!-- Inicia Espacio para Tabla y Recibe Facturas -->
                <div class="row">
                    <!--Tarjeta Listado de Ultimas Facturas-->
                    <div class="col-md-8 col-lg-8">
                        <div class="card border">
                            <div class="card-header bg-pyme-primary">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="m-b-0 text-white"><?= $menuModel->txt('Ultimas_Facturas'); ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="cajaResultados" class="jsgrid" style="position: relative; height: auto; width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Tarjeta Carga de Facturas-->
                    <div class="col-md-4 col-lg-4">
                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
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
                                                    <form class="form" id="Form_CargaFactura" method="post" enctype="multipart/form-data" action="Inicio/registraNuevaFactura">
                                                        <div class="form-group row">
                                                            <span class="col-3 col-form-label"><b><?= $menuModel->txt('No_Proveedor'); ?></b></span>
                                                            <span class="col-3 col-form-label text-success"><b><?= $_SESSION['EQXnoProveedor']; ?></b></span>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="ordenCompra" class="col-3 col-form-label"><?= $menuModel->txt('OC'); ?></label>
                                                            <div class="col-9">
                                                                <input class="form-control" type="search" value="" id="ordenCompra" name="ordenCompra" maxlength="14" onchange="validaOrdCompra(this.value);" required>
                                                                <div class="invalid-feedback" id="invalid_ordenCompra">
                                                                    <img src="../assets/images/barLoadign.gif" alt="" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="listaHES" class="col-12 col-form-label"><?= $menuModel->txt('HES'); ?></label>
                                                            <div class="col-12">
                                                                <textarea class="form-control" id="listaHES" name="listaHES" rows="3" onblur="validaHojaEntrada(this.value);" required></textarea>
                                                                <div class="invalid-feedback" id="invalid_listaHES"></div>
                                                            </div>
                                                        </div>

                                                        <div id="contentNotaCredito">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="facturaPDF"><?= $menuModel->txt('Facturas'); ?></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="facturaPDF" name="facturaPDF" accept=".pdf" required>
                                                                    <label class="custom-file-label" for="facturaPDF"> Select PDF</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <hr>
                                                        <h4 class="card-title"><?= $menuModel->txt('Informacion_Factura'); ?></h4>
                                                        <div class="form-group row">
                                                            <div class="col-12">
                                                                <input class="form-control" type="text" id="uuidFactura" name="uuidFactura" placeholder="Enter Invoice ID" required>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-12">
                                                                <input class="form-control" type="date" id="fechaFactura" name="fechaFactura" placeholder="Enter Invoice Date" required>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-8">
                                                                <input class="form-control" type="number" id="totalFactura" name="totalFactura" placeholder="Amount" required>
                                                            </div>
                                                            <div class="col-4">
                                                                <input class="form-control" type="date" id="fechaFactura" name="fechaFactura" required>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6 text-right"></div>

                                                            <div id="desbloquear-btn1">
                                                                <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> <?= $menuModel->txt('Limpiar'); ?></button>
                                                                <button type="submit" class="btn btn-success waves-effect waves-light"><?= $menuModel->txt('Carga_Factura'); ?></button>
                                                            </div>
                                                            <div id="bloquear-btn1" style="display: none;">
                                                                <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
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

                        <!--<div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white"><?= $menuModel->txt('Carga_Factura_por_anticipo'); ?></h4>
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
                                                            <label for="codAnticipo" class="col-3 col-form-label"><?= $menuModel->txt('CodigoAnticipo'); ?></label>
                                                            <div class="col-9">
                                                                <input class="form-control" type="search" value="" id="codAnticipo" name="codAnticipo" maxlength="14" onchange="validaAnticipo(this.value);" required>
                                                                <div class="invalid-feedback" id="invalid_codAnticipo"></div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="anticipoPDF"><?= $menuModel->txt('Facturas'); ?></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                                </div>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="anticipoPDF" name="anticipoPDF" accept=".pdf" required>
                                                                    <label class="custom-file-label" for="anticipoPDF"> Select PDF.</label>
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
                        </div>-->
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
    <script src="/dist/js/basicFuctions.js"></script>
    <script src="/assets/libs/toastr/build/toastr.min.js"></script>
    <script src="/assets/libs/moment/moment.js"></script>
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
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/pdfmake.min.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            let validOC = false;
            let validHES = false;
            let reqAnticipo = false;

            $("#Form_CargaFactura").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                bloquearBtn('btn1');
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/registraNuevaFactura',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            resetFormulario("Form_CargaFactura");
                            notificaSuc(response.message); // Muestra el mensaje OK
                        } else {
                            notificaBad(response.message); // Muestra el mensaje de error
                        }
                        desbloquearBtn('btn1');
                    },
                    error: function() {
                        notificaBad('Error al querer cargar factura. Consulta a tu administrador');
                        desbloquearBtn('btn1');
                    },
                    complete: function() {
                        // Rehabilitar el botón
                        desbloquearBtn('btn1');

                        // Limpiar los campos Inputs
                        resetFormulario("Form_CargaFactura");
                        cargaTablaUltimasFacturas();
                    }
                });
            });

            // Traere datos iniciales
            cargarDatosIniciales();
            cargaTablaUltimasFacturas();
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

        function cargaTablaUltimasFacturas() {
            $('#cajaResultados').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
            $('#cajaResultados').load('Inicio/tablaUltimas50Facturas');
        }

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
                            validOC = true;
                            $("#ordenCompra").addClass("is-valid");

                            if (response.anticipo) {
                                $("#contentNotaCredito").html(response.solicitaNotaCredito);
                            }
                        } else {
                            validOC = false;
                            $("#ordenCompra").addClass("is-invalid");
                            $("#invalid_ordenCompra").html(response.message);
                        }
                    },
                    error: function() {
                        notificaBad('Error al validar la Orden de Compra. Consulta a tu administrador');
                        validOC = false;
                    }
                });
            } else {
                $("#ordenCompra").addClass("is-invalid");
                $("#invalid_ordenCompra").html("Structure: COM-XXX-######");
                validOC = false;
            }
        }

        function validaHojaEntrada(hojaEntrada) {
            $("#listaHES").removeClass("is-invalid is-valid");
            $("#invalid_listaHES").html("");

            if (hojaEntrada.length === 0) {
                $("#listaHES").addClass("is-invalid");
                $("#invalid_listaHES").html("<?= $menuModel->txt('No_Vacio', 1); ?>");
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
                            url: 'Inicio/validaHojaEntrada',
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
            $("#codAnticipo").removeClass("is-invalid is-valid");
            $("#invalid_codAnticipo").html("");

            ant = validarEstructuraANT(ordenCompra);
            console.log(validarEstructuraANT(ordenCompra));
            console.log(ant.valor);

            $("#codAnticipo").val(ant.valor);
            if (ant.valido) {
                $.ajax({
                    type: 'POST',
                    url: 'Inicio/validaCodigoAnticipo',
                    data: {
                        anticipo: ant.valor
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            validAnt = true;
                            $("#codAnticipo").addClass("is-valid");
                        } else {
                            validOC = false;
                            $("#codAnticipo").addClass("is-invalid");
                            $("#invalid_codAnticipo").html(response.message);
                        }
                    },
                    error: function() {
                        notificaBad('Problemas al consultar el Anticipo. Notifica a tu administrador');
                        validOC = false;
                    }
                });
            } else {
                $("#codAnticipo").addClass("is-invalid");
                $("#invalid_codAnticipo").html("Estructura: ANT-XXX-######");
                validOC = false;
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