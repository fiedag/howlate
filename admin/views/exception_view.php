<?php $controller->get_header(); ?>

        <div class='container primary-content'>

            <h3>
                <?php echo $sorry; ?>
            </h3>
            <div>
                <?php echo $sorry2; ?>
            </div>
            <div>
                <?php echo $class; ?>   <b> <?php echo $message; ?> </b>
            </div>
            <div>
                <?php echo "$file ($line)"; ?>

            </div>

        </div>
<?php $controller->get_footer(); ?>