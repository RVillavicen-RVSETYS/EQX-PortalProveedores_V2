
<header class="topbar">

    <!-- Mobile Header -->
    <div class="wsmobileheader clearfix ">
        <a id="wsnavtoggle" class="wsanimated-arrow"><span></span></a>
        <span class="smllogo"><img src="/assets/menu/images/logo-equinox-gold-Mobil.png" width="80%" alt="" /></span>
    </div>
    <!-- Mobile Header -->

    <div class="wsmainfull clearfix">
        <div class="wsmainwp wsmain clearfix">


            <div class="desktoplogo"><a href="#"><img src="/assets/menu/images/logo-equinox-gold-Desktop.png" width="80%" alt=""></a></div>
            <!--Main Menu HTML Code-->
            <nav class="wsmenu clearfix">
                <ul class="wsmenu-list">

                    <?= $funcionMenu['menuHtml']; ?>
                    <?= generaSeccionUserMenu($areaData['data'], $areaLink); ?>

                </ul>
            </nav>

            <!--Menu HTML Code-->
        </div>
    </div>

</header>

<?php

?>