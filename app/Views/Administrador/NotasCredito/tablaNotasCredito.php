<?php
$debug = 0;

if ($debug == 1) {
    echo '<br><br>Contenido de datosPagina:';
    var_dump($listaNotasCredito);
}

$hoy          = new DateTime();
$inicioSemana = (clone $hoy)->modify('monday this week')->setTime(0, 0, 0);
$finSemana    = (clone $hoy)->modify('sunday this week')->setTime(23, 59, 59);
$mesActual    = $hoy->format('Y-m');
$anioActual   = $hoy->format('Y');

$cantSemana = 0;
$cantMes = 0;
$cantAno = 0;
$cantTotal = 0;

foreach ($notasParaCards as $nota) {
    $cantTotal++;

    if (!empty($nota['FechaNotaCredito'])) {
        //$fechaRefrencia = new DateTime($nota['FechaNotaCredito']);
        $fechaRefrencia = DateTime::createFromFormat('d/m/Y', $nota['FechaNotaCredito']);

        if ($fechaRefrencia >= $inicioSemana && $fechaRefrencia <= $finSemana) {
            $cantSemana++;
        }
        if ($fechaRefrencia->format('Y-m') == $mesActual) {
            $cantMes++;
        }
        if ($fechaRefrencia->format('Y') == $anioActual) {
            $cantAno++;
        }
    }
}

?>

<div class="row d-flex justify-content-center">
    <!-- Card 4: Total Pendientes -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-success">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="text-white">
                        <h2 id="cardTotal"><?= $cantTotal; ?></h2>
                        <h6>Total Pendientes</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-white display-6"><i class="ti-notepad"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Este Año -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-orange">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="text-white">
                        <h2 id="cardAno"><?= $cantAno; ?></h2>
                        <h6>Notas Este Año</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-white display-6"><i class="ti-stats-up"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Este Mes -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-cyan">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="text-white">
                        <h2 id="cardMes"><?= $cantMes; ?></h2>
                        <h6>Notas Este Mes</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-white display-6"><i class="ti-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 1: Esta Semana -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-info">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div class="text-white">
                        <h2 id="cardSemana"><?= $cantSemana; ?></h2>
                        <h6>Notas Esta Semana</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-white display-6"><i class="ti-timer"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<table class="table table-sm" id="tablaNotasCredito">
    <thead>
        <tr>
            <th>No.</th>
            <th>Orden de Compra</th>
            <th>Proveedor</th>
            <th>Motivo</th>
            <th>Descuento</th>
            <th>Monto</th>
            <th>Moneda</th>
            <th>Obligatorio</th>
            <th>Fecha Reg</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $counter = 0;

        foreach ($listaNotasCredito as $notaCredito) {
            $counter++;

            $valorDescuento = '';
            switch ($notaCredito['TipoDescuento']) {
                case 'AMOUNT':
                    $valorDescuento = '$' . number_format($notaCredito['ValorDescuento'], 2);
                    break;
                case 'PERCENT':
                    $valorDescuento = $notaCredito['ValorDescuento'] . '%';
                    break;
            }

            $obligatorio = ($notaCredito['Obligatoria'] == 1) ? 'Si' : 'No';
        ?>
            <tr>
                <th><?= $counter; ?></th>
                <td class="text-center"><?= $notaCredito['FolioCompra']; ?></td>
                <td><?= $notaCredito['NombreProveedor']; ?></td>
                <td><?= $notaCredito['Descripcion']; ?></td>
                <td class="text-right"><?= $valorDescuento; ?></td>
                <td class="text-right">$<?= number_format($notaCredito['Monto'], 2); ?></td>
                <td class="text-center"><?= $notaCredito['Moneda']; ?></td>
                <td class="text-center"><?= $obligatorio; ?></td>
                <td><?= $notaCredito['FechaNotaCredito']; ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<script>
    $('#tablaNotasCredito').DataTable({
        iDisplayLength: 25,
        responsive: false,
        fixedColumns: true,
        fixedHeader: true,
        scrollCollapse: true,
        autoWidth: true,
        bSort: true,
        order: [
            [0, "asc"]
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