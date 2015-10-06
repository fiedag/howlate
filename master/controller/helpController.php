<?php

Class HelpController Extends baseController {

public function index() 
{
    $this->registry->template->controller = $this;
    $this->registry->template->show('help_view');
}




}
?>
