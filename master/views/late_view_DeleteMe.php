<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width, user-scalable=no" />
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <link rel="apple-touch-icon" href="<?php echo $apple_icon_url; ?>" >
        <link rel="apple-touch-icon-precomposed" href="<?php echo $apple_icon_url; ?>" />
        <link href="/css/bootstrap.min.css" type="text/css" rel="stylesheet">
        <link href="/styles/smslate.css" type="text/css" rel="stylesheet">

        <link rel="stylesheet" href="/styles/modal.css">

    <style>
      #map-canvas {
        height:20em;
        background-color: #CCC;
      }
    </style>
        
        <script type="text/javascript" src="/js/bookmark_bubble.js"></script>
        <script type="text/javascript" src="/js/bookmark_bubble_example.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js"></script>

        <script>
            /* use JQuery to assemble a GET parameter comprising comma-separated pins we need to refresh
             * then we make an AJAX call to get these latenesses which is returned as a JSON string
             * This JSON string is used to update the spans with the corresponding ID
             */

            function googleMap(lat,lng,markerText) {
                var mapCanvas = document.getElementById('map-canvas');
                var clinPos = new google.maps.LatLng(lat, lng);
                var mapOptions = {
                    center: clinPos,
                    zoom: 14,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                var map = new google.maps.Map(mapCanvas, mapOptions);
                var marker = new google.maps.Marker({
                     position: clinPos,
                    map: map,
                      title: markerText
                  });
            }


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

            function clinicModal(orgID, clinicID) {
                var refreshUrl = "http://m.how-late.com/api/clin?org=" + orgID + "&clin=" + clinicID;
                var clinicName = "";
                $.getJSON(refreshUrl).then(function(result) {
                            $.each(result, function(fieldName, fieldValue) {
                                switch(fieldName) {
                                    case "ClinicName":
                                        $("#label-show").text(fieldValue);
                                        clinicName = fieldValue;
                                        break;
                                    case "Phone":
                                        $("#clin-phone").attr("href","tel:" + fieldValue);
                                        $("#clin-phone2").attr("href","tel:" + fieldValue);
                                        $("#clin-phone2").text(fieldValue);
                                        break;
                                    case "Address1":
                                        $("#clin-address1").text(fieldValue);
                                        break;
                                    case "Address2":
                                        $("#clin-address2").text(fieldValue);
                                        break;
                                    case "City":
                                        $("#clin-city").text(fieldValue);
                                        break;
                                    case "LatLong":
                                        var arrLatLong = fieldValue.split(",");
                                        if (arrLatLong.length == 2) {
                                            googleMap(arrLatLong[0],arrLatLong[1],clinicName);
                                        }
                                        else {
                                            $("#map-canvas").html("");
                                        }
                                        break;
                                }
                            });
                        });
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


                <div class="panel panel-default clinic" onclick="clinicModal(<?php echo "'" . $latepract[0]->OrgID . "','" . $latepract[0]->ClinicID . "'" ; ?>);">
                    <div class="panel-heading" onclick="clinicModal(<?php echo "'" . $latepract[0]->OrgID . "','" . $latepract[0]->ClinicID . "'" ; ?>);">
                        <img src="<?php echo HowLate_Util::logoURL($latepract[0]->Subdomain); ?>" class="pull-right">
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

        <!-- A modal with its content -->
        <section class="modal--show" id="modal-show"
                 tabindex="-1" role="dialog" aria-labelledby="label-show" aria-hidden="true">

            <div class="modal-inner">
                <header>
                    <h2 id="label-show"></h2>
                    
                </header>

                <div class="modal-content">
                    <a id="clin-phone" href="jquery to supply">Click to speak to us and cancel or reschedule your appointment.</a>
                    <br>
                    <a id="clin-phone2" href="jquery to supply"></a><br>

                    <div id="map-canvas">
                        
                    </div>
                </div>
            </div>

            <footer>
                <p>Footer</p>
            </footer>
        </div>
        <a href="#!" class="modal-close" title="Close this modal" data-dismiss="modal" data-close="Close">&times;</a>
    </section>

</body>
</html>
