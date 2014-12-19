<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="Cache-control" content="no-cache">
        <link media="screen" href="/styles/late.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $apple_icon_url; ?>" >

        <meta name="apple-mobile-web-app-capable" content="yes" />
        <link rel="apple-touch-icon-precomposed" href="<?php echo $apple_icon_url; ?>" />

        <script type="text/javascript" src="/js/bookmark_bubble.js"></script>
        <script type="text/javascript" src="/js/bookmark_bubble_example.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script>
            function DoNav(theUrl)
            {
                document.location.href = theUrl;
            }


            /* use JQuery to assemble a GET parameter comprising comma-separated pins we need to refresh
             * then we make an AJAX call to get these latenesses which is returned as a JSON string
             * This JSON string is used to update the spans with the corresponding ID
             */

            function refreshLates() {
                var url = "<?php echo $refresh_url; ?>";
                
                $.getJSON(url, function(result) {
                    $.each(result, function(id, field) {
                        var j = "#" + id.replace(".", "\\.");  // the dot needs escaping!
                        $(j).html(field);
                        

                        $(j).parent().removeClass().addClass(function() {
                            var classes = "latenesses";
                            if (field == "off duty") {
                                classes = classes + " greydot";
                            } else if (field == "on time") {
                                classes = classes + " greendot";
                            } else if (field.match(/[0-9]{1,2} minutes late/g)) {
                                classes = classes + " yellowdot";
                            } else if (field.match(/1 hour.*/)) {
                                classes = classes + " orangedot";
                            } else 
                            {
                                classes = classes + " reddot";
                            }
                            return classes;
                        });
                    });
                });
                var stop = new Date();
                $("#localtime").text(timestr(stop));
                
            }

            /* and run this every <?php echo $refresh; ?> milliseconds */
            function timestr(dte) {
                return dte.getHours() + ":" + pad(dte.getMinutes()) + ":" + pad(dte.getSeconds());
            }
            
            function pad(n) { return ("0" + n).slice(-2); }
            
            $(document).ready(function() {
               $("#localtime").text(timestr(new Date())); 
               refreshLates();
            });


            var interval = setInterval(refreshLates, <?php echo $refresh; ?>);





        </script>


    </head>
    <body>

        <div class="late-header-box">How Late</div>
        
            <?php
            $clin_num = 0;
            foreach ($lates as $clinic => $latepract) {
                $clin_num++;
                ?>
                <div class="clinic-box fading">
            

                <?php
                $i = 0;
                foreach ($latepract as $key => $r) {
                    /* if first iteration, display extra column showing img */

                    $i++;
                    if ($i == 1) {
                        ?> 
                <div class="clinic-box-header" style="background-image: url('<?php echo howlate_util::logoURL($r->Subdomain); ?>');"><div class="clinic-name"><?php echo $clinic; ?></div></div>
            <?php
        }
        ?>
                <div class="latenesses" id="latenesses"><?php echo $r->AbbrevName; ?></div>
                <div class="late-message">
                <span id="<?php echo "$r->OrgID.$r->ID"; ?>"><?php echo $r->MinutesLateMsg; ?></span>
                </div>

                <?php
            }
            ?>
            
                </div>
    <?php
}
?>
    <!-- div class='refresh '>Updated <span id="localtime"></span></div  -->
        
  </body>

  
</html>