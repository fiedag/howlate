
<link rel="stylesheet" href="/styles/modal.css">

<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<script>
  function openView(UDID) {
      
      var url = "http://m.<?php echo __DOMAIN; ?>/late?udid=" + UDID;
      $('#label-show').html(url);
      $('#late-view').load(url,function(){
      $('#label-show').text('Content loaded!');
   });
      window.location.href = "#modal-show";
  }


</script>

<div class='container primary-content'>
   <?php echo $xcrud_content; ?>
</div>
<?php $controller->get_footer(); ?>


<!-- A modal with its content -->
<section class="modal--show" id="modal-show"
         tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

    <div class="modal-inner">
        <header>
            <h2 id="label-show">This is what the device lateness view is</h2>
        </header>

        <div class="modal-content" id="modal-content">
            <div id="late-view"></div>
        </div>
    </div>

    <footer>
        <p>Footer</p>
        <div id="error"></div>
    </footer>
</div>
<a href="#!" class="modal-close" title="Close this modal"
   data-dismiss="modal" data-close="Close">&times;</a>
</section>


