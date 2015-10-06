<?php $controller->get_header(); ?>

<link rel="stylesheet" href="/styles/modal.css">

<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<script>
  function openView(UDID) {
      var url = "http://m.<?php echo __DOMAIN; ?>/late/view?udid=" + UDID;
      window.open(url,"_blank");
  }


</script>


<div class='container primary-content'>

<?php $controller->getXcrudTable(); ?>
    
    
    
</div>




<?php $controller->get_footer(); ?>


