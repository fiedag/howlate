<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=1">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <link href="/css/bootstrap.min.css" type="text/css" rel="stylesheet">
        <link href="/css/bootstrap-theme.min.css" type="text/css" rel="stylesheet">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

    </head>
    <body>

        <div class="container">
            <div class="page-header"><h2>Patient Registration</h2> </div>
            <div class="panel panel-default">

                <div class="panel-heading">
                    <p>Enter the practitioner's PIN e.g. <b>ABCDE.F</b></p>
                    <?php if (isset($exception) && $exception) echo "<div class='alert alert-danger' role='alert'>$exception</div>"; ?>
                </div>
                <div class="panel-body">
                    <form class="navbar-form navbar-left" name="selfreg" id="selfreg" action="/selfreg/register" method="post">
                        <div class="form-group">

                            <input type="text" class="form-control" id="invitepin" name="invitepin" style="text-transform:uppercase" pattern="[A-Z,a-z]{5}\.[A-Z,a-z]{1,2}" required>
                            <button class="btn btn-lg btn-primary navbar-btn" type="submit"><span class="glyphicon glyphicon-ok"></span></button>
                        </div>
                    </form>
                </div>
            </div>            
        </div>
    </body>
</html>


