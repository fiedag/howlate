<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>EDM Landing Page</title>

        <!-- Bootstrap -->
        <link href="master/css/bootstrap.min.css" rel="stylesheet">
        <link href="master/css/signin.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>

        <?php 
          $email = filter_input(INPUT_GET,"email");
          $name = filter_input(INPUT_GET,"name");
        
        ?>

        <div class="container">

            <form class="form-signin" action="http://ezmail.honeyweb.com.au/t/r/s/ikqtky/" method="post" id="subForm">
                <h2 class="form-signin-heading">Thank you!</h2>
                <h3>We will contact you shortly...</h3>
                <span>Please update your records or include a note.</span>
                    
                <p>
                    <label for="fieldName" class="sr-only">Name</label><br />
                    <input class="form-control" id="fieldName" name="cm-name" type="text" placeholder="Name" value="<?php echo $name; ?>"/>
                </p>
                <p>
                    <label for="fieldEmail" class="sr-only">Email</label><br />
                    <input class="form-control" id="fieldEmail" name="cm-ikqtky-ikqtky" type="email" required placeholder="Email Address" autofocus value="<?php echo $email; ?>"/>
                </p>
                <p>
                    <label for="fielddllrzl" class="sr-only">Phone</label><br />
                    <input class="form-control" id="fielddllrzl" name="cm-f-dllrzl" type="text" placeholder="Phone" />
                </p>
                <p>
                    <label for="fielddllyxr" class="sr-only">Note</label><br />
                    <input class="form-control" id="fielddllyxr" name="cm-f-dllyxr" type="text" placeholder="Note" />
                </p>

                <div class="checkbox">
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>
                </div>
            </form>

        </div> <!-- /container -->      

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="master/js/bootstrap.min.js"></script>
    </body>
</html>