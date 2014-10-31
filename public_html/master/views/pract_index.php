<?php $controller->get_header(); ?>

<link rel="stylesheet" href="/styles/modal.css">

<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<script>
  function gotoAssign(orgid,surrogKey, practID) {
      document.getElementById('assignorgid').value = orgid;
      document.getElementById('assignsurrogkey').value = surrogKey;
      document.getElementById('assignpractid').value = practID;
     
      window.location.href = "#modal-show";
  }


</script>



<div class='container primary-content'>

<?php $controller->getXcrudTable(); ?>
    
    
</div>

<!-- A modal with its content -->
<section class="modal--show" id="modal-show"
         tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

    <div class="modal-inner">
        <header>
            <h2 id="label-show">Assign this practitioner to a new clinic</h2>
        </header>

        <div class="modal-content">
            <form class="" name="assign" action="/pract/assign" method='POST'>
                <input type="hidden" id="assignorgid" name="assignorgid" readonly='readonly'>
                <input type="hidden" id="assignsurrogkey" name="assignsurrogkey" readonly='readonly'>
                <input type="hidden" id="assignpractid" name="assignpractid" readonly='readonly'>
                <select name="selectedclinic" id="selectedclinic" class="dropdown" >
                    <?php $controller->get_clinic_options(); ?>
                </select>
                <input type="submit" id="submit" name="submit" value="Assign">
            </form>



        </div>
    </div>

    <footer>
        <p>Footer</p>
    </footer>
</div>
<a href="#!" class="modal-close" title="Close this modal"
   data-dismiss="modal" data-close="Close">&times;</a>
</section>




<?php $controller->get_footer(); ?>


