<?php

use App\Globals\FuncionesBasicas\FuncionesBasicasController;

$funcionesBase = new FuncionesBasicasController();

$debug = 0;

if ($debug == 1) {
    echo 'Contenido de areaData:' . PHP_EOL;
    var_dump($listaCompras);
    echo 'Contenido IdProveedor' . PHP_EOL;
    var_dump($idProveedor);
}

$fechaInicial = date("Y-m-01");
$fechaFinal = date("Y-m-t");
?>

<h3 class="mt-3">HISTORIAL DE FACTURAS</h3>
<h5 class="mb-3">Facturas Recibidas Por El Proveedor</h5>

<div class="row">
    <!--Tarjeta Nueva Cama-->
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-body">
                <form id="formHistorico" method="POST" role="form" autocomplete="off">
                    <input type="hidden" name="idProveedor" id="idProveedor" value="<?= $idProveedor; ?>">
                    <div class="row ">
                        <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                            <label class="col-form-label">Rango de Fechas</label>
                            <div class="input-group input-daterange mb-3" id="date-range">
                                <div class="input-group-addon">
                                    <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> Desde </span>
                                </div>
                                <input type="date" class="form-control" name="fechaInicial" id="fechaInicial" value="<?= $fechaInicial; ?>" />
                                <div class="input-group-addon">
                                    <span class="input-group-text pyme b-0 text-white bg-pyme-primary"> Hasta </span>
                                </div>
                                <input type="date" class="form-control " name="fechaFinal" id="fechaFinal" value="<?= $fechaFinal; ?>" />
                            </div>
                        </div>

                        <div class="col-2 col-sm-2 col-md-2 col-lg-1 mt-2 pt-4">
                            <button type="submit" class="btn btn-success waves-effect waves-light">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
                    <td class="text-center">' . $row['acuse'] . '</td>
                    <td>' . $claseDocto . '</td>
                    <td>' . $row['ordenCompra'] . '</td>
                    <td>' . $recepciones . '</td>
                    <td>' . $row['fechaReg'] . '</td>
                    <td>' . $row['referencia'] . '</td>
                    <td>' . $valida . ' </td>
                    <td>' . $statContable . '</td>
                    <td> <button class="btn btn-sm btn-success" onClick="detalleCompra(\'' . $row['acuse'] . '\',' . $row['IdProveedor'] . ');"><i class="text-white icon-doc"></i></button> </td>
                    </tr>';
                }
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