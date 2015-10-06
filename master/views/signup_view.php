<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <link rel="apple-touch-icon" href="<?php echo $logourl; ?>" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />
    </head>

    <body class="new-form custom-background">
        <div id="outer">
            <div id="middle">
                <div id="inner">
                    <div class="name-container font-on-custom-background">
                        <div class="company-name">HOW-LATE.COM</div>
                        <div class="login-info">Please sign up by entering your company name and email.</div>
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

                                    <div style="width:100px"></div>
                                    <div id="loader-container" class="load-container">
                                        <div id="loader" class="loader">Loading...</div>
                                    </div>


                                </td>
                                <td> 
                                    <div class="login-right-container">
                                        <div class="form-block">

                                            <form id="signupForm" class="signup-form " name="signupForm" >
                                                <input id="company" name="company" placeholder="Your Company Name"
                                                       type="text" size="25" maxlength="50" class="input-company signupfield" required />
                                                <div class="error-company hide">
                                                    <div class="error"
                                                         data-error-no-company="Your company name is required">
                                                    </div>
                                                    <div class="error-tail">
                                                    </div>
                                                </div>

                                                <input id="email" name="email" placeholder="Your Email Address"
                                                       type="text" size="25" maxlength="50" class="input-email signupfield" required />
                                                <div class="error-email hide">
                                                    <div class="error"
                                                         data-error-no-email="Your email address is required"
                                                         data-error-email-typo="Your email is mistyped">
                                                    </div>
                                                    <div class="error-tail">
                                                    </div>
                                                </div>
                                                <button id="signup-button" class="button large green login" onclick="signupFunction();
                                                        return false;">
                                                    Get Started for Free
                                                </button>
                                            </form>
                                        </div>
                                        <div class="clearb"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>




        <script>
            var xmlhttp;
            function loadXMLDoc(url, cfunc)
            {
                if (window.XMLHttpRequest)
                {// code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp = new XMLHttpRequest();
                }
                else
                {// code for IE6, IE5
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange = cfunc;
                xmlhttp.open("GET", url, true);
                xmlhttp.send();
            }

            function signupFunction()
            {
                document.getElementById("loader").style.display = 'block';
                document.getElementById("signup-button").innerHTML = "Takes around 30 seconds.";
                company = document.getElementById("company").value;
                email = document.getElementById("email").value;
                url_get = "signup/create?company=" + company + "&email=" + email;

                document.getElementById("loader").style.display = 'block';

                loadXMLDoc(url_get, function()
                {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                    {
                        document.getElementById("loader").style.display = 'none';
                        document.getElementById("signup-button").innerHTML = "Done. Check your email!";
                    }
                });
            }

        </script>

        <?php $controller->get_simplefooter(); ?>


