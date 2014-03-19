<?php

Class footerController Extends baseController {

	public function index() 
	{
					$this->view();
	}
	
	
	public function view(){
				$this->registry->template->show('footer_view');
	}
}
?>