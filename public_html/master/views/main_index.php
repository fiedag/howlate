<?php $controller->get_header(); ?>

<link rel="stylesheet" href="/styles/modal.css">

<!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/bean.js"></script>
<![endif]-->



<script>

    function lateHelper(pin) {
        
        msg = document.getElementById('latemsg[' + pin + ']').value;
        document.getElementById('lateness[' + pin + ']').title = "This will be displayed as '" + msg + "'"; 
    }

    function lateHelperOld(pin) {
        curtime = document.getElementById('lateness[' + pin + ']').value;
        tonearest = document.getElementById('tonearest[' + pin + ']').value;
        threshold = document.getElementById('threshold[' + pin + ']').value;
        offset = document.getElementById('offset[' + pin + ']').value;
        ceiling = document.getElementById('ceiling[' + pin + ']').value;
        if (tonearest == 0) { tonearest = 1; }
        rounded = tonearest * Math.round(curtime / tonearest);
        if (rounded < threshold) { 
            result = "on time"; 
        } else {
            result = rounded - offset; 
            result = result + ' minutes late.';
        }
  
        document.getElementById('lateness[' + pin + ']').title = "This will be displayed as " + result;
  
    }


    function changeCursor(el, cursor) {
        el.style.cursor = cursor;
    }

    function gotoInvite(invitepin, PractitionerName) {
        //document.getElementById(pin).innerHTML = document.getElementById(pin).innerHTML + "*"; 
        document.getElementById('invitepin').value = invitepin;
        document.getElementById('PractitionerName').innerHTML = PractitionerName;
        window.location.href = "#modal-show";
    }

    function leadingZero(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }
    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        // add a zero in front of numbers<10
        m = leadingZero(m);
        s = leadingZero(s);
        document.getElementById('clinictime').innerHTML = h + ":" + m + ":" + s;
        t = setTimeout(function() {
            startTime()
        }, 1000);
    }

    function saved_ok_indicator() {
        document.getElementById('saved_indicator').innerHTML = "Saved Ok";
        t = setTimeout(function() {
            document.getElementById('saved_indicator').innerHTML = ""
        }, 1000);
    }

</script>

<div class='container primary-content'>
    <div class="clinic-header">
        <form name="clinics" id="clinics" action="/main/setclinic" method="post">
            <select name="selectedclinic" id="selectedclinic" class="dropdown" onchange="this.form.submit();" value="<?echo $controller->currentClinicName; ?>" >
<?php $controller->get_clinic_options(); ?>
            </select>
            <span ><?php echo date('h:i:s A') . " (" . $controller->currentClinicTimezone . ")"; ?> </span>
        </form>

    </div>

<?php $controller->show_lateness_form(); ?>

<?php if (isset($saved_ok)) {
    echo '<script>saved_ok_indicator();</script>';
} ?>
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
                                <h2>Invite a smart-phone user to updates for <p id="PractitionerName">Drs name goes here</p></h2>
                                <p>Please enter a mobile phone number to send an invitation to</p>
                                <form name="invite" action="/main/invite" method='POST'>
                                    <input type="hidden" id="invitepin" name="invitepin" readonly='readonly'>
                                    Mobile:<input type="text" id="udid" name="udid">
                                    <input type="submit" id="submit" name="submit" value="Invite">
                                </form>
                            </div>
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
