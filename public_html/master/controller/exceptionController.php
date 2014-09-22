<?php

Class exceptionController Extends baseController {

    public $org;
    
    
    public function index() {
        $this->view();
    }
  
    public function view($exception) {
        $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, 'Subdomain');
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = howlate_util::logoURL(__SUBDOMAIN);
        $this->registry->template->sorry = "Something went wrong...";
        $this->registry->template->sorry2 = "We have entered the error into our issues list and will deal with it in due course.";
        $this->registry->template->message = $exception->getMessage();
        
        $this->registry->template->file = basename($exception->getFile());
        $this->registry->template->line = $exception->getLine();
        $this->registry->template->code = $exception->getCode();   
        $this->registry->template->trace = $exception->getTraceAsString();
        $this->registry->template->class = get_class($exception);
        
        $this->registry->template->controller = $this;
        $this->registry->template->show('exception_view');
    }
 
}

?>
