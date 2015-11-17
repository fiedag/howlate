<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="styles/howlate_signup.css">

        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
        <link rel="apple-touch-icon" href="<?php echo $logourl; ?>" >
        <link rel="icon" type="image/png" href="<?php echo $logourl; ?>" />
    </head>

    <body class="container">
        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="/js/jquery.jeditable.min.js"></script>

        <div class="container">
            <span class="load-container col-md-2">
                <div id="loader" class="loader">Loading...</div>
            </span>
            <span class="col-md-10">
                <form id="signupForm" name="signupForm" class="form-inline">
                    <legend>Sign up for HOW-LATE Trial</legend>

                    <div class="form-group">
                        <label for='company'>Organisation:</label>
                        <input class="form-control" id="company" name="company" type="text" maxlength="50" placeholder="Your Organisation name" required />
                    </div>
                    <div class="form-group">
                        <label for='email'>Email:</label>
                        <input class="form-control" id="email" name="email" type="text" maxlength="50" placeholder="Your Email Address" required />
                    </div>
                    <button id="signup-button" class="btn-primary" type="button" onclick="signupFunction();return false;">Sign up</button>
                </form>
            </span>

        </div>
        <div class="container">
            <div id="result" class="hidden alert alert-success" role="alert">Alert Message Here</div>
        </div>

        <script>

            function signupFunction()
            {
                $("#loader").css("display", "block");
                $("#signup-button").html('Please wait...');

                company = $("#company").val();
                email = $("#email").val();
                url_get = "signup/create?company=" + company + "&email=" + email;
                $.ajax({
                    url: url_get,
                    dataType: 'json'
                }).done(function (data) {  
                    debugger;
                    if(data.status === "error") {
                        $("#result").removeClass('hidden').addClass('alert-danger').html(data.message);
                        $("#signup-button").html('Error');
                    }
                    else {
                        $("#result").removeClass('hidden').addClass('alert-success').html("Signup was successful.");
                        $("#signup-button").html('OK');
                    }
                });
                $("#loader").css("display", "none");

            }

        </script>

    </body>

</html>


