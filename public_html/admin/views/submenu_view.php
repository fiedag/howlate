<div id="subnav" class="fresh-header">
    <div class="container">
        <ul class>

            <?php
                foreach($submenu as $key=>$value) {
            ?>
            <li>
                <span>
                    <a class="custom-background-fixed-lightness-95<?php echo ($key==$view_name)?'':'-hover';?> custom-font-on-white" href="/tranlog/<?php echo $key; ?>" id="nav-sub-bps">
                        <span><?php echo $value; ?></span></a>
                </span>
            </li> 

            <?php
                }
            ?>
            
        </ul>
    </div>
</div>