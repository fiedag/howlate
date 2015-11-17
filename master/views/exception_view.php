<?php $controller->get_header(); ?>

        <div class='container primary-content'>

            <h3>
                <span class="glyphicon glyphicon-exclamation-sign">  &nbsp;</span><?php echo $sorry; ?>
            </h3>
            <div>
                <?php echo $sorry2; ?>
            </div>
            <br>
            <div>
                <b> <?php echo $class; ?> </b>  <i> <?php echo $message; ?> </i>
            </div>
            <br>
            <div>
                <?php echo "$file ($line)"; ?>

            </div>

        </div>
<?php $controller->get_footer(); ?>