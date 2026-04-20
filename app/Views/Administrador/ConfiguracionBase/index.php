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
    echo '<br><br>Contenido de empresas:';
    var_dump($empresas);
    echo '<br><br>Contenido de reglas:';
    var_dump($reglas);
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
                                    <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#Home" role="tab"><span class="hidden-sm-up"><i class="fas fa-cogs"></i></span> <span class="hidden-xs-down">Configuración de Tolerancia</span></a> </li>
                                    <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#proveedores" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Proveedores</span></a> </li>
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
                                                                <!-- Aquí iteramos las empresas -->
                                                                <?php
                                                                // Para consultar las empresas al modelo
                                                                foreach ($empresas['data'] as $empresa) {
                                                                    echo '<option value="' . $empresa['id'] . '">' . $empresa['nombre'] . '</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="idMoneda">Tipo de Moneda:</label>
                                                            <select required name="idMoneda" id="idMoneda" class="select2 form-control custom-select" style="width: 100%;">
                                                                <option value="">Selecciona una Moneda</option>
                                                                <?php
                                                                // Para consultar los tipos de moneda al modelo
                                                                // Se prioriza el campo que contenga el código de moneda (CHAR(5))
                                                                foreach ($tiposMoneda['data'] as $moneda) {
                                                                    $value = $moneda['idMoneda'] ?? $moneda['codigo'] ?? $moneda['id'] ?? '';
                                                                    $valueEsc = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                                                                    $descEsc = htmlspecialchars($moneda['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
                                                                    echo "<option value=\"$valueEsc\">$value $descEsc</option>";
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
                                                                <option value="" selected>Elige la Aplicación de la Regla</option>
                                                                <option value="1">Solo aplica por Monto</option>
                                                                <option value="2">Solo aplica por Porcentaje</option>
                                                                <option value="3">Aplica Monto y Porcentaje</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3 align-items-start">
                                                    <!-- Contenedor para Monto -->
                                                    <div class="col-md-5" id="containerMonto" style="display: none;">
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
                                                    <div class="col-md-5" id="containerPorcentaje" style="display: none;">
                                                        <div class="form-group">
                                                            <label for="porcentajeTolerancia">Porcentaje de Tolerancia (±):</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" class="form-control" name="porcentajeTolerancia" id="porcentajeTolerancia" placeholder="0.00">
                                                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                            </div>
                                                            <small class="text-muted">Este porcentaje se aplicará como límite superior e inferior.</small>
                                                        </div>
                                                    </div>
                                                    <!-- Botón de Guardar alineado y fijo al extremo de la derecha -->
                                                    <div class="col-md d-flex justify-content-md-end justify-content-start align-items-start mt-3 mt-md-0">
                                                        <div id="bloquear-btnGuardarConfig" style="display:none; margin-top: 32px;">
                                                            <button class="btn btn-primary btn-md" type="button" disabled>
                                                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> Guardando...
                                                            </button>
                                                        </div>
                                                        <div id="desbloquear-btnGuardarConfig" class="ml-2 ml-md-0" style="margin-top: 32px;">
                                                            <button type="submit" class="btn btn-md btn-outline-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </form>

                                            <!-- Tabla para mostrar las reglas guardadas
                                            Empresa, tipo de moneda, regla y monto/porcentaje de tolerancia -->

                                            <?php
                                            if (empty($reglas['data'])) {
                                                echo '<div class="row"><div class="col-12 py-3 alert alert-info">No se ha configurado ninguna regla actualmente.</div></div>';
                                            } else {
                                            ?>
                                                <table class="table table-sm" id="tableReglas">
                                                    <thead class="">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Empresa</th>
                                                            <th>Moneda</th>
                                                            <th>Regla Aplicada</th>
                                                            <th>Monto Sup.</th>
                                                            <th>Monto Inf.</th>
                                                            <th>% Sup.</th>
                                                            <th>% Inf.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $cont = 1;
                                                        foreach ($reglas['data'] as $item) {
                                                            $color = ($item['estatus'] == '1') ? 'btn-outline-success' : 'btn-outline-danger';
                                                            $icono = ($item['estatus'] == '1') ? 'fas fa-check' : 'fas fa-times';
                                                        ?>
                                                            <tr>
                                                                <td><?= $cont++; ?></td>
                                                                <td><?= $item['Empresa']; ?></td>
                                                                <td class="text-center"><?= $item['TipoMoneda']; ?></td>
                                                                <td><?= $item['DescripcionRegla']; ?></td>
                                                                <td class="text-right">$<?= number_format($item['MontoSuperior'], 2); ?></td>
                                                                <td class="text-right">$<?= number_format($item['MontoInferior'], 2); ?></td>
                                                                <td class="text-right"><?= number_format($item['PorcentajeSuperior'], 2); ?>%</td>
                                                                <td class="text-right"><?= number_format($item['PorcentajeInferior'], 2); ?>%</td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php
                                            }
                                            ?>

                                            <!-- Fin de la tabla de reglas guardadas $$$$$$$$$$ -->
                                        </div>

                                    </div>
                                    <!-- Segunda pestaña del Varcenas Peña-->
                                    <div class="tab-pane  p-20" id="proveedores" role="tabpanel">
                                        <div class="row">
                                            <!-- Columna izquierda: Formulario -->
                                            <div class="col-md-8">
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
                                                                            foreach ($empresas['data'] as $empresa) {
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
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- Columna derecha: Widget de clima -->
                                            <div class="col-md-4">
                                                <div class="card border-left border-success border-bottom border-success">
                                                    <div class="card-body">
                                                        <div class="d-flex">
                                                            <h4 class="card-title">Seleccione </h4>
                                                                <select class="custom-select w-75 ml-auto" id="empresaSelect" name="empresaSelect" style="width: 60%;">
                                                                     <option selected="" value="">Empresa</option>
                                                                            <?php
                                                                            foreach ($empresas['data'] as $empresa) {
                                                                                echo '<option value="' . $empresa['id'] . '">' . $empresa['nombre'] . '</option>';
                                                                            }
                                                                            ?>
                                                                </select>
                                                        </div>
                                                        <div class="d-flex align-items-center flex-row m-t-30">
                                                            <div class="p-2 display-5 text-info"><img src="../assets/images/sinImagen.png" id="logoEmpresa" alt="user" class="rounded-circle" width="100"></div>

                                                            <div class="p-2">
                                                                <h3 class="m-b-0" id="selectRazonSocial">Empresa</h3><small id="selectRFC">RFC</small></div>
                                                        </div>
                                                        <table class="table no-border">
                                                            <tbody>
                                                                <tr>
                                                                    <td>Limite de complementos de Pago:</td>
                                                                    <td class="font-medium" id="limiteComplementos">Limite de Complementos</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Dias de Pago:</td>
                                                                    <td class="font-medium"  id="diasPago">Dias de Pago</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>
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
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.js"></script>
    <script src="/assets/libs/sweetalert2/dist/sweetalert2.all.js"></script>
    <script src="/assets/libs/sweetalert2/sweet-alert.init.js"></script>
    <script src="/dist/js/basicFuctions.js"></script>

    <script>
        /*Este Se Queda Aquí*/
        $('#tableReglas').DataTable({
            iDisplayLength: 10,
            responsive: false,
            fixedColumns: true,
            fixedHeader: true,
            scrollCollapse: true,
            autoWidth: true,
            scrollCollapse: true,
            bSort: true,
            dom: 'Blfrtip',
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "Todo"]
            ],
            info: true,
            buttons: [{
                    extend: 'pdfHtml5',
                    className: 'btn btn-pdf bg-pyme-primary text-white',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    text: "Pdf",
                },

                {
                    extend: 'csvHtml5',
                    className: 'btn btn-pdf bg-pyme-primary text-white',
                    text: "Csv",
                    exportOptions: {
                        columns: ":not(.no-exportar)"
                    }
                },
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-pdf bg-pyme-primary text-white',
                    text: "Excel",
                    exportOptions: {
                        columns: ":not(.no-exportar)"
                    }
                },
                {
                    extend: 'copy',
                    className: 'btn btn-pdf bg-pyme-primary text-white',
                    text: "Copiar",
                    exportOptions: {
                        columns: ":not(.no-exportar)"
                    }
                }
            ]
        });
    </script>

    <?php include 'index_js.php'; ?>

</body>

</html>
