<?php
$rutaManual = 'Manuales\Manual_de_Portal_proveedores.pdf';
$urlPDF = base64_encode($rutaManual);
?>
<div id="note-full-container" class="note-has-grid row">
    <div class="col-md-4 single-note-item all-category">
        <div class="card card-body">
            <span class="side-stick"></span>
            <h5 class="note-title text-truncate w-75 mb-0">Manual de Carga de Facturas <i class="point fas fa-circle ml-1 font-10"></i></h5>
            <p class="note-date font-12 text-muted">10/09/2026</p>
            <div class="note-content mb-3">
                <p class="note-inner-content text-muted">Manual para el uso del portal proveedores.</p>
            </div>

            <!-- Botón de Fancybox -->
            <button data-fancybox
                data-type="pdf"
                data-preloader="true"
                data-src="/ProveedorNacional/SoporteProveedor/verDocumento/PDF/<?= $urlPDF; ?>/#toolbar=1"
                class="btn btn-outline-success btn-sm">
                <i class="far fa-file-pdf mr-1"></i> Ver Manual
            </button>
        </div>
    </div>
</div>

<script>
    // Inicializa Fancybox sobre el botón que acaba de cargarse
    Fancybox.bind("[data-fancybox]", {
        dragToClose: false,
        click: "close",
        pdf: {
            iframe: {
                preload: false
            }
        }
    });
</script>