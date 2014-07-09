<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
    </head>

    <body>
        <div class="container">
            <img class="logo" src="/pri/<?php echo $subdomain; ?>/logo.png">
            <h1><?php echo $clinic->ClinicName; ?></h1>
            <div class="address">
                Phone: <a href="tel:<?php echo $clinic->Phone; ?>"><?php echo $clinic->Phone; ?></a><p></p>
                Address: <?php echo "$formattedAddress"; ?>
                <p>
                <a href="<?php echo $addressURL;?>">Search with Google Maps</a>
                

            </div>

        </div>
    </body>


</html>