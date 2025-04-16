<?php

function generarMenu($menuData, $linkActual)
{
    $menuHtml = '';
    $menus = [];
    $datosPagina = [];
    $rutaMenu = '';

    // Agrupamos los datos de acuerdo a menu_id para construir la estructura correctamente
    foreach ($menuData as $item) {
        $menuId = $item['menu_id'];
        $submenu1Id = $item['submenu1_id'] ?? null;
        $submenu2Id = $item['submenu2_id'] ?? null;
        $submenu3Id = $item['submenu3_id'] ?? null;

        // Si el menú principal no existe en el arreglo, lo inicializamos
        if (!isset($menus[$menuId])) {
            $menus[$menuId] = [
                'menu' => [
                    'id' => $menuId,
                    'nombre' => $item['menu_nombre'],
                    'descripcion' => $item['menu_descripcion'],
                    'icono' => $item['menu_icono'],
                    'link' => $item['menu_link'],
                    'tipo' => $item['menu_tipo'],
                    'visible' => $item['menu_visible']
                ],
                'submenus' => []
            ];
        }

        // Agregar el submenú de primer nivel si existe
        if ($submenu1Id) {
            if (!isset($menus[$menuId]['submenus'][$submenu1Id])) {
                $menus[$menuId]['submenus'][$submenu1Id] = [
                    'nombre' => $item['submenu1_nombre'],
                    'descripcion' => $item['submenu1_descripcion'],
                    'icono' => $item['submenu1_icono'],
                    'link' => $item['submenu1_link'],
                    'visible' => $item['submenu1_visible'],
                    'submenus' => []
                ];
            }

            // Agregar el submenú de segundo nivel si existe
            if ($submenu2Id) {
                if (!isset($menus[$menuId]['submenus'][$submenu1Id]['submenus'][$submenu2Id])) {
                    $menus[$menuId]['submenus'][$submenu1Id]['submenus'][$submenu2Id] = [
                        'nombre' => $item['submenu2_nombre'],
                        'descripcion' => $item['submenu2_descripcion'],
                        'icono' => $item['submenu2_icono'],
                        'link' => $item['submenu2_link'],
                        'visible' => $item['submenu2_visible'],
                        'submenus' => []
                    ];
                }

                // Agregar el submenú de tercer nivel si existe
                if ($submenu3Id) {
                    $menus[$menuId]['submenus'][$submenu1Id]['submenus'][$submenu2Id]['submenus'][$submenu3Id] = [
                        'nombre' => $item['submenu3_nombre'],
                        'descripcion' => $item['submenu3_descripcion'],
                        'icono' => $item['submenu3_icono'],
                        'link' => $item['submenu3_link'],
                        'visible' => $item['submenu3_visible']
                    ];
                }
            }
        }
    }

    // Ahora generamos el HTML del menú a partir de la estructura agrupada
    foreach ($menus as $menu) {
        $menuInfo = $menu['menu'];
        $menuTipo = $menuInfo['tipo'];
        $isActive = ($menuInfo['link'] === $linkActual) ? 'active' : '';
        $menuLink = !empty($menuInfo['link']) ? htmlspecialchars($menuInfo['link']) : '#';

        // Si el menú principal coincide con $linkActual, asignamos datos de página y ruta
        if ($menuInfo['link'] === $linkActual) {
            $datosPagina = [
                'menu_nombre' => $menuInfo['nombre'],
                'menu_descripcion' => $menuInfo['descripcion']
            ];
            $rutaMenu = $menuInfo['nombre'];
        }

        if ($menuInfo['visible'] == 0) {
            continue;
        }

        // Generación del menú principal según el tipo
        switch ($menuTipo) {
            case 1:
                $menuHtml .= '<li aria-haspopup="true"><a href="' . $menuLink . '" class="menuhomeicon ' . $isActive . '"><i class="' . htmlspecialchars($menuInfo['icono']) . '"></i></a></li>';
                break;

            case 2:
                $menuHtml .= '<li aria-haspopup="true"><a href="' . $menuLink . '" class="' . $isActive . '"><i class="' . htmlspecialchars($menuInfo['icono']) . '"></i>' . htmlspecialchars($menuInfo['nombre']) . '</a></li>';
                break;

            case 3:
                // Revisamos si algún submenú coincide con el link actual para activar el menú y submenús
                $subMenuActive = $isActive; // Puede ser 'active' si el menú principal coincide
                $currentRuta = $menuInfo['nombre']; // Inicializamos la ruta del menú

                // Generación de submenús de primer nivel
                $subMenuHtml = '<ul class="sub-menu">';
                foreach ($menu['submenus'] as $submenu1) {
                    $isSubmenu1Active = ($submenu1['link'] === $linkActual) ? 'active' : '';
                    $submenu1Link = !empty($submenu1['link']) ? htmlspecialchars($submenu1['link']) : '#';

                    // Si el submenú1 coincide con $linkActual, asignamos datos de página y ruta
                    if ($submenu1['link'] === $linkActual) {
                        $datosPagina = [
                            'menu_nombre' => $submenu1['nombre'],
                            'menu_descripcion' => $submenu1['descripcion']
                        ];
                        $rutaMenu = $currentRuta . '/' . $submenu1['nombre'];
                    }

                    // Generación de submenús de segundo nivel
                    $subSubMenuHtml = '';
                    if (!empty($submenu1['submenus'])) {
                        $subSubMenuHtml .= '<ul class="sub-menu">';
                        foreach ($submenu1['submenus'] as $submenu2) {
                            $isSubmenu2Active = ($submenu2['link'] === $linkActual) ? 'active' : '';
                            $submenu2Link = !empty($submenu2['link']) ? htmlspecialchars($submenu2['link']) : '#';

                            // Si el submenú2 coincide con $linkActual, asignamos datos de página y ruta
                            if ($submenu2['link'] === $linkActual) {
                                $datosPagina = [
                                    'menu_nombre' => $submenu2['nombre'],
                                    'menu_descripcion' => $submenu2['descripcion']
                                ];
                                $rutaMenu = $currentRuta . '/' . $submenu1['nombre'] . '/' . $submenu2['nombre'];
                            }

                            // Generación de submenús de tercer nivel
                            $subSubSubMenuHtml = '';
                            if (!empty($submenu2['submenus'])) {
                                $subSubSubMenuHtml .= '<ul class="sub-menu">';
                                foreach ($submenu2['submenus'] as $submenu3) {
                                    $isSubmenu3Active = ($submenu3['link'] === $linkActual) ? 'active' : '';
                                    $submenu3Link = !empty($submenu3['link']) ? htmlspecialchars($submenu3['link']) : '#';

                                    // Si el submenú3 coincide con $linkActual, asignamos datos de página y ruta
                                    if ($submenu3['link'] === $linkActual) {
                                        $datosPagina = [
                                            'menu_nombre' => $submenu3['nombre'],
                                            'menu_descripcion' => $submenu3['descripcion']
                                        ];
                                        $rutaMenu = $currentRuta . '/' . $submenu1['nombre'] . '/' . $submenu2['nombre'] . '/' . $submenu3['nombre'];
                                    }

                                    $subSubSubMenuHtml .= '<li aria-haspopup="true"><a href="' . $submenu3Link . '" class="' . $isSubmenu3Active . '"><i class="' . htmlspecialchars($submenu3['icono']) . '"></i>' . htmlspecialchars($submenu3['nombre']) . '</a></li>';
                                }
                                $subSubSubMenuHtml .= '</ul>';
                            }

                            $subSubMenuHtml .= '<li aria-haspopup="true"><a href="' . $submenu2Link . '" class="' . $isSubmenu2Active . '"><i class="' . htmlspecialchars($submenu2['icono']) . '"></i>' . htmlspecialchars($submenu2['nombre']) . '</a>' . $subSubSubMenuHtml . '</li>';
                        }
                        $subSubMenuHtml .= '</ul>';
                    }

                    $subMenuHtml .= '<li aria-haspopup="true"><a href="' . $submenu1Link . '" class="' . $isSubmenu1Active . '"><i class="' . htmlspecialchars($submenu1['icono']) . '"></i>' . htmlspecialchars($submenu1['nombre']) . '</a>' . $subSubMenuHtml . '</li>';
                }
                $subMenuHtml .= '</ul>';

                $menuHtml .= '<li aria-haspopup="true"><a href="' . $menuLink . '" class="' . $subMenuActive . '"><i class="' . htmlspecialchars($menuInfo['icono']) . '"></i>' . htmlspecialchars($menuInfo['nombre']) . '<span class="wsarrow"></span></a>';
                $menuHtml .= $subMenuHtml . '</li>';
                break;
        }
    }

    // Retorno de los tres valores solicitados
    return [
        'menuHtml' => $menuHtml,
        'datosPagina' => $datosPagina,
        'rutaMenu' => $rutaMenu
    ];
}

function generaSeccionUserMenu($areaData, $areaLink)
{
    $Admin = (isset($_SESSION['EQXAdmin'])) ? $_SESSION['EQXAdmin'] : 0;

    // Convertimos la ruta en arreglo para identificar el IDIOMA
    $requestUri = $_SERVER['REQUEST_URI'];
    $cleanUri = parse_url($requestUri, PHP_URL_PATH);
    $piezasURL = explode('/', trim($cleanUri, '/'));

    switch ($Admin) {
        case '1':
            $linkPerfil = URL_BASE_PROYECT . '/Administrador/MiCuenta';
            $linkSoporte = URL_BASE_PROYECT . '/Administrador/soporteMamalon';
            $linkLogout = URL_BASE_PROYECT . '/logoutAdmin.php';
            $nameUser = $_SESSION['EQXnombreUserCto'];
            $subText = $_SESSION['EQXnombreUser'];
            $textSoporte = 'Centro de Ayuda';
            $textlogout = 'Cierra Sessión';
            $textPerfil = 'Ver Perfil';
            break;

        default:
            $linkPerfil = 'MiPerfil';
            $linkSoporte = 'soporteProveedor';
            $linkLogout = URL_BASE_PROYECT . '/logout.php';
            $nameUser = $_SESSION['EQXnombreUserCto'];
            $subText = $_SESSION['EQXcorreo'];

            $urlIdioma = __DIR__ . '/Idiomas/' . $piezasURL[0] . '/' . $_SESSION['EQXidioma'] . '.php';
            //echo '<br>Ruta de Idioma: '.$urlIdioma;
            //echo '<br>Ruta Actual: '.__DIR__;
            require_once($urlIdioma);

            $menuModel = new Idiomas($piezasURL[1]);

            $textSoporte = $menuModel->txt('Centro_Ayuda', 1);
            $textlogout = $menuModel->txt('Cerrar_Session', 1);
            $textPerfil = $menuModel->txt('Ver_Perfil', 1);
            break;
    }

    $arsLinks = '';

    // Agrupamos los datos de acuerdo a menu_id para construir la estructura correctamente
    foreach ($areaData as $item) {
        $estatus = ($item['link'] == $areaLink) ? 'active' : '';
        $arsLinks .= '
		    <li><a class="' . $estatus . '" href="' . URL_BASE_PROYECT . '/' . $item['link'] . '/' . $item['menu_link'] . '"><i class="' . $item['icono'] . '"></i> ' . $item['nombre'] . '</a></li>';
    }

    ?>
    <li aria-haspopup="true"><a href="#"><i class="fas fa-user-tie"></i><?= $nameUser; ?><span class="wsarrow"></span>
        </a>
        <ul class="sub-menu sub-session">
            <div class="d-flex no-block align-items-center p-15 bg-pyme-secundary text-white m-b-5">
                <div class="">
                    <img src="/assets/images/users/avatar.jpg" alt="user" class="rounded-circle" width="60">
                </div>
                <div class="m-l-10">
                    <h4 class="m-b-0"><b><?= $_SESSION['EQXnombreNivel']; ?></b></h4>
                    <p class=" m-b-0"><?= $subText; ?></p>
                </div>
            </div>
            <?= $arsLinks; ?>
            <hr>
            <li><a id="menuMiPerfil" href="<?=$linkPerfil;?>"><i class="fas fa-user-tie"></i> <?= $textPerfil;?></a></li>
            <li><a id="menuCentroAyuda" href="<?= $linkSoporte;?>"><i class="fas fa-question-circle"></i> <?= $textSoporte; ?></a></li>
            <hr>
            <li><a href="<?= $linkLogout; ?>"><i class="fas fa-sign-out-alt"></i> <?= $textlogout; ?></a></li>
        </ul>
    </li>
    <?php


}

function generaNotificacionStatica($tipoMensaje, $titulo, $mensaje){
    switch ($tipoMensaje) {
        case 'INFO':
            $noti = '
                <div class="col-12">
                    <div class="alert alert-info">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-info"><i class="fa fa-exclamation-circle"></i> '.$titulo.'</h3> '.$mensaje.'
                    </div>
                </div>
                    ';
            break;
    
        case 'WARNING':
            $noti = '
                <div class="col-12">
                    <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> '.$titulo.'</h3> '.$mensaje.'
                    </div>
                </div>
                    ';
            break;
        
        case 'ERROR':
            $noti = '
                <div class="col-12">
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-danger"><i class="fas fa-ban"></i> '.$titulo.'</h3> '.$mensaje.'
                    </div>
                </div>
                    ';
            break;
    }

    return $noti;
}
?>