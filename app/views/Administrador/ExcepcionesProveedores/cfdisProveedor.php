<?php
$debug = 0;

if ($debug == 1) {
    echo '<br><br>Lista De Proveedores Con Sus CFDIs Permitidos: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($cfdisPermitidosProv['data']);
    echo '<br><br>Lista De Proveedores: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($listaProveedores['data']);
    echo '<br><br>Lista General De CFDIs Permitidos: <br><br>Contenido de Proveedores_Mdl.php:';
    var_dump($cfdisPermitidosGeneral['data']);
}

?>

<div class="row">
    <div class="col-md-4">

        <div class="card">
            <div class="card-header bg-pyme-primary text-white">
                <h4 class="card-title">Lista De Proveedores</h4>
            </div>
            <div class="card-body border">
                <form id="agregarProveedorBUC">
                    <div class="row">
                        <label for="idProveedorBUC">Proveedores</label>
                        <div class="input-group mb-3">
                            <select name="idProveedorBUC" id="idProveedorBUC" class="select2 form-control custom-select" style="width: 100%;">
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
                        <label for="idUsoCfdiPermitido">Uso CFDI</label>
                        <div class="input-group mb-3">
                            <select name="idUsoCfdiPermitido[]" id="idUsoCfdiPermitido" class="select2 form-control custom-select" multiple style="width: 100%;">
                                <option value="">Selecciona Uso De CFDI</option>
                                <?php
                                foreach ($cfdisPermitidosGeneral['data'] as $usoCfdi) {
                                ?>
                                    <option value="<?= $usoCfdi['ClaveUsoCFDI']; ?>"><?= $usoCfdi['ClaveUsoCFDI']; ?> - <?= $usoCfdi['UsoCfdi']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">

                        <div id="bloquear-btnAgregaProveedorCfdiPerm" style="display:none;">
                            <button class="btn btn-primary btn-md" type="button" disabled="" style="height: 100%;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="desbloquear-btnAgregaProveedorCfdiPerm">
                            <button type="submit" id="btnAgregaProveedorCfdiPerm" class="btn btn-md btn-outline-primary mx-2 mt-3">Guardar</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="col-8">
        <?php
        if ($cfdisPermitidosProv['success'] != true) {
        ?>
            <div class="alert alert-info">Lista de CFDIs permitidos por proveedor. </div>
        <?php
        } else {
        ?>
            <table class="table table-sm" id="tableCFDIsPermitidos">
                <thead>
                    <tr>
                        <th>No. Proveedor</th>
                        <th>Proveedor</th>
                        <th>Cfdis</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($cfdisPermitidosProv['data'] as $proveedor) {

                    ?>
                        <tr>
                            <td class="text-right"><?= $proveedor['IdProveedor']; ?></td>
                            <td><?= $proveedor['Proveedor']; ?></td>
                            <td><?= $proveedor['UsoCfdi']; ?></td>
                            <td><button onclick="eliminarCfdiPermitido(<?= $proveedor['IdBloq']; ?>);" class="btn btn-xs btn-rounded btn-outline-danger"><i class="fas fa-trash-alt"></i></button></td>
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
    $(document).on('submit', '#agregarProveedorBUC', function(event) {

        event.preventDefault();

        $.ajax({
            url: 'ExcepcionesProveedores/agregarProveedorBUC',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const respuesta = JSON.parse(response);
                if (respuesta.success) {
                    notificaSuc(respuesta.message);
                    cargarBloqueoDeCFDIs();
                } else {
                    notificaBad(respuesta.message);
                    bloqueoBtn('bloquear-btnAgregaProveedorCfdiPerm', 2);
                }
            },
            beforeSend: function() {
                bloqueoBtn('bloquear-btnAgregaProveedorCfdiPerm', 1);
            }
        });
    });

    /*Este Se Queda Aquí*/
    $('#tableCFDIsPermitidos').DataTable({
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
</script>