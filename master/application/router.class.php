<?php

class router {
 /*
 * @the registry
 */
 private $registry;

 /*
 * @the controller path
 */
 private $path;

 private $args = array();

 public $file;

 public $controller;

 public $action; 

 function __construct($registry) {
        $this->registry = $registry;
 }

 /**
 *
 * @set controller directory path
 *
 * @param string $path
 *
 * @return void
 *
 */
 function setPath($path) {

	/*** check if path i sa directory ***/
	if (is_dir($path) == false)
	{
		throw new Exception ('Invalid controller path: `' . $path . '`');
	}
	/*** set the path ***/
 	$this->path = $path;
}


 /**
 *
 * @load the controller
 *
 * @access public
 *
 * @return void
 *
 */
 public function loader()
 {
	/*** check the route ***/
	$this->getController();

	/*** if the file is not there diaf ***/
	if (is_readable($this->file) == false)
	{
		$this->file = $this->path.'/error404.php';
                $this->controller = 'error404';
	}

	/*** include the controller ***/
	include $this->file;

	/*** a new controller class instance ***/
	$class = $this->controller . 'Controller';
	$controller = new $class($this->registry);

	/*** check if the action is callable ***/
	if (is_callable(array($controller, $this->action)) == false)
	{
		$action = 'index';
	}
	else
	{
		$action = $this->action;
	}
	/*** run the action ***/
        
        
        if ($this->controller != "login" 
                and $this->controller != "late" 
                and $this->controller != "l" 
                and $this->controller != "signup" 
                and $this->controller != "api"
                and $this->controller != "terms"
                and $this->controller != "error404"
                and $this->controller != "selfreg"
                and $this->controller != "clinicinfo"
                and $this->controller != "reset" ) {
            
            $controller->session_start();
        }
        
        $controller->$action();
 }


 /**
 *
 * @get the controller
 *
 * @access private
 *
 * @return void
 *
 */
private function getController() {

	/*** get the route from the url ***/
	$route = (empty($_GET['rt'])) ? '' : $_GET['rt'];
        //echo "Route = $route";
	if (empty($route))
	{
		$route = 'login';
	}
	else
	{
		/*** get the parts of the route ***/
		$parts = explode('/', $route);
		$this->controller = $parts[0];
		if(isset( $parts[1]))
		{
			$this->action = $parts[1];
		}
	}

	if (empty($this->controller))
	{
		$this->controller = 'login';
	}

	/*** Get action ***/
	if (empty($this->action))
	{
		$this->action = 'login';
	}

        //echo "action=" . $this->action;
	/*** set the file path ***/
	$this->file = $this->path .'/'. $this->controller . 'Controller.php';
}


}

?>
