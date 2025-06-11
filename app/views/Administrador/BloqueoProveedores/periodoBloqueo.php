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
    echo '<br><br>Lista De Proveedores: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($periodo['data']);
}

?>

<?php
if ($periodo['success'] == false) {
?>
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
        No se tiene un cierre anual progamado.
    </div>
<?php
} else {
?>
    <div class="row">
        <div class="col-12">
            <h4><i class="fas fa-calendar-times text-info"></i> De <span class="text-success"><?= $periodo['data']['FechaIni']; ?></span> Hasta <span class="text-success"><?= $periodo['data']['FechaFin'] ?></span></h4>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <h5 class="text-info">Mensaje</h5>
            <p><?= $periodo['data']['MsjEsp']; ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h5 class="text-info">Message</h5>
            <p><?= $periodo['data']['MsjIng']; ?></p>
        </div>
    </div>
<?php
}
?>

<!-- MODAL PARA ASIGNAR BLOQUE Y EDITAR ESTATUS-->
<div class="modal fade" id="modalRegistraCierre" role="dialog" aria-labelledby="modalRegistraCierreLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalRegistraCierre" role="document">
        <div class="modal-content">
            <div class="modal-header bg-pyme-primary">
                <h5 class="modal-title text-white" id="modalRegistraCierreLabel">Programar Nuevo Cierre</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalRegistraCierre').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistraCierre">
                    <input type="hidden" id="proveedorId" name="proveedorId">

                    <div class="row">
                        <div class="col-md-6">
                            <label for="fechaInicio">Fecha Inicio:</label>
                            <div class="input-group mb-3">
                                <input required type="date" autocomplete="off" class="form-control" name="fechaInicio" id="fechaInicio">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="fechaFin">Fecha Final:</label>
                            <div class="input-group mb-3">
                                <input required type="date" autocomplete="off" class="form-control" name="fechaFin" id="fechaFin">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="msjEsp">Mensaje En Español</label>
                            <div class="input-group mb-3">
                                <textarea required class="form-control" name="msjEsp" id="msjEsp" style="resize: none;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="msjIng">Mensaje En Inglés</label>
                            <div class="input-group mb-3">
                                <textarea required class="form-control" name="msjIng" id="msjIng" style="resize: none;"></textarea>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <div id="bloquear-btnCerrarModal" style="display:none;">
                    <button class="btn btn-danger btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnCerrarModal">
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalRegistraCierre').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnRegistraCierre" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnRegistraCierre">
                    <button form="formRegistraCierre" id="btnRegistraCierre" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>