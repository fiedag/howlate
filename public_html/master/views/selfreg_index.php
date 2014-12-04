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

    </head>
    <body>

        <h1>Register for lateness updates</h1>

        <form name="selfreg" id="selfreg" action="/selfreg/register" method="post">
            <input id="invitepin" name="invitepin" value="<?php echo $invitepin; ?>">
            

        </form>


    </body>



</html>
