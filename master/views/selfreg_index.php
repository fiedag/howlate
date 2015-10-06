<html>
    <head>
        <title>How Late</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=1">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="apple-mobile-web-app-capable" content="yes" />

       <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
       <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha256-Sk3nkD6mLTMOF0EOpNtsIry+s1CsaqQC1rVLTAy+0yc= sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>

        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>

    </head>
    <body>

        <div class="container">
            <div class="page-header"><h2>Get Info</h2> </div>
            <div class="panel panel-default">

                <div class="panel-heading">
                    <p>Enter the practitioner's PIN e.g. <b>ABCDE.F</b> to get lateness info on this device.</p>
                    <?php if (isset($exception) && $exception) echo "<div class='alert alert-danger' role='alert'>$exception</div>"; ?>
                </div>
                <div class="panel-body">
                    <form class="navbar-form navbar-left" name="selfreg" id="selfreg" action="/selfreg/register" method="post">
                        <div class="form-group">

                            <input type="text" class="form-control" id="invitepin" name="invitepin" style="text-transform:uppercase" pattern="[A-Z,a-z]{5}\.[A-Z,a-z]{1,2}" required>
                            <button class="btn btn-primary navbar-btn" type="submit"><span class="glyphicon glyphicon-plus"></span></button>
                        </div>
                    </form>
                </div>
            </div>            
        </div>
    </body>
</html>


