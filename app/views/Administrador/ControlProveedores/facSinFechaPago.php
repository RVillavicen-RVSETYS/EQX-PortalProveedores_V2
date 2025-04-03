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
    //echo '<br><br>Facturas Sin Fecha De Pago:  <br><br>';
    //var_dump($facturasSinFechaPago);
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
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
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