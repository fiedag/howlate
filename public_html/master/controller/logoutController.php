<?php

Class logoutController Extends baseController {

    public function index() {
        $this->registry->template->controller = $this;
        $this->registry->template->show('logout_index');
    }

    public function get_header() {
        include 'controller/headerController.php';
        $header = new headerController($this->registry);
        $header->view($this->org);
    }

    public function get_footer() {
        include 'controller/footerController.php';
        $footer = new footerController($this->registry);
        $footer->view($this->org);
    }

}

?>