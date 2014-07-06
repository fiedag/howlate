
<?php $controller->get_header(); ?>

<script>


    function changeCursor(el, cursor) {
        el.style.cursor = cursor;
    }

    function gotoInvite(pin, prac) {
        //document.getElementById(pin).innerHTML = document.getElementById(pin).innerHTML + "*"; 
        document.getElementById('invitepin').value = pin;
        document.getElementById('inviteprac').innerHTML = prac;
        window.location.href = "#invite";
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
<?php $controller->get_valid_lateness_datalist(); ?>

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

<div id="invite" class="modalDialog">
    <div> 
        <a href="#close" title="Close" class="close">X</a>
        <h2>Invite a smartphone user to updates for <p id="inviteprac">Drs name goes here</p></h2>
        <p>Please enter a mobile phone number to send an invitation to</p>
        <form name="invite" action="/main/invite" method='GET'>
            <input type="hidden" id="invitepin" name="invitepin" readonly='readonly'>
            Mobile:<input type="text" id="udid" name="udid">
            <input type="submit" id="submit" name="submit" value="Invite">
        </form>
    </div>
</div>

<?php $controller->get_footer(); ?>
