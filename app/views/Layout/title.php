<?php
if ($debug == 1) {
    echo '<br><br>Contenido de datosPagina:';
    var_dump($datosPagina);
    echo '<br><br>Contenido de rutaMenu:'.$rutaMenu;
}

function generarBreadcrumb($rutaMenu, $menuActual)
{
    // Divide la ruta en partes
    $partes = explode('/', $rutaMenu);
    
    // Inicia el HTML del breadcrumb
    $breadcrumbHtml = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

    // Recorre cada parte de la ruta y construye el HTML
    $totalPartes = count($partes);
    foreach ($partes as $index => $parte) {
        // Si es la última parte, no incluye el enlace
        $statusBreadcrumb = ($menuActual == $parte) ? 'active' : '' ;
        if ($index === $totalPartes - 1) {
            $breadcrumbHtml .= '<li class="breadcrumb-item '.$statusBreadcrumb.'" aria-current="page">' . htmlspecialchars($parte) . '</li>';
        } else {
            $breadcrumbHtml .= '<li class="breadcrumb-item '.$statusBreadcrumb.'"><a href="#">' . htmlspecialchars($parte) . '</a></li>';
        }
    }

    // Cierra el HTML del breadcrumb
    $breadcrumbHtml .= '</ol></nav>';

    return $breadcrumbHtml;
}
?>
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-5 align-self-center">
                        <h4 class="page-title"><?=$datosPagina['menu_nombre'];?></h4>
                        <h4><?=$datosPagina['menu_descripcion'];?></h4>
                    </div>
                    <div class="col-7 align-self-center">
                        <div class="d-flex align-items-center justify-content-end">
                            <?=generarBreadcrumb($rutaMenu, $datosPagina['menu_nombre']);?>
                        </div>
                    </div>
                </div>
            </div>