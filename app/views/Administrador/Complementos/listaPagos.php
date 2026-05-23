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

<table class="table table-sm" id="tablaPagosRealizados">
    <thead>
        <tr>
            <th>Acuse</th>
            <th>Serie</th>
            <th>Proveedor</th>
            <th>Orden Compra</th>
            <th>Recepción</th>
            <th>Forma Pago</th>
            <th>Monto Pagado</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($listaPagos as $pago) {
        ?>
            <tr>
                <th class="text-center"><?= $pago['Acuse']; ?></th>
                <th><?= $pago['Serie']; ?></th>
                <th><?= $pago['Emisor']; ?></th>
                <th><?= $pago['OC']; ?></th>
                <th><?= $pago['HES']; ?></th>
                <th><?= $pago['FormaPago']; ?></th>
                <th class="text-right">$ <?= number_format($pago['MontoPagado'], 2, '.', ','); ?></th>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<script>
    $('#tablaPagosRealizados').DataTable({
        iDisplayLength: 25,
        responsive: false,
        fixedColumns: true,
        fixedHeader: true,
        scrollCollapse: true,
        autoWidth: true,
        bSort: true,
        order: [
            [0, "desc"]
        ],
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