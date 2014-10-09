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

        <form id="api_call" action="api?met=''" action='POST'>
            <input name="subdomain" id="subdomain" type="text" size="25" maxlength="50" class="input-company signupfield" required></input>
            <button type="submit" name="submit" value="submit">Delete Subdomain</button>
        </form>

    </body>
</html>
