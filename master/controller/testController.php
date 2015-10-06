<?php


Class TestController Extends baseController {

    public $clinic;
    public $appointments;


    public function phpinfo() {
        echo phpinfo();
    }
    

    public function log() {
        
        $OrgID = filter_input(INPUT_GET,"org");
        $ClinicID = filter_input(INPUT_GET,"clin");

        $test = new ApptBookTests($OrgID, $ClinicID);
        
        $test->displayLog();
        
    }
    
    public function notify() {
        $OrgID = filter_input(INPUT_GET,"org");
        $ClinicID = filter_input(INPUT_GET,"clin");

        $test = new ApptBookTests($OrgID, $ClinicID);
        $test->testNotify();
    }
    
    public function index() {
        
        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
        
    }
    
    public function index333() {

        $graphs = new Graphing();
        
        $ClinicID = filter_input(INPUT_GET,"clin");

        $clinic = Clinic::getInstance($this->org->OrgID, $ClinicID);
        //require_once('includes/kint/Kint.class.php');
        $practs = $clinic->getPlacedPractitioners(false);
        $graphs->plotAll($clinic, $practs);
    }

    public function appts() {
        
        assert_options(ASSERT_CALLBACK, function($file, $line, $code) { echo("$code");});
        $this->registry->template->controller = $this;
        require_once('includes/kint/Kint.class.php');
        
        $OrgID = filter_input(INPUT_GET,'org');
        $ClinicID = filter_input(INPUT_GET,'clin');
        
        $test = new ApptBookTests($OrgID, $ClinicID);
        
        $test->runAppts();
        
    }
    
    
    public function chargeover() {
        $chargeover = new Chargeover();
        require_once('includes/kint/Kint.class.php');
        d($chargeover);
        
        $cust = $chargeover->getCustomer($this->org->OrgID);
        d($cust);
    }

    public function upload() {

        $filename = $_FILES["fileToUpload"]["tmp_name"];
        $target_file = "pri/logos/" . __SUBDOMAIN . ".png";

        $uploadOk = 0;
        $ret = "";
        // Check if image file is a actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if ($check !== false) {
                //echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                $ret .= "File is not an image, ";
                $uploadOk = 0;
            }
        }
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            $ret .= "file is too large,";
            $uploadOk = 0;
        }
        $imageFileType = pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION);
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "only (JPG,JPEG,PNG,GIF) files allowed, ";
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded:" . $ret;
        } else {
            if (imagepng(imagecreatefromstring(file_get_contents($filename)), $target_file)) {
                echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    public function email() {

        $email = filter_input(INPUT_POST, "email");
        $to = filter_input(INPUT_POST, "to");
        $subject = filter_input(INPUT_POST, "subject");
        $body = filter_input(INPUT_POST, "body");
        $from = filter_input(INPUT_POST, "from");
        $fromName = filter_input(INPUT_POST, "fromName");

        $mailer = new Howlate_Mailer();


        $mailer->send2($email, $to, $subject, $body, $from, $fromName);

        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
    }

    public function usage() {
        $OrgID = filter_input(INPUT_POST, "OrgID");
        $billing = new Billing();

        $billing->recordOrgUsage($OrgID);
    }

    public function package() {
        $OrgID = filter_input(INPUT_POST, "OrgID");
        $billing = new Billing();

        $organisation = Organisation::getInstance($OrgID, 'OrgID');
        $billing->createPackage($organisation);
    }

    /*
     * Need a test function to extract vwBillingClinPract
     * which shows a line for every clinic which has practitioners
     * assigned and how many.  It also shows whether the org is integrated
     * i.e. has an agent running
     * 
     * We then need to be able to compare that with the active package
     * If an active package does not exist, then create one
     * If it does exist, then do a line by line comparison
     * Using the external_key = ClinicID
     * Amend Qty if required.
     * and then also check whether an SMS line exists.  Create one if not.
     * 
     */

    public function check_package() {
        require_once("includes/kint/Kint.class.php");

        $OrgID = filter_input(INPUT_POST, "OrgID");
        $billing = new Billing();

        $organisation = Organisation::getInstance($OrgID, 'OrgID');
        $res = $billing->getHowLateDetails($organisation->OrgID);

        d($res);

        $package = $billing->getChargeoverPackage($organisation->OrgID);

        d($package);

        $package_id = $package->package_id;
        $line_items = $package->line_items;
        $first_item = $line_items[0];
        $line_item_id = $first_item->line_item_id;

        d($first_item);
        $response = $billing->upgradePackageLine($package_id, $line_item_id, 8);
        d($response);
    }

    public function create_line() {
        $package_id = filter_input(INPUT_POST, "package_id");
        $line_item_id = filter_input(INPUT_POST, "line_item_id");
        $external_key = filter_input(INPUT_POST, "external_key");
        $descrip = filter_input(INPUT_POST, "descrip");

        $billing = new Billing();
        $response = $billing->upgradePackageLine($package_id, $line_item_id, $descrip, $external_key);
    }
    
    
    private function LateMessageTests() {
        $ret = array(
            0 => array('ActualLate' => 0, 'ExpectedMessage' => 'on time'),
            1 => array('ActualLate' => 24, 'ExpectedMessage' => 'on time'),
            2 => array('ActualLate' => 25, 'ExpectedMessage' => '15 minutes late'),
            3 => array('ActualLate' => 27, 'ExpectedMessage' => '15 minutes late'),
            4 => array('ActualLate' => 28, 'ExpectedMessage' => '20 minutes late'),
            5 => array('ActualLate' => 31, 'ExpectedMessage' => '20 minutes late'),
            6 => array('ActualLate' => 80, 'ExpectedMessage' => 'an hour 10 minutes late'),
            7 => array('ActualLate' => 151, 'ExpectedMessage' => '2 hours 20 minutes late'),
            8 => array('ActualLate' => -65, 'ExpectedMessage' => 'on time'),
            
            9 => array('ActualLate' => 250, 'ExpectedMessage' => '4 hours late'));
        
        return $ret;

    }
    
    private function AppointmentBookTests() {

        $ret[] = array(// all appts are in the future, none have begun
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 0, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 0, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '37800', 'ConsultPredicted' => null)
        );
        
        $ret[] = array(// no appointment has begun
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'no appt has begun', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 0, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 0, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 0, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => 0, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => 0, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => 0, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '40500', 'ConsultPredicted' => null)
        ); 
        $ret[] = array(// first appt started over time and is still going
            0 => array('ConsultationTime' => 32600, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started late, still going', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '39600', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first appt started half an hour late
            0 => array('ConsultationTime' => 34200, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started 0.5h late', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '39600', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first appt started half an hour late, second 25 minutes late and still going at 35100 (9:45)
            0 => array('ConsultationTime' => 34200, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started 1800 late', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 34800, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started 1500 late still going', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '39300', 'ConsultPredicted' => null)
        );

        $ret[] = array(// first and second appts started on time, third patient not there and fourth started early and is still going
            0 => array('ConsultationTime' => 32400, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started ontime', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 33300, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started ontime', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => 'not arrived', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 34200, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started early, still going', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first and second appts started 10m late, then a 15m gap so should be able to catch up
            0 => array('ConsultationTime' => 33000, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'L=600s', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 33900, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => 'L=600s,ends 34800 ', 'ConsultPredicted' => null),
            // gap 2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 35000, 'Duration' => 900, 'ApptType' => '', 'Status' => 'assume starts now', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first four appts started on time.  fourth is not due to finish until 35800, should not start before then
            0 => array('ConsultationTime' => 32400, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'ontime', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 33300, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => 'ontime', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 34200, 'AppointmentTime' => 34200, 'Duration' => 700, 'ApptType' => '', 'Status' => 'ontime', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 34900, 'AppointmentTime' => 34900, 'Duration' => 900, 'ApptType' => '', 'Status' => 'ontime ends 35800', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first is 1800 late, second same and still going.  then huge break.
            0 => array('ConsultationTime' => 34200, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => '1800 late', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 35100, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '1800 late', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first and second did not start.  skipped to third appointment which started late
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'skipped', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 0, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => 'skipped', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 35000, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started late', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 0, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => 0, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => 0, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => 0, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '40400', 'ConsultPredicted' => null)
        );

        $ret[] = array(// 
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'skipped', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 34000, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '34000', 'ConsultPredicted' => null)
        );
        
        
        
        return $ret;
    }
    
    private function AppointmentBookTests2() {
        $ret = array(// no appointment has begun
            0 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '','Status' => 'no appt has begun', 'ConsultPredicted' => null),
            1 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            2 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            3 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            4 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            5 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            6 => array('Provider' => "Dr Anthony Alvano",'ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '40500', 'ConsultPredicted' => null),
            7 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => 32600, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'Status' => 'started late, still going', 'ConsultPredicted' => null),
            8 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            9 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            10 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            11 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            12 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'Status' => '', 'ConsultPredicted' => null),
            13 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'Status' => '39600', 'ConsultPredicted' => null)
        );
        return $ret;
    }
}

?>