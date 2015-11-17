<div class="container">
    <div class="navbar">
        <ul class="nav nav-tabs">

            <?php
            foreach ($submenu as $key => $value) {
                
                ?>
            
                <li class="<?php  if ($key == $view_name) {echo 'active';} ?>">
                        <a href="/<?php echo $controller->getsimplename(); ?>/<?php echo $key; ?>" id="nav-sub-bps">
                            <span><?php echo $value; ?></span></a>
                </li>

                <?php
            }
            ?>

        </ul>
    </div>
</div>
