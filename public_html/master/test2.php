<!DOCTYPE html>
<html>
    <head>
        <link media="screen" href="/styles/howlate_signup.css" type="text/css" rel="stylesheet" >     

        <script>
            var xmlhttp;
            var toggle = true;
            
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

            function myFunction(val )
            {
                if (val == 1) {
                    document.getElementById("loader").style.display = "block"; 
                }
                else
                {
                    document.getElementById("loader").style.display = "none"; 
                }
            }
            
        </script>
    </head>
    <body>

        <form id="api_call" action="api/org" method="POST">
            <input name="credentials" id="credentials" type="text" size="25" maxlength="50" value="alexf.9cbf8a4dcb8e30682b927f352d6559a0"></input>
            <input name="OrgName" id="OrgName" type="text" size="25" maxlength="50" value='Hastings1 New'></input>
            <button type="submit" name="submit" value="submit">test method</button>
        </form>

    </body>
</html>
