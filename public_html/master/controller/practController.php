<?php

Class practController Extends baseController {

    public $org;


    public function index() {
	    $this->org = new organisation();
        $this->org->getby(__SUBDOMAIN, "Subdomain");
        $this->registry->template->companyname = $this->org->OrgName;
        $this->registry->template->logourl = $this->org->LogoURL;

        $this->registry->template->controller = $this;
        $this->registry->template->show('pract_index');
		
		
    }
    
    
    public function getXcrudTable() {
        include('includes/xcrud/xcrud.php');
        $xcrud = Xcrud::get_instance();
        $xcrud->table('practitioners')->where('OrgID =', $this->org->OrgID)->limit(10)->columns('OrgID, ID, UpdIndic, SurrogKey', true);
        $xcrud->fields('OrgID, ID, UpdIndic, SurrogKey', true, false, 'edit');
        $xcrud->hide_button('view');
        $xcrud->label(array('FullName' => 'Full Name', 'AbbrevName' => 'Abbrev Name', 'DateCreated' => 'Created', 'SurrogKey' => 'PIN'));
        $xcrud->unset_csv(true)->unset_numbers(true)->unset_print(true)->unset_limitlist(true)->hide_button('save_and_edit')->hide_button('save_and_new');
        $xcrud->create_action('assign', 'assign_action', __FILE__); 
        $arr = array(  // set action vars to the button
            'data-task' => 'action',
            'data-action' => 'assign',
            'data-OrgID' => '{OrgID}',
            'data-ID' => '{ID}');
        $xcrud->button('#','Assign','','',$arr);
        echo $xcrud->render();

    }
    
    
    public function assign_action($xcrud) {
        $task = $xcrud->get('blah');
        $action = $xcrud->get('action');
        $OrgID = $xcrud->get('OrgID');
        $ID = $xcrud->get('ID');
        echo ("$task, $action, $OrgID, $ID");
            
        
    }

}
