<?php




try {

echo "Throwing an exception:";

include("test2.php");


}
catch(Exception $ex)
{
    echo "Something went wrong.  The exception has been caught and logged but not shown to the user.";
    
}
?>
