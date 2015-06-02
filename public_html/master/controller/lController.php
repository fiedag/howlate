<?php
include_once('controller/lateController.php');

class LController extends LateController {
    public function index() {
        $this->view();
    }
}
