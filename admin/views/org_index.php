<?php $controller->get_header(); ?>


<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<script>
  function gotoSite(url) {
      window.open(url,'_blank');
  }


  function deleteSubd(subd) {
      if (confirm("Are you sure you wish to delete the subdomain from cPanel?")) {
          if (confirm("Are you REALLY sure????")) {
              $("#button_" + subd).text("Deleting...");
              var delURL = "https://admin.how-late.com/org/delete?subd=" + subd;
              $.get(delURL, function(result) {
                  $("#button_" + subd).text(result);
                });
          }
      }
  }
</script>



<div class='container primary-content'>
   <?php echo $xcrud_content; ?>
</div>
<?php $controller->get_footer(); ?>





