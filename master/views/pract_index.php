<?php $controller->get_header(); ?>

<link rel="stylesheet" href="/styles/modal.css">

<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<style>
    .notwide { width:280px;}

</style>


<div class='container primary-content'>
    <?php $controller->getXcrudTable(); ?>
</div>


<script>
    $(document).ready(function() {
        $('.edit').editable('pract/saveassign', {
            tooltip: "Click to reassign or unassign",
            data: <?php echo $clinic_json; ?>,
            type: 'select',
            submit: 'Save'
        });
    });



</script>




<?php $controller->get_footer(); ?>


