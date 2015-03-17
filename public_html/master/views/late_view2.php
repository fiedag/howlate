<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <link rel="apple-touch-icon" href="<?php echo $apple_icon_url; ?>" >
        <link rel="apple-touch-icon-precomposed" href="<?php echo $apple_icon_url; ?>" />
        <link href="/lib/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
        <link href="/styles/smslate.css" type="text/css" rel="stylesheet">

        <link rel="stylesheet" href="/styles/modal.css">

        <script type="text/javascript" src="/js/bookmark_bubble.js"></script>
        <script type="text/javascript" src="/js/bookmark_bubble_example.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script>
            /* use JQuery to assemble a GET parameter comprising comma-separated pins we need to refresh
             * then we make an AJAX call to get these latenesses which is returned as a JSON string
             * This JSON string is used to update the spans with the corresponding ID
             */

            (function($, refreshInMs, refreshUrl) {

                function formatTime(date) {
                    return date.getHours() + ":" + pad(date.getMinutes()) + ":" + pad(date.getSeconds());
                }

                function pad(n) {
                    return ("0" + n).slice(-2);
                }

                function latenessToCssClass(lateValue) {
                    if (lateValue === 'off duty') {
                        return "offduty";
                    }
                    if (lateValue === 'on time') {
                        return "ontime";
                    }
                    if (lateValue.match(/[0-9]{1,2} minutes late/g)) {
                        return "bitlate";
                    }
                    if (lateValue.match(/1 hour.*/)) {
                        return "late";
                    }
                    return "verylate";
                }

                (function refresh() {
                    if (arguments.length === 0) {
                        $.getJSON(refreshUrl).then(function(result) {
                            $.each(result, function(name, lateValue) {
                                var el$ = $("#" + name.replace(".", "\\."));
                                el$.text(lateValue);
                                el$.parent().removeClass('offduty ontime bitelate late verylate').addClass(latenessToCssClass(lateValue));
                            });
                            window.setTimeout(refresh, refreshInMs);
                        });
                    }
                    $("#localtime").text(formatTime(new Date()));
                })();

            })($, '<?php echo $refresh; ?>', '<?php echo $refresh_url; ?>');

            function showMessage(OrgID, ClinicID, PractitionerID, PractitionerName, UDID) {
                $("#PractitionerName").text(PractitionerName);
                $('input[name=ClinicID]').val(ClinicID);
                $('input[name=OrgID]').val(OrgID);
                $('input[name=PractitionerID]').val(PractitionerID);
                $('input[name=PractitionerName2]').val(PractitionerName);
                $('input[name=UDID]').val(UDID);
                
                window.location.href = "#modal-show";
            }




        </script>
    </head>
    <body>
        <div class="container">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">            
                        <div class="navbar-brand"><img src="/images/logos/logo150x47.png"></div>
                    </div>
                </div>
            </nav>


            <?php
            $clin_num = 0;
            foreach ($lates as $clinic => $latepract) {
                $clin_num++;
                ?>


                <div class="panel panel-default clinic">
                    <div class="panel-heading" >
                        <img src="<?php echo howlate_util::logoURL($latepract[0]->Subdomain); ?>" class="pull-right">
    <?php echo $clinic; ?>
                    </div>
                    <div class="panel-body">

                        <ul>
                            <?php
                            foreach ($latepract as $key => $r) {
                                /* if first iteration, display extra column showing img */
                                ?> 
                                <li>
                                    <div class="stopwatch">
                                        <div class="inner">
                                            <div class="button"></div>    
                                        </div>
                                        <div class="button"></div>
                                        <div class="hand"></div>
                                    </div>
                                <?php echo $r->AbbrevName; ?> is <span id="<?php echo "$r->OrgID.$r->ID"; ?>"><?php echo $r->MinutesLateMsg; ?></span>
                                <?php if ($r->AllowMessage) { ?>
                                <span onclick="showMessage(<?php echo "'$r->OrgID', '$r->ClinicID', '$r->ID', '$r->AbbrevName', '$UDID'"; ?> )">Cancel</span>
                                
                                <?php } ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </body>

    <!-- A modal with its content -->
    <section class="modal--show" id="modal-show"
             tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

        <div class="modal-inner">
            <header>
                <h2 id="label-show">Let us know if you have to cancel!</h2>
            </header>

            <div class="modal-content">
                <div> 
                    <h2>Sorry I can't make it to <span id="PractitionerName">Drs name goes here</span>'s appointment</h2>
                    <form name="message" action="/late/cancel" method='POST'>
                        <input type="hidden" id="UDID" name="UDID">
                        <input type="hidden" id="OrgID" name="OrgID" >
                        <input type="hidden" id="ClinicID" name="ClinicID" >
                        <input type="hidden" id="PractitionerID" name="PractitionerID">
                        <input type="hidden" id="PractitionerName2" name="PractitionerName2">
                        
                        <input type="submit" id="submit" name="submit" value="Cancel Appt">
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



</html>