<?php

Class blogController Extends baseController {

public function index() 
{
        $this->registry->template->blog_heading = 'This is the blog Index';
        $this->registry->template->show('blog_index');
}


public function view(){

	/*** should not have to call this here.... FIX ME ***/

	$this->registry->template->blog_heading = 'This is the blog heading';
	$this->registry->template->blog_content = 'This is the blog content';
	$this->registry->template->show('blog_view');
}

public function add(){

	/*** should not have to call this here.... FIX ME ***/

	$this->registry->template->blog_heading = 'Going to add stuff';
	$this->registry->template->blog_content = 'This is the blog content';
	$this->registry->template->what_is_this_doing = 'what is this doing';

	$this->registry->template->blog_content = 'Going to add stuff';
	$this->registry->template->show('blog_view');
}


}
?>
