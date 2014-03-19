<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link id="cssbase" media="screen" href="/styles/howlate_base.css" type="text/css" rel="stylesheet">
        <link id="csslogin" media="screen" href="/styles/howlate_login.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $logourl ?>/logo.png" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />

    </head>

    <body class="custom-background">
        <div class="middlediv">
            <div>
                <div class="company-name"><?php echo $companyname ?></div>
                <div class="login-info">Please log in to your account.</div>
            </div>
            <div class="protected">
                <div class="secure-lock"></div>
                This website is protected by 256-bit SSL security
            </div>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="275" class="login-table">
                        <img class="photo" alt="" width="100" src="<?php echo $logourl; ?>" height="100" />
                    </td>
                    <td>
                        <div class="login-right-container">
                            <div class="login-forms">
                                <form name="login" method="POST" id="form_login" style="display: block;" action="login/attempt">
                                    <div class="form-block">
                                        <label for="username">Username:</label>
                                        <input type="text" name="username" id="username" maxlength="50" value=""  /><br />

                                        <label for="password">Password:</label>
                                        <input type="password" name="password" id="password" maxlength="120" value=""  /><br />

                                        <button class="button large green login" id="log_in" type="submit" name="Submit" value="Submit">Log in</button>

                                        <div class="forgot-link">
                                            <a href="javascript:forgot();">
                                                Forgot your username or password?
                                            </a>
                                        </div>
                                    </div>

                                    <input type="hidden" name="openid" id="openid" value="" />
                                    <input type="hidden" name="forgotten" id="forgotten" value="" />
                                    <input type="hidden" name="javascriptDisabled" id="javascriptDisabled" value="1" />
                                </form>

                            </div>

                            <div class="clearb"></div>
                        </div>
                    </td>
                </tr>
            </table>

        </div>

    </body>
</html>
