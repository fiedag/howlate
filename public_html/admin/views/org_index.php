<?php $controller->get_header(); ?>


<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<script>
  function gotoSite(url) {
      window.open(url,'_blank');
  }


</script>



<div class='container primary-content'>
   <?php echo $xcrud_content; ?>
</div>
<?php $controller->get_footer(); ?>





