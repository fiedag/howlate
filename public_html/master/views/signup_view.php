<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet" >     
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $logourl; ?>" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />
    </head>

    <body class='signup'>
        <div id="navmain" class="fresh-header">
            <header class="header">
                <div class="center">
                    <a href="http://<?php echo __DOMAIN;?>"><div class="howlate-logo"><img class="systemlogo" src="<?php echo $logourl; ?>"></div></a>
                </div>
                
            </header>

            <div class="signup">
                <h1>Try How-Late Free</h1>
                <h2>No credit card required. Cancel anytime.</h2>

                <form class="signup-form subscribe-form" name="form" action="/signup/create" method="post">

                    <input name="company" placeholder="Your Company Name"
                           type="text" size="25" maxlength="50" class="input-company signupfield" required />
                    <div class="error-company hide">
                        <div class="error"
                             data-error-no-company="Your company name is required">
                        </div>
                        <div class="error-tail">
                        </div>
                    </div>

                    <input name="email" placeholder="Your Email Address"
                           type="text" size="25" maxlength="50" class="input-email signupfield" required />
                    <div class="error-email hide">
                        <div class="error"
                             data-error-no-email="Your email address is required"
                             data-error-email-typo="Your email is mistyped">
                        </div>
                        <div class="error-tail">
                        </div>
                    </div>

                    <button type="submit" name="submit" value="submit" class="getstarted-button">
                            Get Started for Free
                    </button>



                </form>

            </div>

            <div>
                <b> <?php echo $signup_result; ?></b>
                
                
                
            </div>
            
        </div>

<?php $controller->get_simplefooter(); ?>


