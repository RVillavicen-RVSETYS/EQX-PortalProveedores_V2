<?php
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

<div class="row">
    <div class="col-2">
        <div class="list-group d-flex text-center" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action font-18" id="list-Proveedor-list" data-toggle="list" href="#list-Proveedor" onclick="datosGenerales(<?= $idProveedor; ?>);" role="tab" aria-controls="Proveedor"><i class="fas fa-address-card"></i><br>PROVEEDOR<br><small>Datos Generales.</small></a>
            <a class="list-group-item list-group-item-action font-18" id="list-Recepciones-list" data-toggle="list" href="#list-Recepciones" onclick="recepciones(<?= $idProveedor; ?>);" role="tab" aria-controls="Recepciones"><i class="mdi mdi-email"></i><br>RECEPCIONES<br><small>Sin Facturas Del Prov.</small></a>
            <a class="list-group-item list-group-item-action font-18" id="list-SinFecha-list" data-toggle="list" href="#list-SinFecha" onclick="sinFecha(<?= $idProveedor; ?>);" role="tab" aria-controls="SinFecha"><i class="fas fa-calendar-alt"></i><br>SIN FECHA<br><small>Fac. Sin Fecha Pago.</small></a>
            <a class="list-group-item list-group-item-action font-18" id="list-Historico-list" data-toggle="list" href="#list-Historico" onclick="historico(<?= $idProveedor; ?>);" role="tab" aria-controls="Historico"><i class="fas fa-file-pdf"></i><br>HISTORICO<br><small>Historial De Facturas.</small></a>
        </div>
    </div>
    <div class="col-10 border">
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade" id="list-Proveedor" role="tabpanel" aria-labelledby="list-Proveedor-list">

            </div>
            <div class="tab-pane fade" id="list-Recepciones" role="tabpanel" aria-labelledby="list-Recepciones-list">

            </div>
            <div class="tab-pane fade" id="list-SinFecha" role="tabpanel" aria-labelledby="list-SinFecha-list">

            </div>
            <div class="tab-pane fade" id="list-Historico" role="tabpanel" aria-labelledby="list-Historico-list">

            </div>
        </div>
    </div>
</div>