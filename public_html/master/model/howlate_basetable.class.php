<?php 
class Howlate_BaseTable {


	public function __construct($arr = null) {
            if (!is_null($arr)) {
		foreach($arr as $key => $val) {
			$this->$key = $val;
		}
            }
	}
	
}

?>