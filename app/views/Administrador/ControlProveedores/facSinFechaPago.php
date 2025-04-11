<?php

$debug = 0;

if ($debug == 1) {
    echo 'Contenido de areaData:' . PHP_EOL;
    var_dump($listaCompras);
    echo 'Contenido IdProveedor' . PHP_EOL;
    var_dump($idProveedor);
}

?>

<h3 class="mt-3">FACTURAS SIN FECHA DE PAGO</h3>
<h5 class="mb-3">Listado De Recepciones Facturadas Que Aun No Tienen Fecha De Pago</h5>

<table id="tableFacturasSinFechaPago" class="table table-sm">
    <thead>
        <tr>
            <th>#</th>
            <th>Acuse</th>
            <th>Tipo</th>
            <th>Proveedor</th>
            <th>Orden Compra</th>
            <th>No. Recepción</th>
            <th>Serie</th>
            <th>Monto</th>
            <th>Estatus</th>
            <th>Fecha Recibido</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($listaCompras)) {
            foreach ($listaCompras as $row) {
                switch ($row['claseDocto']) {
                    case 'WE':
                        $claseDocto = 'FACT';
                        break;

                    case 'KA':
                        $claseDocto = '<b>ANT</b>';
                        break;

                    case 'RZ':
                        $claseDocto = '<b>CONS</b>';
                        break;

                    default:
                        $claseDocto = '<span class="text-danger"> NO_DEF </span>';
                        break;
                }

                switch ($row['estatus']) {
                    case '1':
                        $statContable = '<center class="text-danger"><i class="fas fa-clock"></i></center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;

                    case '2':
                        $statContable = '<center class="text-success"><i class="fas fa-check"></i></center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;

                    case '3':
                        $statContable = '<center class="text-danger"><i class="fas fa-times"></i></center>';
                        $txtColor = 'text-danger';
                        $bgColor = 'danger';
                        break;

                    case '4':
                        $statContable = '<center class="text-danger"><i class="fas fa-times"></i></center>';
                        $txtColor = 'text-danger';
                        $bgColor = 'danger';
                        break;

                    default:
                        $txtColor = '';
                        $bgColor = '';
                        break;
                }

                $valida = '<center class="text-success"><i class="fas fa-check"></i></center>';
                $cantCharRecp = strlen($row['noRecepcion']);
                if ($cantCharRecp > 25) {
                    $recepciones = substr($row['noRecepcion'], 0, 25) . '...';
                } else {
                    $recepciones = $row['noRecepcion'];
                }
                $count = 1;
        ?>
                <tr>
                    <th><?= $count++; ?></th>
                    <th><?= $row['acuse']; ?></th>
                    <th><?= $claseDocto; ?></th>
                    <th><?= $row['RazonSocial']; ?></th>
                    <th><?= $row['ordenCompra']; ?></th>
                    <th><?= $recepciones; ?></th>
                    <th><?= $row['SerieFact']; ?><?= $row['FolioFact']; ?></th>
                    <th><?= number_format($row['Total'], 2, '.', ','); ?></th>
                    <th><?= $statContable; ?></th>
                    <th><?= $row['FechaReg']; ?></th>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>

<script>
    /*Este Se Queda Aquí*/
    $('#tableFacturasSinFechaPago').DataTable({
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