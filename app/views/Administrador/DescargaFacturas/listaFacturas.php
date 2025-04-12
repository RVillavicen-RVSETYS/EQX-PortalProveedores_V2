<?php
$debug = 0;

if ($debug == 1) {
    echo '<br><br>Contenido de datosPagina:';
    var_dump($listaCompras);
}

if (empty($listaCompras)) {
    echo '<div class="alert alert-info">No se encontraron facturas, por favor filtre por otro rango de fechas. </div>';
    exit(0);
}

?>

<div class="row">
    <div class="col-12 col-md-3 mb-4 my-1">
        <button type="button" onclick="descargarAcuses();" class="btn btn-sm waves-effect waves-light btn-outline-success"><i class="fas fa-download"></i> Descargar Facturas</button>
    </div>
</div>

<table class="table table-sm table-striped search-table v-middle">
    <thead class="header-item">
        <th>
            <div class="n-chk align-self-center text-center">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="check-all-facturas">
                    <label class="custom-control-label" for="check-all-facturas"></label>
                    <span class="new-control-indicator"></span>
                </div>
            </div>
        </th>
        <th class="text-dark font-weight-bold">Acuse</th>
        <th class="text-dark font-weight-bold">Proveedor</th>
        <th class="text-dark font-weight-bold">Orden Compra</th>
        <th class="text-dark font-weight-bold">Monto</th>
        <th class="text-dark font-weight-bold">Folio</th>
        <th class="text-dark font-weight-bold">Fecha Recepción</th>
        <th class="text-dark font-weight-bold">Factura</th>
        <th class="text-dark font-weight-bold">Complemento</th>
        <th class="text-dark font-weight-bold">Nota Credito</th>
        <th class="text-dark font-weight-bold">Descargar</th>
    </thead>
    <tbody>
        <?php
        foreach ($listaCompras as $facturas) {

            $conFactura = 'fas fa-check text-success';

            $conComplemento = ($facturas['CantComplementos'] > 0) ? 'fas fa-check text-success' : 'fas fa-times text-danger';

            $conNotaCredito = ($facturas['NotaCredito'] == 1) ? 'fas fa-check text-success' : 'fas fa-times text-danger';


        ?>
            <tr class="search-items">
                <td>
                    <div class="n-chk align-self-center text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input factura-chkbox" id="checkbox<?= $facturas['acuse']; ?>">
                            <label class="custom-control-label" for="checkbox<?= $facturas['acuse']; ?>"></label>
                        </div>
                    </div>
                </td>
                <td class="text-right"><?= $facturas['acuse']; ?></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="ml-2">
                            <div class="user-meta-info">
                                <h5 class="user-name mb-0"><?= $facturas['RazonSocial']; ?></h5>
                                <span class="user-work text-muted"><?= $facturas['RFC']; ?></span>
                            </div>
                        </div>
                    </div>
                </td>
                <td><span><?= $facturas['ordenCompra']; ?></span></td>
                <td class="text-right"><span>$ <?= number_format($facturas['Total'], 2, '.', ','); ?></span></td>
                <td class="text-right"><span><?= $facturas['SerieFact']; ?><?= $facturas['FolioFact']; ?></span></td>
                <td><span><?= $facturas['FechaReg']; ?></span></td>
                <td class="text-center"><i class="<?= $conFactura; ?>"></i></td>
                <td class="text-center"><i class="<?= $conComplemento; ?>"></i></td>
                <td class="text-center"><i class="<?= $conNotaCredito; ?>"></i></td>
                <td class="text-center">
                    <button type="button" onclick="descargarAcuse(<?= $facturas['acuse']; ?>);" class="btn btn-sm waves-effect waves-light btn-outline-success">
                        <i class="fas fa-download"></i>
                    </button>
                </td>
            </tr>
        <?php
        }
        ?>

    </tbody>
</table>

<script>
    function descargarAcuse(acuse) {
        const url = `DescargaFacturas/descargarAcuses?acuses=${acuse}`;
        window.open(url, '_blank');
    }

    function descargarAcuses() {
        const checkboxes = document.querySelectorAll('.factura-chkbox:checked');
        const acuses = Array.from(checkboxes).map(cb => cb.id.replace('checkbox', ''));
        if (acuses.length > 0) {
            const url = `DescargaFacturas/descargarAcuses?acuses=${acuses.join(',')}`;
            window.open(url, '_blank');
        }else {
            notificaBad('No se seleccionaron facturas.');
        }
    }

    document.getElementById('check-all-facturas').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.factura-chkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    });
</script>