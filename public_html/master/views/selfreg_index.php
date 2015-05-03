<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <link href="/lib/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">

        <link rel="stylesheet" href="/styles/modal.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    </head>
    <body>
        <div class="container">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">            
                        <div class="navbar-brand"><img src="/images/logos/logo150x47.png"></div>
                        <h3>Patient Registration</h3> 
                    </div>
                </div>
            </nav>

            <div class="panel panel-default clinic">
                <div class="panel-heading">Enter your practitioner's PIN e.g. "ABCDE.F" </div>

                <div class="panel-body">
                    <form name="selfreg" id="selfreg" action="/selfreg/register" method="post">
                        <input type="text" id="invitepin" name="invitepin" style="text-transform:uppercase" pattern="[A-Z]{5}\.[A-Z]{1,2}" required>
                        <input type="submit" value="Register">
                    </form>
                </div>
                <div class="panel-body"><?php echo $message; ?></div>
            </div>            
    </body>
</html>


