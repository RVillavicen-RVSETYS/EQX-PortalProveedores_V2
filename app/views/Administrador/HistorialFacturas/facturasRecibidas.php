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
    echo '<br><br>Contenido de historialFacturas';
    var_dump($historialFacturas['data']);
}

function calcularFechaPago($basePago, $fechaVence)
{
    return "Hola Mundo";
}

?>

<?php
if ($historialFacturas['success'] == false) {
?>
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
        <h3 class="text-info"><i class="fa fa-exclamation-circle"></i> Atención</h3> No Hay Facturas Recientes.
    </div>
<?php
} else {
?>
    <table class="table table-sm" id="tableAprobacionesNa">
        <thead>
            <tr>
                <th>Acuse</th>
                <th>Clase Docto</th>
                <th>Fecha Registro</th>
                <th>País</th>
                <th>Proveedor</th>
                <th>Orden Compra</th>
                <th>Folio Ref</th>
                <th>Receptor</th>
                <th>Monto</th>
                <th>Estatus Físcal</th>
                <th>Estatus Contable</th>
                <th>Factura</th>
                <th>Complemento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($historialFacturas['data'] as $key => $historial) {

                switch ($historial['ClaseDocto']) {
                    case 'KA':
                        $claseDocto = 'ANT';
                        break;

                    case 'RZ':
                        $claseDocto = 'CONS';
                        break;

                    default:
                        $claseDocto = 'FACT';
                        break;
                }

                if ($historial['Validada'] == 1) {
                    $estatusFiscal = '<i class="fas fa-check text-default" data-toggle="tooltip" data-placement="top" title="No Se Valida Fiscalmente"></i>';
                } else {
                    $estatusFiscal = '<i class="fas fa-check text-success" data-toggle="tooltip" data-placement="top" title="Valida Fiscalmente"></i>';
                }

                $estatusContable = $historial['EstatusContable'];
                $basePago = $historial['BasePago'];
                $fehcaVence = $historial['FechaVence'];
                $msjContable = '';
                switch ($estatusContable) {
                    case '1':
                        $msjContable = 'En Espera De Validación';

                        if ($val['fechaPosibleVencimiento'] != '') {
                            $detContable = '<span>' . calcularFechaPago(json_encode($basePago), json_encode($fehcaVence)) . '</span>';
                        } else {
                            $detContable = '<center class="text-default-light"><i class="md md-query-builder"></i></center>';
                        }
                        $txtColor = '';
                        $bgColor = '';
                        break;
                    case '2':
                        if ($historial['BasePago'] == '' or $historial['FechaVence'] == '') {
                            if ($historial['FechaPosibleVencimiento'] != '') {
                                $detContable = '<span>' . calcularFechaPago(json_encode($historial['FechaRegistro']), json_encode($historial['FechaPosibleVencimiento'])) . '</span>';
                            } else {
                                $detContable = '<span class="text-success">En espera de Fecha de Pago</span>';
                            }
                        } else {
                            $detContable = '<span>' . calcularFechaPago(json_encode($basePago), json_encode($fehcaVence)) . '</span>';
                        }
                        $msjContable = 'Se A Contabilizado';
                        $txtColor = '';
                        $bgColor = '';
                        break;
                    case '3':
                        $msjContable = 'Esta Factura Fue Regresada';
                        $msjRegresa = $historial['ComentRegresa'];
                        $detContable = '<center class="text-danger"><a onclick="muestraMensaje(' . $historial['Acuse'] . ',\'' . $msjRegresa . '\');" ><i class="md md-reply"></i></a></center>';
                        $txtColor = 'text-danger';
                        $bgColor = 'danger';
                        break;
                    case '4':
                        $msjContable = 'Factura Eliminada Por El Usuario';
                        $detContable = '<center class="text-default-light"><i class="fa fa-close"></center>';
                        $txtColor = '';
                        $bgColor = '';
                        break;
                    default:
                        $msjContable = 'Estatus Contable No Identificado';
                        break;
                }
                $msjFinal = '<div class="tooltip-container">
                                <span>' . $detContable . '</span>
                                <div class="tooltip-text">
                                    <strong>Meta</strong>
                                    <p></p>
                                </div>
                            </div>';

            ?>
                <tr>
                    <td><?= $historial['Acuse']; ?></td>
                    <td><?= $claseDocto; ?></td>
                    <td><?= $historial['FechaReg']; ?></td>
                    <td><?= $historial['Pais']; ?></td>
                    <td><?= $historial['NombreProveedor']; ?></td>
                    <td><?= $historial['OC']; ?></td>
                    <td><?= $historial['Referencia']; ?></td>
                    <td><?= $historial['RfcReceptor']; ?></td>
                    <td><?= $historial['MontoFactura']; ?></td>
                    <td class="text-center"><?= $estatusFiscal; ?></td>
                    <td> <?= $msjFinal; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php
            }
            ?>

        </tbody>
    </table>
<?php
}
?>