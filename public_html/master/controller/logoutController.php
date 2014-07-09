<?php

Class logoutController Extends baseController {

    public function index() {
        $this->registry->template->controller = $this;
        
        setcookie("USER", '', 1);
        setcookie("ORGID", '', 1);

        
        session_unset();

        $this->registry->template->show('logout_index');
    }


}

?>