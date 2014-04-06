<?php
    session_start();
    session_unset(); 
    session_destroy();
    
?>

<div class='large form-block'>

    <h3>You have been logged out.</h3>
    <a href='/login' >Log in again</a>
        
</div>


