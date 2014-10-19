<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="refresh" content="30" >
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $icon_url; ?>" >

        <meta name="apple-mobile-web-app-capable" content="yes" />
        <link rel="apple-touch-icon-precomposed" href="/images/icon_calendar.png" />

        <script type="text/javascript" src="/js/bookmark_bubble.js"></script>
        <script type="text/javascript" src="/js/bookmark_bubble_example.js"></script>

    </head>
    <body>

        <h1>Register for lateness updates</h1>

        <form name="selfreg" id="selfreg" action="/selfreg/register" method="post">
            <input id="invitepin" name="invitepin" value="<?php echo $invitepin; ?>">
            
            <input id="device" name="device" value="Enter mobile number">
            
            <button type="submit" name="submit" value="reg"
               class="button medium green signup-form-button transition">
               <span class="signup-button-text transition">
                        Get lateness updates
                    </span>
            </button>
            <button type="submit" name="submit" value="unreg"
               class="button medium green signup-form-button transition">
               <span class="signup-button-text transition">
                        Cancel lateness updates
                    </span>
            </button>

        </form>


    </body>



</html>
