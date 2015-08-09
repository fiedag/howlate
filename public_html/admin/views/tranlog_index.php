

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


<!-- A modal with its content -->
<section class="modal--show" id="modal-show"
         tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

    <div class="modal-inner">
        <header>
            <h2 id="label-show">This is what the device lateness view is</h2>
        </header>

        <div class="modal-content" id="modal-content">
            <iframe id="iframe"></iframe>
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
