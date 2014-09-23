<!DOCTYPE html>
<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="refresh" content="<?php echo $refresh; ?>">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $apple_icon_url; ?>" >

        <meta name="apple-mobile-web-app-capable" content="yes" />
        <link rel="apple-touch-icon-precomposed" href="<?php echo $apple_icon_url; ?>" />

        <script type="text/javascript" src="/js/bookmark_bubble.js"></script>
        <script type="text/javascript" src="/js/bookmark_bubble_example.js"></script>
        <script>

            function DoNav(theUrl)
            {
                document.location.href = theUrl;
            }

        </script>


    </head>
    <body class="lateness">
        
        <img alt="" id="sysLogo" class="system-logo" 
        title="<?php if (isset($usercookie)) {echo "User cookie $usercookie .";} if (isset($orgidcookie)) {echo "  OrgID cookie $orgidcookie";} ?>" 
        src="<?php echo $icon_url; ?>" height="100" width="100">
        <h3>How late is my appointment?</h3>

        <?php
        foreach ($lates as $clinic => $latepract) {
            ?>
            <table>

                <?php
                $i = 0;
                foreach ($latepract as $key => $r) {
                    /* if first iteration, display extra column showing img */

                    $i++;
                    if ($i == 1) {
                        ?><tr>
                            <td class="clinrow"><?php echo $clinic ; ?></td>
                        </tr> 
                        <tr>
                            <td><img class="clinic-logo" src="<?php echo howlate_util::logoURL($r->Subdomain); ?>"></td> 
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><?php echo $r->AbbrevName; ?> is <?php echo $r->MinutesLateMsg; ?></td>
                    </tr>

                    <?php
                }
                ?>
            </table>
            <p />
            <?php
        }
        ?>

            
    <table>
        <tr>
            <td class='refresh '><?php echo $when_refreshed; ?></td><td><a class="refresh" href="javascript:location.reload(true);">Refresh</a></td>
        </tr>
    </table>            
            
    </body>

</html>