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
    var_dump($exentosFechaEmision['data']);
    echo '<br><br>Lista De Proveedores: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($listaProveedores['data']);
}

?>

<div class="row">
    <div class="col-md-4">

        <div class="card">
            <div class="card-header bg-Equinoxgold text-white">
                <h4 class="card-title">Lista De Proveedores</h4>
            </div>
            <div class="card-body border">
                <form id="agregarProveedorEFE">
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

                        <div id="bloquear-btnAgregaProveedorEFE" style="display:none;">
                            <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="desbloquear-btnAgregaProveedorEFE">
                            <button type="submit" id="btnAgregaProveedorEFE" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-8">
        <?php
        if ($exentosFechaEmision['success'] != true) {
        ?>
            <div class="alert alert-info">Aún no se registran proveedores para . </div>
        <?php
        } else {
        ?>
            <table class="table table-sm" id="tableFechaEmision">
                <thead>
                    <tr>
                        <th>No. Proveedor</th>
                        <th>Proveedor</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($exentosFechaEmision['data'] as $proveedor) {
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
                            <td class="text-center">
                                <div id="bloquear-btnEstatus3<?= $proveedor['IdExento']; ?>" style="display:none;">
                                    <button class="btn btn-xs btn-rounded <?= $color; ?> " type="button" disabled="" style="height: 100%;">
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div id="desbloquear-btnEstatus3<?= $proveedor['IdExento']; ?>">
                                    <button id="btnEstatus3<?= $proveedor['IdExento']; ?>" onclick="cambiarEstatus(<?= $proveedor['Estatus']; ?>, <?= $proveedor['IdExento']; ?>, <?= 3 ?>)" type="button" class="btn btn-xs btn-rounded <?= $color; ?>"><i class="<?= $icono; ?>"></i></button>
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

</div>

<script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
<script src="/assets/libs/select2/dist/js/select2.min.js"></script>
<script src="/dist/js/pages/forms/select2/select2.init.js"></script>

<script>
    /*Este Se Queda Aquí*/
    $('#tableFechaEmision').DataTable({
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