<?php
$debug = 0;

if ($debug == 1) {
    echo '<br><br>Contenido de datosPagina:';
    var_dump($listaPagos);
}

if (empty($listaPagos)) {
    echo '<div class="alert alert-info">No se encontraron pagos, por favor filtre por otro rango de fechas. </div>';
    exit(0);
}

?>

<table class="table table-sm" id="tablaReporteContable">
    <thead>
        <tr>
            <th class="text-center">Acuse</th>
            <th>Razón Social</th>
            <th>RFC</th>
            <th>Folio Fac</th>
            <th>UUID Fac</th>
            <th>Metodo Pago</th>
            <th>Monto Egreso</th>
            <th>Fecha de Pago</th>
            <th>Tipo de Cambio</th>
            <th>Folio CP</th>
            <th>UUID CP</th>
            <th>Folio NC</th>
            <th>UUID NC</th>
            <th>Banco y N° de Cuenta</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($listaPagos as $pago) {
        ?>
            <tr>
                <th class="text-center"><?= $pago['Acuse']; ?></th>
                <th><?= $pago['RazonSocial']; ?></th>
                <th><?= $pago['RFC']; ?></th>
                <th><?= $pago['FolioFac']; ?></th>
                <th><?= $pago['UUIDFac']; ?></th>
                <th><?= $pago['MetodoPago']; ?></th>
                <th class="text-right">$ <?= number_format($pago['MontoEgreso'], 2, '.', ','); ?></th>
                <th class="text-center"><?= $pago['FechaPago']; ?></th>
                <th><?= $pago['TipoCambio']; ?></th>
                <th><?= $pago['FolioCP']; ?></th>
                <th><?= $pago['UUIDCP']; ?></th>
                <th><?= $pago['FolioNC']; ?></th>
                <th><?= $pago['UUIDNC']; ?></th>
                <th><?= $pago['CuentaBanco']; ?></th>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<script>
    $('#tablaReporteContable').DataTable({
        iDisplayLength: 25,
        responsive: false,
        fixedColumns: true,
        fixedHeader: true,
        scrollCollapse: true,
        autoWidth: true,
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