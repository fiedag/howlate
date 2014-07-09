<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <meta http-equiv="refresh" content="30" >
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <link rel="apple-touch-icon" href="<?php echo $icon_url; ?>" >
        
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <link rel="apple-touch-icon-precomposed" href="/images/icon_calendar.png" />
    
        <script type="text/javascript" src="/js/bookmark_bubble.js"></script>
        <script type="text/javascript" src="/js/bookmark_bubble_example.js"></script>
        <script>
            
            function DoNav(theUrl)
            {
                document.location.href = theUrl;
            }
            
        </script>
        
        
    </head>
<body>
    <h1>How late is my doctor?</h1>
    
    
    <table>
        <tr>
            <td class='refresh '><?php echo $when_refreshed; ?></td><td><a class="refresh" href="javascript:location.reload(true);">Update</a></td>
        </tr>
    </table>
    <p />


    <span id="footer">
        <a class="refresh" href="javascript:location.reload(true);">Update</a>
    </span>

</body>

</html>