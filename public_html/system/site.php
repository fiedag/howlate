<?php 

class site() {

	public string $template_path = '/home/howlate/public_html/secure';
	public string $site_path;
	public string $base_path = '/home/howlate/public_html';

	protected string $subdomain;
	
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
		symlink($template_path, $sitepath) or trigger_error('The site for subdomain ' . $this->subdomain . ' could not be created on path ' . $this->site_path , E_USER_ERROR);

		// then we might create the organisation in the database etc.
		
	}
}

?>

