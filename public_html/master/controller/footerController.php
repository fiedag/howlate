<?php

Class footerController Extends baseController {

    public function index() {
        $this->view();
    }

    public function view($org) {
        $this->registry->template->copyright = "Copyright " . date('Y', time()) . " $org->OrgName";
        $this->registry->template->show('footer_view');
    }

}

?>