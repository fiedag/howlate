<?php

Abstract Class baseController {
    /*
     * @registry object
     */

    protected $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    /**
     * @all controllers must contain an index method
     */
    abstract function index();

    function get_header() {
        include 'controller/headerController.php';
        $header = new headerController($this->registry);
        $header->view($this->org);
    }

    function get_footer() {
        include 'controller/footerController.php';
        $footer = new footerController($this->registry);
        $footer->view($this->org);
    }

}

?>
