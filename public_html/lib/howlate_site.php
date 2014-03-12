<?php 

class howlate_site {

	public $template_path = '/home/howlate/public_html/master';
	public $site_path;
	public $base_path = '/home/howlate/public_html';

	protected $subdomain;
	
  function __construct($subdomain) {
		$this->subdomain = $subdomain;
		$this->site_path = $this->base_path . '/' . $subdomain;
	}
	
	public function create($subdomain) {
		// check the path itself 
		$this->subdomain = $subdomain;
		$this->site_path = $this->base_path . '/' . $subdomain;
		
		if (file_exists($this->site_path)) {
			trigger_error('System Error: This site already exists.  Cannot create. (Site path = ' . $this->site_path . ')', E_USER_ERROR);
		}
		// create the subfolder
		echo 'Creating link <b>' . $this->site_path . '</b> to <b>' . $this->template_path . '</b><br>';
		symlink($this->template_path, $this->site_path) ;
		echo 'Creating in cpanel...<br>';
		$this->createCPanel($subdomain);
		echo 'Cpanel subdomain created ok.';
		
		// then we might create the organisation in the database etc.
		
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
			exit();
		}
		echo 'socket opened<br>';
		
		$indom = "GET /frontend/x3/subdomain/doadddomain.html?domain=$ustring&rootdomain=$udomain\r\n HTTP/1.0\r\nHost:$udomain\r\nAuthorization: Basic $pass\r\n\r\n";
		echo 'putting this in socket: ' . $indom;
		echo '<br>';
		fputs($socket2,$indom);
		while (!feof($socket2)) {
			$buf = fgets ($socket2,128);
			//echo $buf ;
		}
		fclose($socket2);
	
	}
	
}

?>

