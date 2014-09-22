<?php

set_exception_handler(unh_excep);


throw new Exception("This is the exception from test.php");


function unh_excep() {
    
    echo "Something bad happened.  Don worry bout it.";
    
}

?>
