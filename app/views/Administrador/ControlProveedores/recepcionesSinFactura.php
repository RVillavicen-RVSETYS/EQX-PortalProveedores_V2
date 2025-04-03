<?php

use App\Globals\FuncionesBasicas\FuncionesBasicasController;
$funcionesBase = new FuncionesBasicasController();

$debug = 0;

if ($debug == 1) {
    echo 'Contenido de menuData:';
    var_dump($menuData);
    echo '<br><br>Contenido de areaData:';
    var_dump($areaData);
    echo '<br><br>Contenido de areaLink:';
    var_dump($areaLink);
    echo '<br><br>Contenido de _SESSION:';
    var_dump($_SESSION);
    echo '<br><br>Request-URI: ' . $_SERVER['REQUEST_URI'] . '<br>Contenido de piezasURL:';
    var_dump($piezasURL);
    echo '<br><br>Ruta del MenuActual: ' . $rutaMenu . '<br><br>Contenido de datosPagina:';
    var_dump($datosPagina);
    echo '<br><br>Datos De Recepciones Sin Facturas: ';
    var_dump($datosRecepcionesSinFactura);
}

?>

<h3 class="mt-3">RECEPCIONES POR FACTURAR</h3>
<h5 class="mb-3">Listado de recepciones pendientes por Facturar de parte del Proveedor</h5>

<table class="table table-sm" id="tableRecSinFact">
    <thead>
        <tr>
            <th>#</th>
            <th>Orden De Compra</th>
            <th>No. Recepción</th>
            <th>Compromiso Pago</th>
            <th>Monto</th>
            <th>Moneda</th>
            <th>SubTotal</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = 1;
        $subTotal = 0;
        $ultimaCompra = null;

        $totalRegistros = count($datosRecepcionesSinFactura['data']);

        foreach ($datosRecepcionesSinFactura['data'] as $index => $recepciones) {
            if ($recepciones['ErpIdCompra'] != $ultimaCompra) {
                $subTotal = 0;
            }

            $subTotal += $recepciones['Monto'];

            $esUltimoDeCompra = false;
            if ($index == $totalRegistros - 1 || $datosRecepcionesSinFactura['data'][$index + 1]['ErpIdCompra'] != $recepciones['ErpIdCompra']) {
                $esUltimoDeCompra = true;
            }

            $partesCP = explode("-", $recepciones['CP']);
            $compromisoPago = $funcionesBase->convertirCompromisoPago($partesCP[0], $partesCP[1]);

        ?>
            <tr>
                <td><?= $count++; ?></td>
                <td><?= $recepciones['OC']; ?></td>
                <td><?= $recepciones['HES']; ?></td>
                <td class="text-right"><?= $compromisoPago['data']; ?></td>
                <td class="text-right">$ <?= number_format($recepciones['Monto'], 2, '.', ','); ?></td>
                <td><?= $recepciones['Moneda']; ?></td>
                <td class="text-right"><?= $esUltimoDeCompra ? '$ ' . number_format($subTotal, 2, '.', ',') : '-'; ?></td>
            </tr>
        <?php
            $ultimaCompra = $recepciones['ErpIdCompra'];
        }
        ?>
    </tbody>
</table>

<script>
    /*Este Se Queda Aquí*/
    $('#tableRecSinFact').DataTable({
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