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

                    <div class="col-md-8 col-lg-8">
                        <div class="card border">
                            <div class="card-header bg-pyme-primary">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 class="m-b-0 text-white">Facturas</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="cajaResultados" class="jsgrid" style="position: relative; height: auto; width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-4">
                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white">Carga Factura</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form" id="Form_CargaFactura" method="post" enctype="multipart/form-data" action="CargarFacturas/registraNuevaFactura">

                                                    <div class="form-group row">
                                                        <label for="noProveedor" class="col-3 col-form-label">Proveedor</label>
                                                        <div class="col-9 input-group mb-3">
                                                            <select name="noProveedor" id="noProveedor" class="select2 form-control custom-select" style="width: 100%;height: 36px;" required>
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

                                                    <div class="form-group row">
                                                        <label for="ordenCompra" class="col-3 col-form-label">Orden Compra</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="ordenCompra" name="ordenCompra" maxlength="14" onchange="validaOrdCompra(this.value,'FACT');" required>
                                                            <div class="invalid-feedback" id="invalid_ordenCompra">
                                                                <img src="../assets/images/barLoadign.gif" alt="" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="listaHES" class="col-12 col-form-label">Numeros Recepción</label>
                                                        <div class="col-12">
                                                            <textarea class="form-control" id="listaHES" name="listaHES" rows="3" onblur="validaHojaEntrada(this.value);" required></textarea>
                                                            <div class="invalid-feedback" id="invalid_listaHES"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="facturaPDF">Facturas</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="facturaPDF" name="facturaPDF" accept=".pdf" required>
                                                                <label class="custom-file-label" for="facturaPDF"> Elegir PDF de Factura..</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="far fa-file-code text-info"></i> &nbsp;XML</span>
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="facturaXML" name="facturaXML" accept=".xml" required>
                                                                <label class="custom-file-label" for="facturaXML">Elegir XML de Factura..</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="contentNotaCredito">

                                                    </div>
                                                    <div class="justify-content-end d-none" id="btnNotaCredito">
                                                        <button type="button" class="btn btn-success mt-2" onclick="cargarFormNotaCredito(lastNotasCredito, 'FACT')"><i class="fas fa-plus"></i> Nota de Crédito</button>
                                                    </div>

                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>

                                                        <div id="desbloquear-btn1">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> Limpiar</button>
                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Factura</button>
                                                        </div>
                                                        <div id="bloquear-btn1" style="display: none;">
                                                            <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white">Carga Factura por Anticipo</h4>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form">
                                                    <div class="form-group row">
                                                        <label for="codAnticipo" class="col-3 col-form-label">Código Anticipo</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="codAnticipo" name="codAnticipo" maxlength="14" onchange="validaAnticipo(this.value);" required>
                                                            <div class="invalid-feedback" id="invalid_codAnticipo"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="anticipoPDF">Facturas</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="far fa-file-pdf text-danger"> </i> &nbsp;PDF</span>
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="anticipoPDF" name="anticipoPDF" accept=".pdf" required>
                                                                <label class="custom-file-label" for="anticipoPDF"> Elegir PDF de Factura..</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="far fa-file-code text-info"></i> &nbsp;XML</span>
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="anticipoXML" name="anticipoXML" accept=".xml" required>
                                                                <label class="custom-file-label" for="anticipoXML">Elegir XML de Factura..</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>

                                                        <div id="desbloquear-btn1">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i>Limpiar</button>

                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Factura</button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div class="card border">
                                    <div class="card-header bg-pyme-primary">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <h4 class="m-b-0 text-white">Carga Notas De Crédito</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <form class="form" id="Form_CargaNotaCredito" method="post" enctype="multipart/form-data" action="CargarFacturas/registraNotaCredito">

                                                    <div class="form-group row">
                                                        <label for="noProveedorNC" class="col-3 col-form-label">Proveedor</label>
                                                        <div class="col-9 input-group mb-3">
                                                            <select name="noProveedorNC" id="noProveedorNC" class="select2 form-control custom-select" style="width: 100%;height: 36px;" required>
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

                                                    <div class="form-group row">
                                                        <label for="ordenCompraNC" class="col-3 col-form-label">Orden Compra</label>
                                                        <div class="col-9">
                                                            <input class="form-control" type="search" value="" id="ordenCompraNC" name="ordenCompraNC" maxlength="14" onchange="validaOrdCompra(this.value,'NC');" required>
                                                            <div class="invalid-feedback" id="invalid_ordenCompraNC">
                                                                <img src="../assets/images/barLoadign.gif" alt="" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="contentNotaCreditoNC">

                                                    </div>
                                                    <div class="justify-content-end d-none" id="btnNotaCreditoNC">
                                                        <button type="button" class="btn btn-success mt-2" onclick="cargarFormNotaCredito(lastNotasCredito,'NC')"><i class="fas fa-plus"></i> Nota de Crédito</button>
                                                    </div>

                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6 text-right"></div>

                                                        <div id="desbloquear-btn1">
                                                            <button type="reset" class="btn btn-danger waves-effect" onclick="resetForm()"><i class="far fa-trash-alt text-white"></i> Limpiar</button>
                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Carga Factura</button>
                                                        </div>
                                                        <div id="bloquear-btn1" style="display: none;">
                                                            <div class="loading text-center"><img src="../assets/images/loadingHorizontal.gif" alt="loading..." /></div>
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
    <script src="/assets/libs/moment/moment.js"></script>
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.js"></script>
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.all.js"></script>
    <script src="/assets/extra-libs/datatables.net/js/jquery.dataTables.min-ESP.js"></script>
    <script src="/dist/js/pages/datatable/datatable-basic.init.js"></script>

    <script src="/assets/extra-libs/datatables.net/js/jszip.min.js"></script>

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
    <script src="/assets/libs/sweetalert2/sweet-alert.init.js"></script>
    <script src="/dist/js/basicFuctions.js"></script>

    <script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="/dist/js/pages/forms/select2/select2.init.js"></script>

    <script>
        let lastNotasCredito = [];

        function validaOrdCompra(ordenCompra, tipo) {
            const isFactura = tipo === 'FACT';
            const prefix = isFactura ? '' : 'NC';

            const noProveedor = $(`#noProveedor${prefix}`).val();
            if (!noProveedor) {
                notificaBad('Selecciona un proveedor');
            }

            const $inputOC = $(`#ordenCompra${prefix}`);
            const $invalidMsg = $(`#invalid_ordenCompra${prefix}`);
            const $contentNota = $(`#contentNotaCredito${prefix}`);
            const $btnNota = $(`#btnNotaCredito${prefix}`);

            $inputOC.removeClass("is-invalid is-valid");
            $invalidMsg.html("");

            const oc = validarEstructuraOC(ordenCompra);
            console.log(validarEstructuraOC(ordenCompra));
            console.log(oc.valor);

            $inputOC.val(oc.valor);

            if (oc.valido) {
                $.ajax({
                    type: 'POST',
                    url: 'CargarFacturas/validaOrdenCompra',
                    data: {
                        ordenCompra: oc.valor,
                        noProveedor: noProveedor
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            validOC = true;
                            $inputOC.addClass("is-valid");
                            $contentNota.empty();
                            $btnNota.addClass("d-none");
                            contadorFormNotas = 0;
                            if (response.anticipo) {
                                lastNotasCredito = response.NC;
                                cargarFormNotaCredito(lastNotasCredito, tipo);
                                $btnNota.removeClass("d-none");
                            }
                        } else {
                            validOC = false;
                            $inputOC.addClass("is-invalid");
                            $invalidMsg.html(response.message);
                        }
                    },
                    error: function() {
                        notificaBad('Error al validar la Orden de Compra. Consulta a tu administrador');
                        validOC = false;
                    }
                });
            } else {
                $inputOC.addClass("is-invalid");
                $invalidMsg.html("Estructura: COM-XXX-######");
                validOC = false;
            }
        }

        let contadorFormNotas = 0;

        function cargarFormNotaCredito(arrayNotasCredito, tipo) {
            contadorFormNotas++; // Incrementa el contador para el nuevo bloque de formulario

            // URL y contenedor de destino para la solicitud AJAX
            const ajaxUrl = 'CargarFacturas/cargaFormNotaCredito';
            let targetContentId = '';
            let targetButtonId = '';

            switch (tipo) {
                case 'FACT':
                    targetContentId = '#contentNotaCredito';
                    targetButtonId = '#btnNotaCredito';
                    break;
                case 'NC':
                    targetContentId = '#contentNotaCreditoNC';
                    targetButtonId = '#btnNotaCreditoNC';
                    break;
                default:
                    console.error('Tipo de documento no válido:', tipo);
                    return; // Sale de la función si el tipo no es reconocido
            }

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: {
                    id: contadorFormNotas,
                    // Enviar el array de notas de crédito al servidor, si el PHP lo necesita
                    // Aunque para esta funcionalidad, el lado del cliente tiene la lista completa.
                    arrayNotasCredito: arrayNotasCredito
                },
                success: function(response) {
                    // Reemplaza el placeholder {{id}} en el HTML recibido con el contador único
                    const formHtml = response.replace(/{{id}}/g, contadorFormNotas);
                    // Añade el nuevo bloque de formulario al contenedor correspondiente
                    $(targetContentId).append(formHtml);

                    // Obtiene la referencia al elemento <select> que acaba de ser añadido
                    const $select = $(`#notaCredito_${contadorFormNotas}`);

                    // Itera sobre el array de notas de crédito para poblar el select
                    arrayNotasCredito.forEach(nc => {
                        // Solo añade la opción si NO ha sido seleccionada ya en otro select
                        if (!selectedNoteCreditIds.has(nc.IdNotaCredito)) {
                            const esObligatoria = nc.Obligatoria == "1";
                            const optionText = esObligatoria ? `🔴 ${nc.Descripcion}` : nc.Descripcion;
                            const option = new Option(optionText, nc.IdNotaCredito, false, false);
                            $(option).attr('data-obligatoria', nc.Obligatoria);
                            $select.append(option);
                        }
                    });

                    // Inicializa select2 en el nuevo select (ya con las opciones filtradas)
                    $select.select2();

                    // Muestra el botón de guardar si estaba oculto
                    $(targetButtonId).removeClass('d-none');
                },
                error: function() {
                    // Manejo de errores en caso de que la carga del formulario falle
                    $(targetContentId).append('<div class="text-danger">Error cargando formulario de Nota de Crédito.</div>');
                }
            });
        }

        const selectedNoteCreditIds = new Map();

        // Al seleccionar una opción
        $(document).on('select2:select', '.formulario-nota .select2', function(e) {
            const selectedId = e.params.data.id;
            const selectedText = e.params.data.text;

            selectedNoteCreditIds.set(selectedId, selectedText);

            $('.formulario-nota .select2').not(this).each(function() {
                $(this).find(`option[value="${selectedId}"]`).prop('disabled', true);
            });

            // Actualizar UI
            $('.formulario-nota .select2').select2();
        });

        // Al deseleccionar una opción
        $(document).on('select2:unselect', '.formulario-nota .select2', function(e) {
            const deselectedId = e.params.data.id;

            selectedNoteCreditIds.delete(deselectedId);

            $('.formulario-nota .select2').not(this).each(function() {
                $(this).find(`option[value="${deselectedId}"]`).prop('disabled', false);
            });

            // Actualizar UI
            $('.formulario-nota .select2').select2();
        });

        $(document).on('change', '.custom-file-input', function() {
            let fileName = $(this).val().split('\\').pop(); // obtiene el nombre del archivo
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        $(document).on('click', '.btn-eliminar-nota', function() {
            // Verifica cuántos bloques hay antes de eliminar
            const totalBloques = $('.formulario-nota').length;

            const $bloque = $(this).closest('.formulario-nota');
            const $selectInBlock = $bloque.find('.select2');

            if (totalBloques <= 1) {

                let contieneObligatorias = false;

                $selectInBlock.find('option').each(function() {
                    if ($(this).data('obligatoria') == "1") {
                        contieneObligatorias = true;
                        return false; // break
                    }
                });

                // Evitar borrar el último bloque
                if (contieneObligatorias == true) {
                    Swal.fire({
                        type: 'warning',
                        title: 'No puedes eliminar esta Nota de Crédito',
                        text: 'Contiene al menos una nota de crédito obligatoria.',
                        confirmButtonText: 'Entendido'
                    });
                    return; // No se elimina
                }

                if (contieneObligatorias == false) {

                    Swal.fire({
                        title: 'Seguro que quieres eliminar esta Nota de Crédito?',
                        text: "Esta acción no se puede deshacer.",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.value) {
                            if ($selectInBlock.length > 0) {
                                const currentSelections = $selectInBlock.val();

                                if (currentSelections && currentSelections.length > 0) {
                                    currentSelections.forEach(id => {
                                        selectedNoteCreditIds.delete(id);

                                        $('.formulario-nota .select2').not($selectInBlock).each(function() {
                                            $(this).find(`option[value="${id}"]`).prop('disabled', false);
                                        });
                                    });

                                    $('.formulario-nota .select2').select2();
                                }
                            }

                            $bloque.remove();

                            Swal.fire(
                                'Nota de Crédito Eliminada',
                                'La Nota de Crédito ha sido eliminada correctamente.',
                                'success'
                            )
                        }
                    })

                }

            } else {
                if ($selectInBlock.length > 0) {
                    const currentSelections = $selectInBlock.val();

                    if (currentSelections && currentSelections.length > 0) {
                        currentSelections.forEach(id => {
                            selectedNoteCreditIds.delete(id);

                            $('.formulario-nota .select2').not($selectInBlock).each(function() {
                                $(this).find(`option[value="${id}"]`).prop('disabled', false);
                            });
                        });

                        $('.formulario-nota .select2').select2();
                    }
                }
                $bloque.remove();
            }
        });

        $(document).ready(function() {
            $('.select2').select2();
            let validOC = false;
            let validHES = false;
            let reqAnticipo = false;

            $("#Form_CargaFactura").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                bloquearBtn('btn1');
                $.ajax({
                    type: 'POST',
                    url: 'CargarFacturas/registraNuevaFactura',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        desbloquearBtn('btn1');
                        if (response.success) {
                            resetFormulario("Form_CargaFactura");
                            notificaSucSweet("Excelente!!", response.message);
                        } else {
                            notificaBadSweet("Lo sentimos!!", response.message); // Muestra el mensaje de error
                        }
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
                        //window.location.reload();
                        //cargaTablaUltimasFacturas();
                    }
                });
            });

            // Traere datos iniciales
            //cargarDatosIniciales();
            //cargaTablaUltimasFacturas();
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

        function validaHojaEntrada(hojaEntrada) {
            $("#listaHES").removeClass("is-invalid is-valid");
            $("#invalid_listaHES").html("");

            if (hojaEntrada.length === 0) {
                $("#listaHES").addClass("is-invalid");
                $("#invalid_listaHES").html("No puede estar vacío");
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
                            url: 'CargarFacturas/validaHojaEntrada',
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
                    url: 'CargarFacturas/validaCodigoAnticipo',
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