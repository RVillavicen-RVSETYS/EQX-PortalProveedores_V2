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
    echo '<br><br>Contenido de datosPagina:';
    var_dump($listaAlertas['data']);
}

?>
<div class="mb-3 d-flex justify-content-end">
    <button type="button" class="btn ink-reaction btn-primary" data-toggle="modal" data-target="#modalNuevaAlerta"><i class="fa fa-plus"></i> Crear Alerta</button>
</div>

<table class="table table-sm" id="tableAlertas">
    <thead>
        <tr>
            <th>#</th>
            <th>Tipo Proveedor</th>
            <th>Titulo</th>
            <th>Mensaje</th>
            <th>Tipo Alerta</th>
            <th>Perido</th>
            <th>Estatus</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $cont = 1;
        foreach ($listaAlertas['data'] as $alertas) {
            $tipoProv = '';
            $tipoAlerta = '';
            $periodo = '';

            switch ($alertas['TipoProveedor']) {
                case 'NAC':
                    $tipoProv = 'Nacional';
                    break;
                case 'INT':
                    $tipoProv = 'Internacional';
                    break;
            }

            switch ($alertas['TipoMsj']) {
                case 'INFO':
                    $tipoAlerta = '<h6 class="text-info"><i class="fa fa-exclamation-circle"></i> ' . $alertas['TipoMsj'] . '</h6>';
                    break;
                case 'WARNING':
                    $tipoAlerta = '<h6 class="text-warning"><i class="fa fa-exclamation-triangle"></i> ' . $alertas['TipoMsj'] . '</h6>';
                    break;
                case 'ERROR':
                    $tipoAlerta = '<h6 class="text-danger"><i class="fas fa-times-circle"></i> ' . $alertas['TipoMsj'] . '</h6>';
                    break;
            }

            if ($alertas['TipoPeriodo'] == 2) {
                $periodo = 'Del ' . $alertas['Inicio'] . ' Al ' . $alertas['Fin'];
            } else {
                $periodo = 'Indefinido';
            }

            if ($alertas['Estatus'] == 1) {
                $color = 'btn-outline-success';
                $icono = 'fas fa-check';
            } else {
                $color = 'btn-outline-danger';
                $icono = 'fas fa-times';
            }

        ?>
            <tr>
                <td><?= $cont++; ?></td>
                <td><?= $tipoProv; ?></td>
                <td><?= $alertas['Titulo']; ?></td>
                <td><?= $alertas['Mensaje']; ?></td>
                <td class="text-left"><?= $tipoAlerta; ?></td>
                <td><?= $periodo; ?></td>

                <td>
                    <div id="bloquear-btnEstatus<?= $alertas['IdNotificacion']; ?>" style="display:none;">
                        <button class="btn btn-xs btn-rounded <?= $color; ?> " type="button" disabled="" style="height: 100%;">
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="desbloquear-btnEstatus<?= $alertas['IdNotificacion']; ?>">
                        <button id="btnEstatus<?= $alertas['IdNotificacion']; ?>" onclick="cambiarEstatus(<?= $alertas['Estatus']; ?>, <?= $alertas['IdNotificacion']; ?>)" type="button" class="btn btn-xs btn-rounded <?= $color; ?>"><i class="<?= $icono; ?>"></i></button>
                    </div>
                </td>

                <td class="text-center"><button onclick="cargarDatos(<?= $alertas['IdNotificacion']; ?>)" type="button" class="btn btn-xs btn-outline-primary" data-toggle="modal" data-target="#modalEditaAlerta"><i class="fas fa-pencil-alt"></i></button></td>
            </tr>
        <?php
        }
        ?>
    </tbody>

</table>

<!-- MODAL PARA CREAR NUEVA ALERTA-->
<div class="modal fade" id="modalNuevaAlerta" role="dialog" aria-labelledby="modalNuevaAlertaLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalNuevaAlerta" role="document">
        <div class="modal-content">
            <div class="modal-header bg-pyme-primary text-white">
                <h5 class="modal-title text-white" id="modalNuevaAlertaLabel">Crear Nueva Alerta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalNuevaAlerta').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNuevaAlerta">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="titulo">Titulo:</label>
                                <div class="input-group">
                                    <input type="text" required autocomplete="off" class="form-control" oninput="cambiaMayusculas(this.value,'titulo');" name="titulo" id="titulo">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="descripcion">Mensaje:</label>
                                <div class="input-group">
                                    <input type="text" required autocomplete="off" class="form-control" oninput="cambiaMayusculas(this.value,'descripcion');" name="descripcion" id="descripcion">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="tipoMensaje">Categoría</label>
                            <div class="input-group mb-3">
                                <select required name="tipoMensaje" id="tipoMensaje" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Una Categoría</option>
                                    <option value="INFO">Informativo</option>
                                    <option value="WARNING">Alerta</option>
                                    <option value="ERROR">Error</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="tipoProveedor">Tipo De Proveedor</label>
                            <div class="input-group mb-3">
                                <select required name="tipoProveedor" id="tipoProveedor" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Un Tipo De Proveedor</option>
                                    <option value="NAC">Nacional</option>
                                    <option value="INT">Internacional</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="tipoPeriodo">Periodo</label>
                            <div class="input-group mb-3">
                                <select required onchange="mostrarFechas(this.value,1)" name="tipoPeriodo" id="tipoPeriodo" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Un Tipo De Periodo</option>
                                    <option value="1">Indefinido</option>
                                    <option value="2">Rango De Fechas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="fechas" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="fechaInicio">Fecha Inicio:</label>
                                <div class="input-group mb-3">
                                    <input type="date" autocomplete="off" class="form-control" name="fechaInicio" id="fechaInicio">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="fechaFin">Fecha Final:</label>
                                <div class="input-group mb-3">
                                    <input type="date" autocomplete="off" class="form-control" name="fechaFin" id="fechaFin">
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
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalNuevaAlerta').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnNuevaAlerta" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnNuevaAlerta">
                    <button form="formNuevaAlerta" id="btnNuevaAlerta" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR UNA ALERTA-->
<div class="modal fade" id="modalEditaAlerta" role="dialog" aria-labelledby="modalEditaAlertaLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modalEditaAlerta" role="document">
        <div class="modal-content">
            <div class="modal-header bg-pyme-primary">
                <h5 class="modal-title text-white" id="modalEditaAlertaLabel">Editar Alerta</h5>
                <button type="button" class="close text-white" aria-label="Close" onclick="$('#modalEditaAlerta').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditaAlerta">
                    <input type="hidden" name="editIdNotificacion" id="editIdNotificacion">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="editTitulo">Titulo:</label>
                                <div class="input-group">
                                    <input type="text" required autocomplete="off" class="form-control" oninput="cambiaMayusculas(this.value,'editTitulo');" name="editTitulo" id="editTitulo">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="editDescripcion">Mensaje:</label>
                                <div class="input-group">
                                    <input type="text" required autocomplete="off" class="form-control" oninput="cambiaMayusculas(this.value,'editDescripcion');" name="editDescripcion" id="editDescripcion">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="editTipoMensaje">Categoría</label>
                            <div class="input-group mb-3">
                                <select required name="editTipoMensaje" id="editTipoMensaje" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Una Categoría</option>
                                    <option value="INFO">Informativo</option>
                                    <option value="WARNING">Alerta</option>
                                    <option value="ERROR">Error</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="editTipoProveedor">Tipo De Proveedor</label>
                            <div class="input-group mb-3">
                                <select required name="editTipoProveedor" id="editTipoProveedor" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Un Tipo De Proveedor</option>
                                    <option value="NAC">Nacional</option>
                                    <option value="INT">Internacional</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="editTipoPeriodo">Periodo</label>
                            <div class="input-group mb-3">
                                <select required onchange="mostrarFechas(this.value,2)" name="editTipoPeriodo" id="editTipoPeriodo" class="select2 form-control custom-select" style="width: 100%;height: 36px;">
                                    <option value="">Selecciona Un Tipo De Periodo</option>
                                    <option value="1">Indefinido</option>
                                    <option value="2">Rango De Fechas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="editFechas" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="editFechaInicio">Fecha Inicio:</label>
                                <div class="input-group mb-3">
                                    <input type="date" autocomplete="off" class="form-control" name="editFechaInicio" id="editFechaInicio">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="editFechaFin">Fecha Final:</label>
                                <div class="input-group mb-3">
                                    <input type="date" autocomplete="off" class="form-control" name="editFechaFin" id="editFechaFin">
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
                    <button type="button" id="btnCerrarModal" data-dismiss="modal" onclick="$('#modalEditaAlerta').modal('hide');" class="btn btn-md btn-outline-danger mx-2 mt-3">Cerrar</button>
                </div>

                <div id="bloquear-btnEditaAlerta" style="display:none;">
                    <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="desbloquear-btnEditaAlerta">
                    <button form="formEditaAlerta" id="btnEditaAlerta" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /*Este Se Queda Aquí*/
    $('#tableAlertas').DataTable({
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