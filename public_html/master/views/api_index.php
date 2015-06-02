<?php
header('Content-type: application/json');
if (isset($exception) && $exception instanceof APIException) {
    echo $exception->getMessage();
} else {
    echo json_encode($result);
}
?>