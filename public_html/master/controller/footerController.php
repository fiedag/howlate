<?php

Class footerController Extends baseController {

    public function index() {
        $this->registry->template->copyright = "Copyright " . date('Y', time()) . " How-Late.Com";
        $this->registry->template->show('footer_view');
    }

    public function view($org) {
        $this->registry->template->copyright = "Copyright " . date('Y', time()) . " $org->OrgName";
        $this->registry->template->show('footer_view');
        /* comment */
    }

}

?>