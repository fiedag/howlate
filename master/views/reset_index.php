<!DOCTYPE html>

<html lang="en">
    <head>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link media="screen" href="/styles/howlate_login.css" type="text/css" rel="stylesheet">        
        <link rel="apple-touch-icon" href="<?php echo $logourl ?>" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />

    </head>

    <body class="new-form custom-background">


        <div id="outer">
            <div id="middle">
                <div id="inner">
                    <div class="name-container font-on-custom-background">
                        <div class="company-name"><?php echo $controller->Organisation->OrgName; ?></div>
                        <div class="login-info"><?php echo $login_info; ?></div>
                    </div>
                    <div class="secure-info font-on-custom-background ">
                        <div class="secure-lock"></div>
                        This website is protected by 256-bit SSL security
                    </div>
                    <div class="form-pad-top clearb"></div>
                    <div class="boxMain">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="275" class="login-table">
                                    <img class="photo" alt="" title="" width="150" src="<?php echo $logourl; ?>" />
                                </td>
                                <td>

                                    <div class="login-right-container">
        <?php if (!isset($no_access) or $no_access == 0) { ?> 
                                        <form method="post" id="form" action="/reset/change">

                                            <div class="form-block">

                                                <label for="password">New Password:</label>
                                                <input type="password" name="password" id="password" style="width: 408px;" maxlength="" value="" /><br />

                                                <label for="password2">Confirm Password:</label>
                                                <input type="password" name="password2" id="password2" style="width: 408px;" maxlength="" value="" /><br />
                                                <input type="hidden" name="userid" id="userid" value="<?php echo $userid;?>" />
                                                <div id="password-message"><?php echo (isset($password_message))?$password_message:""; ?></div>
                                                <button class="button large inline green first password-reset" type="submit" name="Submit" value="Submit">Reset Password</button>

                                            </div>

                                            <input type="hidden" name="token" id="token" value="<?php echo $token;?>" />
                                        </form>
                                        
        <?php } ?>                                
        <?php if (isset($no_access) and $no_access == 1) { ?> 
                                        
                                        <div class="notification-page notification-error" id="notification_page">
                                            <h3><?php echo $notification_header; ?></h3>
                                            <ul>
                                                <li><?php echo $login_info; ?></li>
                                            </ul>


                                        </div>
                                            <div id="notification_page_button">
                                                <a href="/" class="button large green  " style="">
                                                    Return to login page
                                                </a>


                                        </div>
        <?php } ?>
                                        <div class="clearb"></div>
                                    </div>

                                </td>
                            </tr>
                        </table>


                    </div>
                    <div class="login-box-shadow"></div>
                </div>
            </div>
        </div>

        <script src="js/login.js" type="text/javascript" ></script>


    </body>
</html>
