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
    var_dump($listaProveedores['data']);
}

?>

<table class="table table-sm" id="tableProveedoresBloq">
    <thead>
        <tr>
            <th>#</th>
            <th>No. Proveedor</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Activado</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $cout = 1;
        foreach ($listaProveedores['data'] as $proveedor) {

            $correo = (is_null($proveedor['Correo'])) ? '-' : $proveedor['Correo'];
            //$bloque = (is_null($proveedor['GrupoBloqueo']) or empty($proveedor['GrupoBloqueo'])) ? '-' : $proveedor['GrupoBloqueo'];

            if ($proveedor['EstatusBloqueo'] == 1) {
                $icono = 'fas fa-check';
                $color = 'success';
            } else {
                $icono = 'fas fa-times';
                $color = 'danger';
            }
        ?>
            <tr>
                <th><?= $cout++ ?></th>
                <th><?= $proveedor['IdProveedor']; ?></th>
                <th><?= $proveedor['Proveedor']; ?></th>
                <th><?= $correo; ?></th>
                <th class="text-center">
                    <div id="bloquear-btnEstatus<?= $proveedor['IdProveedor'] ?>" style="display:none;">
                        <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="desbloquear-btnEstatus<?= $proveedor['IdProveedor'] ?>">
                        <button id="btnEstatus<?= $proveedor['IdProveedor'] ?>" class="btn btn-sm btn-md btn-outline-<?= $color; ?> mx-2 "><i class="<?= $icono ?>"></i></button>
                    </div>
                </th>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<!-- MODAL PARA ASIGNAR BLOQUE Y EDITAR ESTATUS
<div class="modal fade" id="modalEditaProv" role="dialog" aria-labelledby="modalEditaProvLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalEditaProv" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#11334b;">
                <h5 class="modal-title text-white" id="modalEditaProvLabel">Editar Proveedor</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalEditaProv').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditaProv">
                    <input type="hidden" id="proveedorId" name="proveedorId">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="bloque">Bloque:</label>
                            <div class="input-group mb-3">
                                <select required name="bloque" id="bloque" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Un Bloque</option>
                                    <option value="A">Bloque A</option>
                                    <option value="B">Bloque B</option>
                                    <option value="C">Bloque C</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="estatusFact">Factura</label>
                            <div class="input-group mb-3">
                                <select required name="estatusFact" id="estatusFact" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Una Opción</option>
                                    <option value="0">No</option>
                                    <option value="1">Si</option>
                                </select>
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
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalEditaProv').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnEditaProv" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnEditaProv">
                    <button form="formEditaProv" id="btnEditaProv" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
-->

<script>
    /*Este Se Queda Aquí*/
    $('#tableProveedoresBloq').DataTable({
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
                className: 'btn btn-pdf bg-Equinoxgold text-white',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                text: "Pdf",
            },

            {
                extend: 'csvHtml5',
                className: 'btn btn-pdf bg-Equinoxgold text-white',
                text: "Csv",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            },
            {
                extend: 'excelHtml5',
                className: 'btn btn-pdf bg-Equinoxgold text-white',
                text: "Excel",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            },
            {
                extend: 'copy',
                className: 'btn btn-pdf bg-Equinoxgold text-white',
                text: "Copiar",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            }
        ]
    });
</script>