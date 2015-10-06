<?php
header('Content-type: application/json');
echo json_encode(array("code"=>200,"status"=>"OK","message"=>(isset($message))?$message:"","response"=>(isset($response))?$response:false));
?>