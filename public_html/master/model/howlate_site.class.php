<?php 

class howlate_site {

	public $template_path = '/home/howlate/public_html/master';
	public $site_path;
	public $base_path = '/home/howlate/public_html';

	public $private_area ;   // a hashed folder location for organisation's logos icons and style sheets
	
	
	protected $subdomain;
	
  function __construct($subdomain) {
		$this->subdomain = $subdomain;
		$this->site_path = $this->base_path . '/' . $subdomain;
		$this->private_area = $this->template_path . '/pri/' . $subdomain;
		if (!file_exists($this->private_area)) {
			mkdir($this->private_area);
		}
		
	}
	
	public function create($subdomain) {
		// check the path itself 
		$this->subdomain = $subdomain;
		$this->site_path = $this->base_path . '/' . $subdomain;
		
		if (file_exists($this->site_path)) {
			trigger_error('System Error: This site already exists.  Cannot create. (Site path = ' . $this->site_path . ')', E_USER_ERROR);
		}
		// create the subfolder
		symlink($this->template_path, $this->site_path) ;
		if (!$this->createCPanel($subdomain)) {
			trigger_error('Error creating cpanel subdomain ' , E_USER_ERROR);
		}
		
		// then we might create the organisation in the database etc.
		return true;
	}
	
	protected function createCPanel($subdomain) {
		$username = "howlate";
		$password = "3134-5Q^hP$1";
		$udomain = "how-late.com";
		$authstr = "$username:$password";
		$pass = base64_encode($authstr);
		$ustring = $subdomain;
		
		$socket2 = fsockopen("how-late.com",2082);
		if(!$socket2) {
			trigger_error('Socket error trying to connect to cpanel to create the subdomain', E_USER_ERROR);
			return false;
		}

		$lookingfor = "has been created!";
		$indom = "GET /frontend/x3/subdomain/doadddomain.html?domain=$ustring&rootdomain=$udomain\r\n HTTP/1.0\r\nHost:$udomain\r\nAuthorization: Basic $pass\r\n\r\n";
		fputs($socket2,$indom);
		while (!feof($socket2)) {
			$buf = fgets ($socket2,128);
			if (strpos($buf,$lookingfor)) {
				fclose($socket2);
				return true;
			}
		}
		fclose($socket2);
		return false;
	}
	
	
	protected function createPrivateArea($subdomain) {
	
	
	
	}
	
}

?>

