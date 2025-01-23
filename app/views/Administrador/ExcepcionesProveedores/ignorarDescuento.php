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
    echo '<br><br>Lista De Proveedores Ignora Descuento: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($listaIgnoraDesc['data']);
    echo '<br><br>Lista De Proveedores: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($listaProveedores['data']);
}

?>

<div class="row">
    <div class="col-8">
        <?php
        if ($listaIgnoraDesc['success'] != true) {
        ?>
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                <h3 class="text-info"><i class="fa fa-exclamation-circle"></i> Atención</h3> No Se Tiene Un Cierre Anual Programado.
            </div>
        <?php
        } else {
        ?>
            <table class="table table-sm" id="tableIgnoraDesc">
                <thead>
                    <tr>
                        <th>No. Proveedor</th>
                        <th>Proveedor</th>
                        <th>Motivo</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($listaIgnoraDesc['data'] as $proveedor) {
                        if ($proveedor['Estatus'] == 1) {
                            $color = 'btn-outline-success';
                            $icono = 'fas fa-check';
                        } else {
                            $color = 'btn-outline-danger';
                            $icono = 'fas fa-times';
                        }
                    ?>
                        <tr>
                            <td class="text-right"><?= $proveedor['IdProveedor']; ?></td>
                            <td><?= $proveedor['Proveedor']; ?></td>
                            <td><?= $proveedor['Motivo']; ?></td>
                            <td class="text-center">
                                <div id="bloquear-btnEstatus1<?= $proveedor['IdConfDesc']; ?>" style="display:none;">
                                    <button class="btn btn-xs btn-rounded <?= $color; ?> " type="button" disabled="" style="height: 100%;">
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div id="desbloquear-btnEstatus1<?= $proveedor['IdConfDesc']; ?>">
                                    <button id="btnEstatus1<?= $proveedor['IdConfDesc']; ?>" onclick="cambiarEstatus(<?= $proveedor['Estatus']; ?>, <?= $proveedor['IdConfDesc']; ?>, <?= 1 ?>)" type="button" class="btn btn-xs btn-rounded <?= $color; ?>"><i class="<?= $icono; ?>"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        <?php
        }
        ?>

    </div>

    <div class="col-md-4">

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title">Lista De Proveedores</h4>
            </div>
            <div class="card-body border">
                <form id="agregarProveedorIG">
                    <div class="row">
                        <label for="idProveedor">Proveedores</label>
                        <div class="input-group mb-3">
                            <select name="idProveedor" id="idProveedor" class="select2 form-control custom-select" style="width: 100%;">
                                <option value="">Selecciona Un Proveedor</option>
                                <?php
                                foreach ($listaProveedores['data'] as $proveedor) {
                                ?>
                                    <option value="<?= $proveedor['IdProveedor']; ?>"><?= $proveedor['IdProveedor']; ?> - <?= $proveedor['Proveedor']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <label for="motivo">Motivo</label>
                        <textarea class="form-control" name="motivo" id="motivo" style="resize: none;"></textarea>
                    </div>

                    <div class="row">

                        <div id="bloquear-btnAgregaProveedor" style="display:none;">
                            <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="desbloquear-btnAgregaProveedor">
                            <button type="submit" id="btnAgregaProveedorIG" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

</div>

<script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
<script src="/assets/libs/select2/dist/js/select2.min.js"></script>
<script src="/dist/js/pages/forms/select2/select2.init.js"></script>

<script>
    /*Este Se Queda Aquí*/
    $('#tableIgnoraDesc').DataTable({
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