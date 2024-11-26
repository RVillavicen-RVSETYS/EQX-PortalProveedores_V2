<?php
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
    echo '<br><br>Id Del Proveedor: ';
    var_dump($idProveedor);
}

?>

<table class="table table-sm" id="tableAprobacionesNa">
    <thead>
        <tr>
            <th>CP</th>
            <th>Acuse</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Proveedor</th>
            <th>OC</th>
            <th>Folio Int</th>
            <th>Receptor</th>
            <th>Monto</th>
            <th>Descuento</th>
            <th>Estatus Fiscal</th>
            <th>Factura</th>
            <th>Acciones</th>
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
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>

<script>
    $('#tableAprobacionesNa').DataTable({
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
                className: 'btn btn-primary',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                text: "PDF",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            },
            {
                extend: 'csvHtml5',
                className: 'btn btn-primary',
                text: "CSV",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            },
            {
                extend: 'excelHtml5',
                className: 'btn btn-primary',
                text: "Excel",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            },
            {
                extend: 'copy',
                className: 'btn btn-primary',
                text: "Copiar",
                exportOptions: {
                    columns: ":not(.no-exportar)"
                }
            }
        ]
    });
</script>