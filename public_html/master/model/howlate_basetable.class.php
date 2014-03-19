<?php 
class howlate_basetable {


	public function __construct($arr) {
		foreach($arr as $key => $val) {
			$this->$key = $val;
		}
	}
	
}

?>