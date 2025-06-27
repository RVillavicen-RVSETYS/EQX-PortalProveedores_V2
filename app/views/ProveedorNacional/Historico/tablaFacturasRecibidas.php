<?php
$debug = 0;

if ($debug == 1) {
    echo 'Contenido de areaData:' . PHP_EOL;
    var_dump($listaCompras);
}


?>
<div class="table-responsive">
    <table class="table table-sm table-striped" id="tableListaCompras" style="width:100%">
        <thead>
            <tr>
                <th class="text-center"># Acuse</th>
                <th class="">Tipo</th>
                <th>Orden Compra</th>
                <th>No Recepcion</th>
                <th>Fecha Recepción</th>
                <th>Folio Interno</th>
                <th>Status Fiscal</th>
                <th>Probable Pago</th>
                <th>Comp. Pago</th> 
                <th class="no-exportar">Ver</th>
            </tr>
        </thead>
        <tbody>
            <?php
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

                $valida = '<center class="text-success"><i class="fas fa-check"></i></center>';
                $compPago = '<center class="text-danger"><i class="fas fa-times"></i></center>';

                if ($row['totalPagos'] == $row['totalComplementos'] &&  $row['Total'] == $row['totalPagos']) {
                    $compPago = '<center class="text-success"><i class="fas fa-check"></i></center>';
                }
                if ($row['totalPagos'] == $row['totalComplementos']) {
                    if ($row['totalComplementos'] > 0) {
                        $compPago = '<center class="text-success">Al Corriente</center>';
                    } else {
                        $compPago = '';
                    }
                    
                }
                if ($row['totalPagos'] > $row['totalComplementos']) {
                    $compPago = '<center class="text-danger">Pendiente</center>';
                }

                switch ($row['estatus']) {
                    case '1':
                        $statContable = '<center class="text-muted"><i class="mdi mdi-clock"></i> En Verificación</center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;

                    case '2':
                        $statContable = '<center class=""> ' . date('d-m-Y', strtotime($row['FechaPago'])) . ' </i></center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;

                    case '3':
                        $statContable = '<center class="text-danger"><i class="fas fa-ban"></i> Factura rechazada</center>';
                        $txtColor = 'text-danger';
                        $bgColor = 'danger';
                        break;

                    case '4':
                        $statContable = '<center class="text-danger"><i class="mdi mdi-close-circle-outline"></i> Factura Cancelada</center>';
                        $txtColor = 'text-danger';
                        $bgColor = 'danger';
                        $valida = '<center class="text-danger"><i class="fas fa-times"></i> Cancelada</center>';
                        $compPago = '<center class="text-danger"><i class="fas fa-times"></i></center>';
                        break;

                    default:
                        $txtColor = '';
                        $bgColor = '';
                        break;
                }

                $cantCharRecp = strlen($row['noRecepcion']);
                if ($cantCharRecp > 25) {
                    $recepciones = substr($row['noRecepcion'], 0, 25) . '...';
                } else {
                    $recepciones = $row['noRecepcion'];
                }

                echo '<tr class="' . $txtColor . ' ' . $bgColor . '" >
                    <td class="text-center">' . $row['acuse'] . '</td>
                    <td>' . $claseDocto . '</td>
                    <td>' . $row['ordenCompra'] . '</td>
                    <td>' . $recepciones . '</td>
                    <td>' . $row['fechaReg'] . '</td>
                    <td>' . $row['referencia'] . '</td>
                    <td>' . $valida . ' </td>
                    <td>' . $statContable . '</td>
                    <td>' . $compPago . '</td>
                    <td> <button class="btn btn-sm btn-success" onClick="detalleCompra(\'' . $row['acuse'] . '\');"><i class="text-white icon-doc"></i></button> </td>';
            }
            ?>
        </tbody>
    </table>
</div>
<script>
    

    $('#tableListaCompras').DataTable({
        iDisplayLength: 25,
        responsive: false,
        fixedColumns: true,
        fixedHeader: true,
        scrollCollapse: true,
        autoWidth: true,
        bSort: true,
        order: [[0, "desc"]],
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