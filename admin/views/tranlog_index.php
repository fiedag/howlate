<script>
  function openView(UDID) {
      debugger;
      
      var url = "http://m.<?php echo __DOMAIN; ?>/late?udid=" + UDID;
      window.open(url,'_blank');
  }


</script>
<div class='container primary-content'>
   <?php echo $xcrud_content; ?>
</div>
<?php $controller->get_footer(); ?>
