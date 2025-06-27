<?php
$debug = 0;

if ($debug == 1) {
    echo 'Contenido de Data:' . PHP_EOL;
    var_dump($data);
}

?>
<table class="table no-wrap v-middle">
    <thead>
        <tr>
            <th class="border-0 text-muted">Proveedor</th>
            <th class="border-0 text-muted">Correo</th>
            <th class="border-0 text-muted text-center">Detalle</th>
            <th class="border-0 text-muted text-center">Monto Pendiente</th>
            <th class="border-0 text-muted text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($data['datosIniciales']['datosTabla'] as $row) {
            switch ($row['estatus']) {
                case '1':
                    $estatus = '<i class="fa fa-circle text-success" data-toggle="tooltip" data-placement="top" title="Activo"></i>';
                    break;
                case '0':
                    $estatus = '<i class="fa fa-circle text-danger" data-toggle="tooltip" data-placement="top" title="Inactivo"></i>';
                    break;
                default:
                    $estatus = '<i class="fa fa-circle text-muted" data-toggle="tooltip" data-placement="top" title="Desconocido"></i>';
                    break;
            }

            $urlFoto = $row['urlFoto'] ?? '../assets/images/sinImagen.png';
            $nombre = $row['nombre'] ?? '-';
            $razonSocial = $row['razonSocial'] ?? 'Sin Razon Social.';
            $correo = $row['correo'] ?? 'No hay correo...';            

            switch ($_POST["tipoSeguimiento"]) {
                case 'ComplementosMasViejos':
                    $detalle = $row['cantCompras'] ?? 0;
                    $plural = ($detalle > 1) ? 's' : '';
                    $detallado = '<b>'.$detalle . '</b> Complemento'.$plural . ' desde:<br><b>' . date_format(date_create($row['minFechaPago']), 'd/m/Y').'</b>';
                    $totalFacturado = $row['totalPagos'] ?? 0;
                    $totalComplemento = $row['totalComplemento'] ?? 0;
                    $monto = ($totalFacturado - $totalComplemento) ?? 0;
                    $monto = number_format($monto, 2, '.', ',');

                    break;
    
                case 'MasComplementos':
                    $detalle = $row['cantCompras'] ?? 0;
                    $plural = ($detalle > 1) ? 's' : '';
                    $detallado = '<b>'.$detalle . '</b> Complemento'.$plural . ' Pendiente' . $plural;
                    $totalFacturado = $row['totalPagos'] ?? 0;
                    $totalComplemento = $row['totalComplemento'] ?? 0;
                    $monto = ($totalFacturado - $totalComplemento) ?? 0;
                    $monto = number_format($monto, 2, '.', ',');
                    break;
    
                case 'InsolutosPendientes':
                    $detalle = $row['cantCompras'] ?? 0;
                    $plural = ($detalle > 1) ? 's' : '';
                    $detallado = '<b>'.$detalle . '</b> Insoluto'.$plural . ' desde:<br><b>' . date_format(date_create($row['minFechaPago']), 'd/m/Y').'</b>';
                    $monto = $row['totalInsolutos'] ?? 0;
                    $monto = number_format($monto, 2, '.', ',');
                    break;
    
                default:
                    $detalle = 'El tipo de Seguimiento no esta Definido.';
                    break;
            }

            ?>
        <tr>
            <td>
                <div class="d-flex no-block align-items-center">
                    <div class="m-r-10"><img src="<?=$urlFoto;?>" alt="user" class="rounded-circle" width="45" /></div>
                    <div class="">
                        <h5 class="m-b-0 font-16 font-medium"><?=$nombre;?></h5>
                        <span class="text-muted"><?=$razonSocial;?></span>
                    </div>
                </div>
            </td>
            <td><?=$correo;?></td>
            <td class="text-center"><?=$detallado;?></td>
            <td class="font-medium text-center">$ <?=$monto;?></td>
            <td class="text-center"><?=$estatus;?></td>
        </tr>
            <?php
        }
        ?>
        
    </tbody>
</table>