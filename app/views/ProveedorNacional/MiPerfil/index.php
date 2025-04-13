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
    echo '<br><br>Valores pasados del Controller Data:';
    var_dump($data['datosIniciales']);
}

$idProveedor = (!empty($data['datosIniciales']['datosProveedores']['IdProveedor'])) ? $data['datosIniciales']['datosProveedores']['IdProveedor'] : '';
$nombreProveedor = (!empty($data['datosIniciales']['datosProveedores']['RazonSocial'])) ? $data['datosIniciales']['datosProveedores']['RazonSocial'] : '';
$regimenFiscal = (!empty($data['datosIniciales']['datosProveedores']['RegimenFiscal'])) ? $data['datosIniciales']['datosProveedores']['RegimenFiscal'] : '';
$pais = (!empty($data['datosIniciales']['datosProveedores']['Pais'])) ? $data['datosIniciales']['datosProveedores']['Pais'] : '';
$correo = (!empty($data['datosIniciales']['datosProveedores']['Correo'])) ? $data['datosIniciales']['datosProveedores']['Correo'] : '';

$cantCredito = (!empty($data['datosIniciales']['CompromisoPago']['cantidad'])) ? $data['datosIniciales']['CompromisoPago']['cantidad'] : '';
$tiempoCredito = (!empty($data['datosIniciales']['CompromisoPago']['tiempo'])) ? $data['datosIniciales']['CompromisoPago']['tiempo'] : '';
if ($cantCredito != '' && $tiempoCredito != '') {
    $plazoCredito = $cantCredito . ' - ' . $tiempoCredito;
} else {
    $plazoCredito = '';
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="es">

<head>
    <?php include '../app/views/Layout/header.php'; ?>
    <!-- Custom CSS -->

    <!-- Vendor -->
    <link href="/assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/fancybox/fancybox.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.toolbar.css" rel="stylesheet">
    <link href="/assets/libs/fancybox/dist/panzoom/panzoom.pins.css" rel="stylesheet">

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
                <!-- Page Content -->
                <!-- ============================================================== -->
                <!-- Row -->
                <div class="row">
                    <!-- Column -->
                    <div class="col-lg-4 col-xlg-3 col-md-5">
                        <div class="card">
                            <div class="card-body">
                                <center class="m-t-30"> <img src="/assets/images/noimg.png" class="rounded-circle" width="150" />
                                    <h4 class="card-title m-t-10"><?=$data['datosIniciales']['datosProveedores']['RFC'];?></h4>
                                    <h6 class="card-subtitle"><?=$data['datosIniciales']['datosProveedores']['RazonSocial'];?></h6>
                                </center>
                            </div>
                            <div>
                                <hr>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">No. Proveedor </small>
                                <h6><?=$idProveedor;?></h6>
                                <small class="text-muted p-t-30 db">Regimen Fiscal</small>
                                <h6><?=$regimenFiscal;?></h6>
                                <small class="text-muted p-t-30 db">Pais</small>
                                <h6><?=$pais;?></h6>
                                <small class="text-muted">Correo </small>
                                <h6 id="provCorreo"><?=$correo;?></h6>
                                <small class="text-muted p-t-30 db">Plazo de Credito</small>
                                <h6><?=$plazoCredito;?></h6>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="col-lg-8 col-xlg-9 col-md-7">
                        <div class="card">
                            <div class="card-header bg-pyme-primary headerFiltros">
                                <h4 class="m-b-0 text-white">Actualización de Datos</h4>
                            </div>
                            <div class="card-body">
                                <form id="actualizaProveedor" action="'MiPerfil/ActualizarProveedor" method="post" class="form-horizontal form-material" role="form">
                                    <div class="form-group">
                                        <label class="col-md-12">Razon Social</label>
                                        <div class="col-md-12">
                                            <input type="text" value="<?=$data['datosIniciales']['datosProveedores']['RazonSocial'];?>" class="form-control form-control-line" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="correo" class="col-md-12">Correo</label>
                                        <div class="col-md-12">
                                            <input type="email" value="<?=$data['datosIniciales']['datosProveedores']['Correo'];?>" class="form-control form-control-line" id="correo" name="correo" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12" for="password1">Contraseña</label>
                                        <div class="col-md-12">
                                            <input type="password" class="psswd form-control form-control-line" onchange="quitaErrorPass()" id="password1" name="password1">
                                            <small id="helperPass1" class="form-text text-muted">Si no quieres cambiar la contraseña deja los 2 campos en blanco.</small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12" for="password2">Repite Contraseña</label>
                                        <div class="col-md-12">
                                            <input type="password" class="psswd form-control form-control-line" onchange="quitaErrorPass()" id="password2" name="password2">
                                            <div class="invalid-feedback" id="invalid_pass2"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12 text-left" id="btnSubmitActualiza" >
                                            <button type="submit" class="btn bg-pyme-primary">Actualizar Perfil</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                </div>
                <!-- Row -->

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
        <div class="customizer-body" id="customizer_body" aria-hidden="true">
        </div>
    </aside>
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
    <script src="/dist/js/app.init.horizontalSilme.js"></script>
    <script src="/dist/js/app-style-switcher.horizontal.js"></script>
    <script src="/dist/js/app-style-switcher.js"></script>

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

    <!--chartis chart-->
    <script src="/assets/libs/chartist/dist/chartist.min.js"></script>
    <script src="/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.js"></script>
    <!--c3 charts -->
    <script src="/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="/assets/libs/moment/moment.js"></script>

    <script>
        $(document).ready(function() {
            $("#menuMiPerfil").addClass("active");
        });

        $("#actualizaProveedor").submit(function(e) {
            e.preventDefault();
            $('#btnSubmitActualiza').html('<img class="text-right" src="/assets/images/pointsLoad.gif" height="50px" alt="Cargando..." />');
            var validPass = validaPassword();
            if (!validPass) {
                $('#btnSubmitActualiza').html('<button type="submit" class="btn bg-pyme-primary">Actualizar Perfil</button>');
                return false;
            }
            var formData = $("#actualizaProveedor").serialize();
            $.ajax({
                type: 'POST',
                url: 'MiPerfil/ActualizarDatosProveedor',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {         
                        $('#provCorreo').html(response.correo);                   
                        notificaSuc(response.message); // Muestra el mensaje OK
                    } else {
                        notificaBad(response.message); // Muestra el mensaje de error
                    }
                    $('#btnSubmitActualiza').html('<button type="submit" class="btn bg-pyme-primary">Actualizar Perfil</button>');
                },
                error: function() {
                    notificaBad('Problemas al Actualizar tus Datos. Notifica a tu administrador');
                    $('#btnSubmitActualiza').html('<button type="submit" class="btn bg-pyme-primary">Actualizar Perfil</button>');
                }
            });
        });

        function validaPassword() {
            var pass1 = $('#password1').val();
            var pass2 = $('#password2').val();

            if (pass1 == '' && pass2 == '') {
            return true;
            }
            if (pass1.length < 8) {
            $('#password1').addClass('is-invalid');
            $('#password2').addClass('is-invalid');
            $('#invalid_pass2').html('La contraseña debe tener al menos 8 caracteres');
            return false;
            }
            if (pass1 != pass2) {
            $('#password1').addClass('is-invalid');
            $('#password2').addClass('is-invalid');
            $('#invalid_pass2').html('Las contraseñas no coinciden');
            return false;
            } else {
            $('#password1').removeClass('is-invalid');
            $('#password2').removeClass('is-invalid');
            $('#invalid_pass2').html('');
            return true;
            }
        }

        function quitaErrorPass() {
            $('#password1').removeClass('is-invalid');
            $('#password2').removeClass('is-invalid');
            $('#invalid_pass2').html('');
        }
    </script>
</body>

</html>