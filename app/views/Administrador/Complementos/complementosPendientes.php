<?php
$debug = 0;

if ($debug == 1) {
    echo '<br><br>Contenido de datosPagina:';
    var_dump($listaComplementos);
}

?>

<table class="table table-sm" id="tablaPagosRealizados">
    <thead>
        <tr>
            <th>Proveedor</th>
            <th>Correo</th>
            <th>Acuse</th>
            <th>UUID</th>
            <th>Serie Y Folio</th>
            <th>Complemento Pendiente</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($listaComplementos as $complemento) {

            $totalComplemento = $complemento['Pagos'] - $complemento['Complemento'];

        ?>
            <tr>
                <th><?= $complemento['NoProveedor']; ?> - <?= $complemento['Proveedor']; ?></th>
                <th><?= $complemento['Correo']; ?></th>
                <th class="text-center"><?= $complemento['Acuse']; ?></th>
                <th><?= $complemento['UUID']; ?></th>
                <th class="text-right"><?= $complemento['Serie']; ?> <?= $complemento['Folio']; ?></th>
                <th class="text-right">$<?= number_format($totalComplemento, 4, '.', ','); ?></th>
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