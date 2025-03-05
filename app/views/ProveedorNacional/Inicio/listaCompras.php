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
                <th>Programación de Pago</th>
                <th>Ver</th>
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

                switch ($row['estatus']) {
                    case '1':
                        $statContable = '<center class="text-danger"><i class="md md-close"></i></center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;

                    case '2':
                        $statContable = '<center class="text-success"><i class="fas fa-check"></i></center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;

                    case '3':
                        $statContable = '<center class="text-danger"><i class="md md-close"></i></center>';
                        $txtColor = 'text-danger';
                        $bgColor = 'danger';
                        break;

                    case '4':
                        $statContable = '<center class="text-danger"><i class="md md-close"></i></center>';
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

                echo '<tr class="' . $txtColor . ' ' . $bgColor . '" >
                    <td>' . $row['acuse'] . '</td>
                    <td>' . $claseDocto . '</td>
                    <td>' . $row['ordenCompra'] . '</td>
                    <td>' . $recepciones . '</td>
                    <td>' . $row['fechaReg'] . '</td>
                    <td>' . $row['referencia'] . '</td>
                    <td>' . $valida . ' </td>
                    <td>' . $statContable . '</td>
                    <td> <button class="btn btn-sm btn-success" onClick="detalleCompra(\'' . $row['acuse'] . '\');"><i class="text-white icon-doc"></i></button> </td>';
            }
            ?>
        </tbody>
    </table>
</div>
<script>
    function detalleCompra(acuse) {
        $('#customizer_body').html('<div class="loading text-center"><img src="../assets/images/loading.gif" alt="loading" /><br/>Un momento, por favor...</div>');
        $(".customizer").toggleClass('show-service-panel');
        $(".service-panel-toggle").toggle();
        $.post("Inicio/detalladoDeCompra", {
                acuse: acuse
            },
            function(respuesta) {
                $("#customizer_body").html(respuesta);
            });
    }

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