<?php
$debug = 0;
if ($debug == 1) {
    var_dump($proveedoresPoliticaActiva ?? null, $listaProveedores ?? null);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-pyme-primary text-white">
                <h4 class="card-title mb-0">Lista de proveedores</h4>
            </div>
            <div class="card-body border">
                <p class="text-muted small mb-3">
                    Activa <strong>descontar promociones aplicables</strong> en la factura (campo <code>proveedores.descontarPromocionesAplicables</code>).
                    El motivo queda registrado en la bitácora del sistema.
                </p>
                <form id="agregarProveedorPoliticaComercial">
                    <div class="row">
                        <label for="idProveedorPolitica">Proveedores</label>
                        <div class="input-group mb-3">
                            <select name="idProveedor" id="idProveedorPolitica" class="select2 form-control custom-select" style="width: 100%;">
                                <option value="">Selecciona un proveedor</option>
                                <?php
                                if (!empty($listaProveedores['success']) && !empty($listaProveedores['data'])) {
                                    foreach ($listaProveedores['data'] as $proveedor) {
                                        ?>
                                        <option value="<?= (int) $proveedor['IdProveedor']; ?>"><?= (int) $proveedor['IdProveedor']; ?> - <?= htmlspecialchars($proveedor['Proveedor'] ?? ''); ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label for="motivoPoliticaComercial">Motivo</label>
                        <textarea class="form-control" name="motivo" id="motivoPoliticaComercial" rows="3" style="resize: none;" placeholder="Describe el motivo del ajuste..."></textarea>
                    </div>
                    <div class="row">
                        <div id="bloquear-btnAgregaProveedorPolitica" style="display:none;">
                            <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="desbloquear-btnAgregaProveedorPolitica">
                            <button type="submit" id="btnAgregaProveedorPolitica" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <?php if (empty($proveedoresPoliticaActiva['success'])) { ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($proveedoresPoliticaActiva['message'] ?? 'No hay proveedores con ajuste de políticas comerciales activo.'); ?>
            </div>
        <?php } else { ?>
            <table class="table table-sm" id="tablePoliticasComercial">
                <thead>
                    <tr>
                        <th>No. proveedor</th>
                        <th>Proveedor</th>
                        <th>Razón social</th>
                        <th class="text-center">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedoresPoliticaActiva['data'] as $proveedor) { ?>
                        <tr>
                            <td class="text-right"><?= (int) $proveedor['IdProveedor']; ?></td>
                            <td><?= htmlspecialchars($proveedor['Proveedor'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($proveedor['RazonSocial'] ?? ''); ?></td>
                            <td class="text-center">
                                <div id="bloquear-btnPolitica<?= (int) $proveedor['IdProveedor']; ?>" style="display:none;">
                                    <button class="btn btn-xs btn-rounded btn-danger" type="button" disabled="">
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div id="desbloquear-btnPolitica<?= (int) $proveedor['IdProveedor']; ?>">
                                    <button type="button" class="btn btn-xs btn-rounded btn-outline-danger"
                                        onclick="eliminarPoliticaComercial(<?= (int) $proveedor['IdProveedor']; ?>)"
                                        title="Desactivar ajuste (pone el campo en 0)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<script src="/assets/libs/select2/dist/js/select2.full.min.js"></script>
<script src="/assets/libs/select2/dist/js/select2.min.js"></script>
<script src="/dist/js/pages/forms/select2/select2.init.js"></script>

<script>
    if ($.fn.DataTable && $('#tablePoliticasComercial').length) {
        $('#tablePoliticasComercial').DataTable({
            iDisplayLength: 10,
            responsive: false,
            fixedColumns: true,
            fixedHeader: true,
            scrollCollapse: true,
            autoWidth: true,
            bSort: true,
            dom: 'Blfrtip',
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todo']],
            info: true,
            initComplete: function() {
                var $filter = $('#tablePoliticasComercial_filter input[type="search"]');
                if ($filter.length) $filter.attr('id', 'tablePoliticasComercial_search').attr('name', 'tablePoliticasComercial_search');
            },
            buttons: [
                { extend: 'pdfHtml5', className: 'btn btn-pdf bg-pyme-primary text-white', orientation: 'landscape', pageSize: 'LEGAL', text: 'Pdf' },
                { extend: 'csvHtml5', className: 'btn btn-pdf bg-pyme-primary text-white', text: 'Csv', exportOptions: { columns: ':not(.no-exportar)' } },
                { extend: 'excelHtml5', className: 'btn btn-pdf bg-pyme-primary text-white', text: 'Excel', exportOptions: { columns: ':not(.no-exportar)' } },
                { extend: 'copy', className: 'btn btn-pdf bg-pyme-primary text-white', text: 'Copiar', exportOptions: { columns: ':not(.no-exportar)' } }
            ]
        });
    }
</script>
