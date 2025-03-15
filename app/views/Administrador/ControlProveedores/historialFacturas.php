<?php

use App\Globals\Controllers\FuncionesBasicas\FuncionesBasicasController;

$funcionesBase = new FuncionesBasicasController();

$debug = 0;

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
    echo '<br><br>Id Del Proveedor: ';
    var_dump($idProveedor);
}

?>

<h3 class="mt-3">HISTORIAL DE FACTURAS</h3>
<h5 class="mb-3">Facturas Recibidas Por El Proveedor</h5>

<div class="row">
    <!--Tarjeta Nueva Cama-->
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-body">
                <form id="filtrado" method="POST" role="form" autocomplete="off">
                    <input type="hidden" name="idProveedor" id="idProveedor" value="<?= $idProveedor; ?>">
                    <div class="row ">
                        <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                            <label class="col-form-label">Rango de Fechas</label>
                            <div class="input-group input-daterange mb-3" id="date-range">
                                <div class="input-group-addon">
                                    <span class="input-group-text pyme b-0 text-white bg-Equinoxgold"> Desde </span>
                                </div>
                                <input type="text" class="form-control" name="fechaInicial" id="fechaInicial" id="fechaInicial" />
                                <div class="input-group-addon">
                                    <span class="input-group-text pyme b-0 text-white bg-Equinoxgold"> Hasta </span>
                                </div>
                                <input type="text" class="form-control " name="fechaFinal" id="fechaFinal" id="fechaFinal" />
                            </div>
                        </div>

                        <div class="col-2 col-sm-2 col-md-2 col-lg-1 mt-2 pt-4">
                            <button type="submit" class="btn btn-success waves-effect waves-light">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#fechaInicial").val(moment().format('01/MM/YYYY'));
        $("#fechaFinal").val(moment().format('DD/MM/YYYY'));
        jQuery('#date-range').datepicker({
            toggleActive: true,
            orientation: "bottom",
            language: 'es',
            todayHighlight: true,
            autoclose: true,
        });
    });
</script>