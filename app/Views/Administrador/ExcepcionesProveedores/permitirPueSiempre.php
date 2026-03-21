<?php
$debug = 0;
$listaPermitir = $listaPermitirPueSiempre ?? ['success' => false];
$listaProveedores = $listaProveedores ?? ['success' => false, 'data' => []];

if ($debug == 1) {
    if (isset($menuData)) { echo 'Contenido de menuData:'; var_dump($menuData); echo '<br><br>'; }
    if (isset($areaData)) { echo 'Contenido de areaData:'; var_dump($areaData); echo '<br><br>'; }
    if (isset($areaLink)) { echo 'Contenido de areaLink:'; var_dump($areaLink); echo '<br><br>'; }
    echo 'Contenido de _SESSION:';
    var_dump($_SESSION);
    echo '<br><br>Lista Permitir PUE Siempre: <br><br>';
    var_dump($listaPermitir);
    echo '<br><br>Lista De Proveedores: <br><br>';
    var_dump($listaProveedores);
}
?>

<div class="row">
    <div class="col-md-4">

        <div class="card">
            <div class="card-header bg-pyme-primary text-white">
                <h4 class="card-title">Lista De Proveedores</h4>
            </div>
            <div class="card-body border">
                <form id="agregarProveedorPUE">
                    <div class="row">
                        <label for="idProveedorPUE">Proveedores</label>
                        <div class="input-group mb-3">
                            <select name="idProveedor" id="idProveedorPUE" class="select2 form-control custom-select" style="width: 100%;">
                                <option value="">Selecciona Un Proveedor</option>
                                <?php
                                if ($listaProveedores['success'] && !empty($listaProveedores['data'])) {
                                    foreach ($listaProveedores['data'] as $proveedor) {
                                ?>
                                    <option value="<?= (int)$proveedor['IdProveedor']; ?>"><?= htmlspecialchars($proveedor['IdProveedor'] . ' - ' . ($proveedor['Proveedor'] ?? '')); ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <label for="fechaExpiracionPUE">Fecha De Expiración Del Permiso</label>
                        <div class="input-group mb-3">
                            <input type="date" class="form-control" name="fechaExpiracion" id="fechaExpiracionPUE" min="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <label for="motivoPUE">Motivo</label>
                        <textarea class="form-control" name="motivo" id="motivoPUE" style="resize: none;" rows="3" required></textarea>
                    </div>

                    <div class="row">

                        <div id="bloquear-btnAgregaProveedorPUE" style="display:none;">
                            <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="desbloquear-btnAgregaProveedorPUE">
                            <button type="submit" id="btnAgregaProveedorPUE" class="btn btn-md btn-outline-primary mx-2 mt-3" <?= (!$listaProveedores['success'] || empty($listaProveedores['data'])) ? 'disabled' : ''; ?>>Guardar</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-8">
        <?php
        if ($listaPermitir['success'] != true || empty($listaPermitir['data'])) {
        ?>
            <div class="alert alert-info">Aún no se registran permisos PUE siempre. </div>
        <?php
        } else {
        ?>
            <table class="table table-sm" id="tablePermitirPueSiempre">
                <thead>
                    <tr>
                        <th>No. Proveedor</th>
                        <th>Proveedor</th>
                        <th>Fecha Expiración Del Permiso</th>
                        <th>Motivo</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($listaPermitir['data'] as $proveedor) {
                        if ($proveedor['Estatus'] == 1) {
                            $color = 'btn-outline-success';
                            $icono = 'fas fa-check';
                        } else {
                            $color = 'btn-outline-danger';
                            $icono = 'fas fa-times';
                        }
                    ?>
                        <tr>
                            <td class="text-right"><?= (int)$proveedor['IdProveedor']; ?></td>
                            <td><?= htmlspecialchars($proveedor['Proveedor'] ?? ''); ?></td>
                            <td><?php
                                $fechaExp = $proveedor['FechaExpiracion'] ?? '';
                                echo $fechaExp ? htmlspecialchars(date('d-m-Y', strtotime($fechaExp))) : '';
                            ?></td>
                            <td><?= htmlspecialchars($proveedor['Motivo'] ?? ''); ?></td>
                            <td class="text-center">
                                <div id="bloquear-btnEstatus6<?= $proveedor['IdConf']; ?>" style="display:none;">
                                    <button class="btn btn-xs btn-rounded <?= $color; ?> " type="button" disabled="" style="height: 100%;">
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div id="desbloquear-btnEstatus6<?= $proveedor['IdConf']; ?>">
                                    <?php if ($proveedor['Estatus'] == 1) : ?>
                                    <button type="button" class="btn btn-xs btn-rounded btn-deshabilitar-pue <?= $color; ?>" data-id-conf="<?= (int)$proveedor['IdConf']; ?>" data-id-proveedor="<?= (int)$proveedor['IdProveedor']; ?>"><i class="<?= $icono; ?>"></i></button>
                                    <?php else : ?>
                                    <button type="button" class="btn btn-xs btn-rounded <?= $color; ?>" data-id-conf="<?= (int)$proveedor['IdConf']; ?>" data-id-proveedor="<?= (int)$proveedor['IdProveedor']; ?>" data-accion="reactivar-pue"><i class="<?= $icono; ?>"></i></button>
                                    <?php endif; ?>
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
    if ($('#tablePermitirPueSiempre').length) {
    $('#tablePermitirPueSiempre').DataTable({
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
        initComplete: function() {
            var $filter = $('#tablePermitirPueSiempre_filter input[type="search"]');
            if ($filter.length) {
                $filter.attr('id', 'tablePermitirPueSiempre_search').attr('name', 'tablePermitirPueSiempre_search');
            }
        },
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
    }
</script>
