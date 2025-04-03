<?php

use App\Globals\FuncionesBasicas\FuncionesBasicasController;

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
    echo '<br><br>Datos Del Proveedor: ';
    var_dump($datosProveedor);
}

$idProveedor = ($datosProveedor['data']['IdProveedor'] == '') ? '' : $datosProveedor['data']['IdProveedor'];
$proveedor = ($datosProveedor['data']['Proveedor'] == '') ? '' : $datosProveedor['data']['Proveedor'];
$rfc = ($datosProveedor['data']['RFC'] == '') ? 'Sin RFC' : $datosProveedor['data']['RFC'];
$correo = ($datosProveedor['data']['Correo'] == '') ? 'Sin Correo' : $datosProveedor['data']['Correo'];
$pais = ($datosProveedor['data']['Pais'] == '') ? 'Sin País' : $datosProveedor['data']['Pais'];
$cPag =  explode('-', $datosProveedor['data']['CPag']);
$cPag = $funcionesBase->convertirCompromisoPago($cPag[0], $cPag[1]);
$razonSocial = ($datosProveedor['data']['RazonSocial'] == '') ? 'Sin Razon Social' : $datosProveedor['data']['RazonSocial'];
$regimenFiscal = ($datosProveedor['data']['RegimenFiscal'] == '') ? 'Sin Regimen Fiscal' : $datosProveedor['data']['RegimenFiscal'];


?>
<div class="row">
    <!-- Column -->
    <div class="col-lg-12 ">
        <input type="hidden" name="idProveedor" id="idProveedor" value="<?= $idProveedor; ?>">
        <div class="card">
            <div class="card-body">
                <div class="d-flex no-block align-items-center m-b-15">
                    <div class="ml-auto">
                        <a id="resetPass" onclick="$('#modalNuevaPass').modal('show');" class=""><i class="fas fa-lock"></i> Resetear Contraseña</a>
                    </div>
                </div>
                <h3 class="font-30"><i class="fas fa-key text-success"></i> <?= htmlentities($idProveedor)  ?> - <?= htmlentities($proveedor); ?></h3>
                <div class="ml-5">
                    <h4 class="mt-2"><span class="text-dark">RFC: </span> <?= htmlentities($rfc); ?></h4>
                    <h4 class="mt-2"><span class="text-dark">Correo: </span><?= htmlentities($correo); ?> <button type="button" class="btn btn-sm btn-outline-primary" onclick="$('#modalNuevoCorreo').modal('show');"><i class="fas fa-pencil-alt"></i></button></h4>
                    <h4 class="mt-2"><span class="text-dark">País: </span><?= htmlentities($pais); ?> </h4>
                    <h4 class="mt-2"><span class="text-dark">C. Pago: </span><?= htmlentities($cPag['data']); ?> </h4>
                    <h4 class="mt-2"><span class="text-dark">Razón Social: </span><?= htmlentities($razonSocial); ?> </h4>
                    <h4 class="mt-2"><span class="text-dark">Régimen Fiscal: </span><?= htmlentities($regimenFiscal); ?> </h4>
                </div>
            </div>
        </div>
    </div>
    <!-- Column -->
</div>


<!-- MODAL PARA ACTUALIZAR RFC-->
<div class="modal fade" id="modalNuevoRFC" role="dialog" aria-labelledby="modalNuevoRFCLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalNuevoRFC" role="document">
        <div class="modal-content">
            <div class="modal-header bg-pyme-primary">
                <h5 class="modal-title text-white" id="modalNuevoRFCLabel">Nuevo RFC</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalNuevoRFC').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="nuevoRFC">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="newRfc">Nuevo RFC:</label>
                                <div class="input-group">
                                    <input type="text" autocomplete="off" class="form-control" oninput="cambiaMayusculas(this.value,'newRfc');" name="newRfc" id="newRfc">
                                </div>
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
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalNuevoRFC').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnNevoRFC" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnNevoRFC">
                    <button form="nuevoRFC" id="btnNevoRFC" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- MODAL PARA ACTUALIZAR CORREO-->
<div class="modal fade" id="modalNuevoCorreo" role="dialog" aria-labelledby="modalNuevoCorreoLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalNuevoCorreo" role="document">
        <div class="modal-content">
            <div class="modal-header bg-pyme-primary" >
                <h5 class="modal-title text-white" id="modalNuevoCorreoLabel">Nuevo Correo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalNuevoCorreo').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="nuevoCorreo">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="newCorreo">Nuevo Correo:</label>
                                <div class="input-group">
                                    <input type="text" autocomplete="off" class="form-control" name="newCorreo" id="newCorreo">
                                </div>
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
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalNuevoCorreo').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnNevoCorreo" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnNevoCorreo">
                    <button form="nuevoCorreo" id="btnNevoCorreo" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- MODAL PARA ACTUALIZAR CONTRASEÑA-->
<div class="modal fade" id="modalNuevaPass" role="dialog" aria-labelledby="modalNuevaPassLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalNuevaPass" role="document">
        <div class="modal-content">
            <div class="modal-header bg-pyme-primary">
                <h5 class="modal-title text-white" id="modalNuevaPassLabel">Nueva Contraseña</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalNuevaPass').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNewPass">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="newPass">Ingrese La Nueva Contraseña:</label>
                                <div class="input-group">
                                    <input type="password" autocomplete="off" class="form-control" name="newPass" id="newPass">
                                </div>
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
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalNuevaPass').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnNuevaPass" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnNuevaPass">
                    <button form="formNewPass" id="btnNuevaPass" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>