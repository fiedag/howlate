<?php

Abstract Class BaseController {
    /*
     * @registry object
     */

    protected $registry;

    public $Organisation;
    
    function __construct($registry) {
        $this->registry = $registry;  
        set_exception_handler(array($this, 'handle_exception'));
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);
        
        
    }

    /**
     * @all controllers must contain an index method
     */
    abstract function index();

    function get_header() {
        include 'controller/headerController.php';
        $header = new headerController($this->registry);
        $header->view($this->Organisation);
    }
    
    function get_footer() {
        include 'controller/footerController.php';
        $footer = new FooterController($this->registry);
        $footer->view($this->Organisation);
    }

    function get_simplefooter() {
        include 'controller/footerController.php';
        $footer = new FooterController($this->registry);
        $footer->index();
    }

    
    public function handle_exception($exception) {
        try {
            $ip = $_SERVER["REMOTE_ADDR"];
            Logging::write_error(0, 1, $exception->getMessage(), $exception->getFile(), $exception->getLine(), $ip, $exception->getTraceAsString());
        } catch (Exception $ex) {
        }

        include 'controller/exceptionController.php';
        $controller = new exceptionController($this->registry);
        $controller->view($exception);
    }

    
    function session_start() {
        session_start();

        if (!isset($_SESSION["USER"]) or $_SESSION["LAST_ACTIVITY"] < (time() - 3600)) {
            if (isset($_COOKIE["USER"]) and isset($_COOKIE["ORGID"])) {
                echo "no user session var or session is old, but cookies fine";
                $_SESSION["DIAG"] .= ",cookie is set so assign and redirect to " . $_COOKIE["URL"];
                // get some info from the cookie
                $_SESSION["ORGID"] = $_COOKIE["ORGID"];
                $_SESSION["USER"] = $_COOKIE["USER"];
                $_SESSION['LAST_ACTIVITY'] = time();
                if (isset($_COOKIE["URL"])) {
                    header("location: " . $_COOKIE["URL"]);
                }
            } else {
                
                session_unset();
                session_destroy();

                $login = "https://" . __FQDN . "/login";
                header("location: " . $login);
            }
        }

        $_SESSION["LAST_ACTIVITY"] = time();


        $callingURL = "https://" . $_SERVER['HTTP_HOST'] . "/" . $_GET['rt'];
        setcookie("URL", $callingURL, time() + 3600 * 8);
    }
    
    public function getsimplename() {
        $res = get_class($this);
        $suf = strrpos($res, "Controller");
        return strtolower(substr($res,0,$suf));
  
    }

}

?>
