<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;">
        <link media="screen" href="/styles/howlate.css" type="text/css" rel="stylesheet">
        <link media="only screen and (max-device-width: 480px)" href="/styles/howlate_mobile.css" type="text/css" rel="stylesheet">
        <script>
            function bookmark(address, sitename) {
                if (window.sidebar) { //Firefox
                    window.sidebar.addPanel(sitename, address, "");
                } else if (document.all) { //IE
                    window.external.AddFavorite(address, sitename);
                } else if (window.opera && window.print) { //Opera
                    var elem = document.createElement('a');
                    elem.setAttribute('href', address);
                    elem.setAttribute('title', sitename);
                    elem.setAttribute('rel', 'sidebar');
                    elem.click();
                }
            }
            
            
            function DoNav(theUrl)
            {
                document.location.href = theUrl;
            }
            
        </script>
    </head>
<body>
    <table>
        <tr>
            <td id="when"><?php echo $when_refreshed; ?></td><td><a id="refresh" href="javascript:location.reload(true);">Refresh</a></td>
        </tr>
    </table>
    <p />
    <?php
    foreach ($lates as $clinic => $latepract) {
        ?>
        <table class="lateness">

            <?php
            $i = 0;
            foreach ($latepract as $key => $r) {
                /* if first iteration, display extra column showing img */
           
                $i++;
                if ($i == 1) {
                    ?><tr>
                        <td class="clinrow"><a href='<?php echo "/clinicinfo?orgid=$r->OrgID&clinicid=$r->ClinicID"; ?>'><?php echo $clinic ; ?></a></td>
                        <td class="clinrow logo"><img src="/pri/<?php echo $r->Subdomain; ?>/logo.png"></td> 
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td class="practrow"><?php echo $r->AbbrevName; ?> </td><td class="practrow"> <?php echo $r->MinutesLateMsg; ?></td>
                </tr>

                <?php
            }
            ?>
        </table>
        <p />
        <?php
    }
    ?>


    <span id="footer">
        <a id="refresh" href="javascript:location.reload(true);">Refresh</a>
        <a href="#" onclick="bookmark('<?php echo $bookmark_url; ?>', '<?php echo $bookmark_title; ?>')">Bookmark Us Now!</a>

    </span>

</body>

</html>