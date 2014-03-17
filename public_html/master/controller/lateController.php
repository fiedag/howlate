<?php

Class lateController Extends baseController {

	public function index() 
	{
					$this->view();
	}
	
	
	public function view(){
		$this->registry->template->when_refreshed = 'Refreshed ' . date('h:i A');
		if (isset($_GET['udid'])) {
			$udid = $_GET['udid'];
			$this->registry->template->UDID = $udid;
			$db = new howlate_db();
			$lates = $db->getlatenesses($udid); // a two-dimensional array ["clinic name"][array]
			$db->trlog(TranType::LATE_GET, 'Lateness got by device ' . $udid, null, null, null, $udid);
			if (!empty($lates)) {
				$this->registry->template->lates = $lates;
				$this->registry->template->show('late_view');
			}
		}
	}
}
?>