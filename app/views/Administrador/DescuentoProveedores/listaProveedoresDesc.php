<?php
$debug = 0;

$requestUri = $_SERVER['REQUEST_URI'];
$cleanUri = parse_url($requestUri, PHP_URL_PATH);
$piezasURL = explode('/', trim($cleanUri, '/'));
$paginaLink = $piezasURL[1];
include '../app/views/Layout/funciones.php';

$funcionMenu = generarMenu($menuData['data'], $paginaLink);
$datosPagina = $funcionMenu['datosPagina'];
$rutaMenu = $funcionMenu['rutaMenu'];

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
    echo '<br><br>Lista De Proveedores:<br><br>';
    var_dump($listaProveedoresDesc['data']);
}

?>

<?php
if ($listaProveedoresDesc['success'] == false) {
?>
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
        Por favor agrega un proveedor para indicar que tiene descuento.
    </div>
<?php
} else {
?>
    <table id="tableProvDesc" class="table table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>No. Proveedor</th>
                <th>Proveedor</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cont = 1;
            foreach ($listaProveedoresDesc['data'] as $proveedorDesc) {

                if ($proveedorDesc['Estatus'] == 1) {
                    $color = 'btn-outline-success';
                    $icono = 'fas fa-check';
                } else {
                    $color = 'btn-outline-danger';
                    $icono = 'fas fa-times';
                }
            ?>
                <tr>
                    <th><?= $cont++; ?></th>
                    <th><?= $proveedorDesc['IdProveedor']; ?></th>
                    <th><?= $proveedorDesc['Proveedor']; ?></th>
                    <th class="text-center">
                        <div id="bloquear-btnEstatus<?= $proveedorDesc['Id']; ?>" style="display:none;">
                            <button class="btn btn-xs btn-rounded <?= $color; ?> " type="button" disabled="" style="height: 100%;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="desbloquear-btnEstatus<?= $proveedorDesc['Id']; ?>">
                            <button id="btnEstatus<?= $proveedorDesc['Id']; ?>" onclick="cambiarEstatus(<?= $proveedorDesc['Estatus']; ?>, <?= $proveedorDesc['Id']; ?>)" type="button" class="btn btn-xs btn-rounded <?= $color; ?>"><i class="<?= $icono; ?>"></i></button>
                        </div>
                    </th>
                </tr>
            <?php
            }
            ?>
            </tr>
        </tbody>
    </table>
<?php
}
?>

<script>
    /*Este Se Queda Aquí*/
    $('#tableProvDesc').DataTable({
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