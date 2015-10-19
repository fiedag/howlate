<?php

Class ExceptionController Extends baseController {
    
    public function index() {
        $this->view();
    }
  
    public function view($exception) {
        $this->registry->template->companyname = (!$this->Organisation == null)?$this->Organisation->OrgName:"";
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
