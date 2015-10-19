<?php $controller->get_header(); ?>

<link rel="stylesheet" href="/styles/modal.css">

<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->

<style>
    .notwide { width:60px;}
    
</style>

<div class='container'>
    <div class="panel panel-primary">

        <div class="navbar">
            <ul class="nav nav-tabs">
                <?php
                foreach ($controller->Organisation->ActiveClinics as $key => $value) {
                    ?>
                    <li class="<?php
                    if ($controller->currentClinicName == $value->ClinicName) {
                        echo 'active';
                    }
                    ?>"><a href="/main/setclinic?clinic=<?php echo $value->ClinicID; ?>"><?php echo $value->ClinicName; ?></a></li>
                        <?php
                    }
                    ?>
            </ul>            
        </div>

        <?php $controller->show_lateness_form(); ?>
    </div>

    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;<span id="clinictime"><?php echo date('H:i:s A');?> </span>&nbsp;<span><?php echo "(" . $controller->currentClinicTimezone . ")"; ?> </span>
    </div>

</div>

<!-- A modal with its content -->
<section class="modal--show" id="modal-show"
         tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

    <div class="modal-inner">
        <header>
            <h2 id="label-show">Enter a mobile number</h2>
        </header>

        <div class="modal-content">
            <div> 
                <h2>Invite a smart-phone user to updates for <p id="modal-name">Drs name goes here</p></h2>
                <p>Please enter a mobile phone number to send an invitation to</p>
                <form name="invite" action="/main/invite" method='POST'>
                    <input type="text" id="modal-invitepin" name="invitepin" readonly='readonly'>
                    Mobile:<input type="text" id="udid" name="udid">
                    <input type="submit" id="submit" name="submit" value="Invite">
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>Footer</p>
    </footer>
    <a href="#!" class="modal-close" title="Close this modal" data-dismiss="modal" data-close="Close">&times;</a>
</section>


<script src="/js/jquery.jeditable.min.js"></script>

<script>
    function myPad(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }
    function updateClinicTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        // add a zero in front of numbers<10
        m = myPad(m);
        s = myPad(s);
        $('#clinictime').text(h + ":" + m + ":" + s);
        t = setTimeout(function() {
            updateClinicTime();
        }, 1000);
    }

    updateClinicTime();

    $(document).ready(function() {
        $('.edit').editable('https://<?php echo __FQDN; ?>/main/save', {
            indicator: ' ',
            tooltip: ' '
        });

        $('.chekbox').change(function() {
            id = $(this).attr("id");
            late = $('.form-control, #' + id).html();
            sticky = ($('input[name="' + id + '"]:checked').val() == 'on') ? 1 : 0;
            $.post("/main/savechk", {
                id: $(this).attr("id"),
                late: late,
                sticky: sticky
            })
                    .done(function(data) {
                        //alert("Data Loaded: " + data);
                    });
        });

        $('.btn-invite').click(function() {
            invitepin = $(this).data("invitepin");
            fullname = $(this).data("fullname");

            $('#modal-invitepin').val(invitepin);
            $('#modal-name').html(fullname);
            window.location.href = "#modal-show";
        });
    });
</script>


<?php $controller->get_footer(); ?>
