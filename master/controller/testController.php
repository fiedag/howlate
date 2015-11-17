<?php

Class TestController Extends baseController {

    public $clinic;
    public $appointments;

    
    public function udid() {
        $number = filter_input(INPUT_GET, "number");
        $i = new HowLate_Phone($number, null, false);
        
        $url="http://m.howlate.com/late?xudid=";
        echo "<a href='$url$i->XUDID'>$url$i->XUDID</a><br>";
    }
    public function xudid() {
        $xudid = filter_input(INPUT_GET, "xudid");
        $i = new HowLate_Phone($xudid, null, true);
        $url="http://m.howlate.com/late?udid=";
        echo "<a href='$url$i->CanonicalMobile'>$url$i->CanonicalMobile</a><br>";
        
    }
    
    public function testtime() {
        $i = HowLate_Time::fromMinutes(60);
        
        
        
    }
    
    public function testappt5() {
        $appt_list = array(
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Away','ArrivalTime' => '0','AppointmentTime' => '31400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => 'Meeting','ArrivalTime' => '','AppointmentTime' => '34300',
                'ConsultationTime' => '','Duration' => '50','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '32400',
                'ConsultationTime' => '32400','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '33400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61403569377','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Meeting','ArrivalTime' => '','AppointmentTime' => '34300',
                'ConsultationTime' => '','Duration' => '50','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Away','ArrivalTime' => '','AppointmentTime' => '34350',
                'ConsultationTime' => '','Duration' => '50','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '32020','AppointmentTime' => '34400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '32000','AppointmentTime' => '34450',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '35400',
                'ConsultationTime' => '33450','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => 'Away','ArrivalTime' => '0','AppointmentTime' => '31400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61403569377','ConsentSMS' => '1'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '32400',
                'ConsultationTime' => '32400','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '33400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '0405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => 'Away','ArrivalTime' => '','AppointmentTime' => '34350',
                'ConsultationTime' => '','Duration' => '50','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '32020','AppointmentTime' => '34400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '32000','AppointmentTime' => '34450',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0'),
            array ('Provider' => 'Dr J Marion Sims','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '35400',
                'ConsultationTime' => '33450','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0')
            
            );
        
        
        $time_now = 33460;
        $_POST['appt'] = $appt_list;
   
        $_POST = array('credentials'=>'demo.e8dc4081b13434b45189a720b77b6818','time_now'=>$time_now,'appt'=>$appt_list);

        $Clinic = Clinic::getInstance($this->Organisation->OrgID,139);
        $api = new AgentApi($this->Organisation, $Clinic);

        include_once __SITE_PATH . '/includes/kint/Kint.class.php';
        d($appt_list);
        
        $ret = $api->appt();

        d($time_now);
        d($ret->Appointments);
   }
    
    
    public function testappt6() {
        $appt_list = array(
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '27400',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '','ConsentSMS' => '0')
            );
        
        
        $time_now = 33460;
        $_POST['appt'] = $appt_list;
   
        $_POST = array('credentials'=>'demo.e8dc4081b13434b45189a720b77b6818','time_now'=>$time_now,'appt'=>$appt_list);

        $Clinic = Clinic::getInstance($this->Organisation->OrgID,139);
        $api = new AgentApi($this->Organisation, $Clinic);

        include_once __SITE_PATH . '/includes/kint/Kint.class.php';
        d($appt_list);
        
        $ret = $api->appt();

        d($time_now);
        d($ret->Appointments);
   }

   public function testappt7() {
        $appt_list = array(
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '32400',
                'ConsultationTime' => '32460','Duration' => '900','MobilePhone' => '49666765765','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '33000',
                'ConsultationTime' => '33060','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '33300',
                'ConsultationTime' => '33360','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '32400',
                'ConsultationTime' => '32460','Duration' => '1800','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '35900','AppointmentTime' => '36000',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '35920','AppointmentTime' => '36900',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '37800',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Away','ArrivalTime' => '','AppointmentTime' => '39600',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Away','ArrivalTime' => '','AppointmentTime' => '43200',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Lunch','ArrivalTime' => '','AppointmentTime' => '46800',
                'ConsultationTime' => '','Duration' => '3000','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => 'Lunch','ArrivalTime' => '','AppointmentTime' => '57600',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '58500',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '59100',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '60000',
                'ConsultationTime' => '','Duration' => '900','MobilePhone' => '61405149704','ConsentSMS' => '1')
            );
       
        $time_now = 58000;
        $_POST['appt'] = $appt_list;
   
        $_POST = array('credentials'=>'demo.e8dc4081b13434b45189a720b77b6818','time_now'=>$time_now,'appt'=>$appt_list);

        $Clinic = Clinic::getInstance($this->Organisation->OrgID,139);
        $api = new AgentApi($this->Organisation, $Clinic);

        include_once __SITE_PATH . '/includes/kint/Kint.class.php';
        d($appt_list);
        
        $ret = $api->appt();

        d($time_now);
        d($ret->Appointments);
       
       
   }
    public function sms() {
        $to = filter_input(INPUT_GET, "to");

        if(!$to) {
            throw new Exception("Must supply ?to parameters");
        }
        
        $message = 'simple text message';

        $sms = new HowLate_SMS();
        $sms->httpSend('CCEOW', $to, $message);

        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
        
        
    }
    public function mocksms() {
        $to = filter_input(INPUT_GET, "to");

        if(!$to) {
            throw new Exception("Must supply ?to parameters");
        }
        
        $message = 'simple mock text message';

        $sms = new HowLate_SMS(new MockClickatell());
        $sms->httpSend('CCEOW', $to, $message);

        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
        
        
    }
    
    
    
    public function email() {
        $to = filter_input(INPUT_GET, "to");

        if(!$to) {
            throw new Exception("Must supply ?to parameters");
        }
        
        $mailer = new Howlate_Mailer();
        
        $subject = 'simple test email';
        $body = '<h1>body of simple test email</h1>';
        $from = 'dev@howlate.com';

        $mailer->sendhtml($to, $to, $subject, $body, $from, $from);

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
    
      public function index333() {
        $graphs = new Graphing();
        
        $ClinicID = filter_input(INPUT_GET,"clin");
        if(!$ClinicID) {
            throw new Exception("Need a ?clin parameter");
        }
        
        $clinic = Clinic::getInstance($this->Organisation->OrgID, $ClinicID);
        //require_once('includes/kint/Kint.class.php');
        $practs = $clinic->getPlacedPractitioners(false);
        $graphs->plotAll($clinic, $practs);
        
    }
    public function notify() {
        $OrgID = filter_input(INPUT_GET,"org");
        $ClinicID = filter_input(INPUT_GET,"clin");

        $test = new ApptBookTests($OrgID, $ClinicID);
        $test->testNotify();
    }
    public function log() {
        $ClinicID = filter_input(INPUT_GET,"clin");

        if(!$ClinicID) {
            throw new Exception("Clinic ID ?clin must be given");
            
        }
            
        $test = new ApptBookTests($this->Organisation->OrgID, $ClinicID);
        $test->displayLog();
    }
    public function phpinfo() {
        echo phpinfo();
    }
    
    public function index() {
        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
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
        
        $cust = $chargeover->getCustomer($this->Organisation->OrgID);
        d($cust);
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
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 0, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 0, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '37800', 'ConsultPredicted' => null)
        );
        
        $ret[] = array(// no appointment has begun
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'no appt has begun', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 0, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 0, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 0, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => 0, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => 0, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => 0, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '40500', 'ConsultPredicted' => null)
        ); 
        $ret[] = array(// first appt started over time and is still going
            0 => array('ConsultationTime' => 32600, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started late, still going', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '39600', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first appt started half an hour late
            0 => array('ConsultationTime' => 34200, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started 0.5h late', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '39600', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first appt started half an hour late, second 25 minutes late and still going at 35100 (9:45)
            0 => array('ConsultationTime' => 34200, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started 1800 late', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 34800, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started 1500 late still going', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '39300', 'ConsultPredicted' => null)
        );

        $ret[] = array(// first and second appts started on time, third patient not there and fourth started early and is still going
            0 => array('ConsultationTime' => 32400, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started ontime', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 33300, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started ontime', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'not arrived', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 34200, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started early, still going', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first and second appts started 10m late, then a 15m gap so should be able to catch up
            0 => array('ConsultationTime' => 33000, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'L=600s', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 33900, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'L=600s,ends 34800 ', 'ConsultPredicted' => null),
            // gap 2 => array('ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 35000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'assume starts now', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first four appts started on time.  fourth is not due to finish until 35800, should not start before then
            0 => array('ConsultationTime' => 32400, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'ontime', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 33300, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'ontime', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 34200, 'AppointmentTime' => 34200, 'Duration' => 700, 'ApptType' => '', 'ApptStatus' => 'ontime', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 34900, 'AppointmentTime' => 34900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'ontime ends 35800', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first is 1800 late, second same and still going.  then huge break.
            0 => array('ConsultationTime' => 34200, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '1800 late', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 35100, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '1800 late', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '37800', 'ConsultPredicted' => null)
        );
        $ret[] = array(// first and second did not start.  skipped to third appointment which started late
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'skipped', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 0, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'skipped', 'ConsultPredicted' => null),
            2 => array('ConsultationTime' => 35000, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started late', 'ConsultPredicted' => null),
            3 => array('ConsultationTime' => 0, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('ConsultationTime' => 0, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('ConsultationTime' => 0, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('ConsultationTime' => 0, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '40400', 'ConsultPredicted' => null)
        );

        $ret[] = array(// 
            0 => array('ConsultationTime' => 0, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'skipped', 'ConsultPredicted' => null),
            1 => array('ConsultationTime' => 34000, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '34000', 'ConsultPredicted' => null)
        );
        
        
        
        return $ret;
    }
    
    private function AppointmentBookTests2() {
        $ret = array(// no appointment has begun
            0 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '','ApptStatus' => 'no appt has begun', 'ConsultPredicted' => null),
            1 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            2 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            3 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            4 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            5 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            6 => array('Provider' => "Dr Joseph Lister",'ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '40500', 'ConsultPredicted' => null),
            7 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => 32600, 'AppointmentTime' => 32400, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => 'started late, still going', 'ConsultPredicted' => null),
            8 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 33300, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            9 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 34200, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            10 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 35100, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            11 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 36000, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            12 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 36900, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '', 'ConsultPredicted' => null),
            13 => array('Provider' => "Dr Natasha Litjens",'ConsultationTime' => null, 'AppointmentTime' => 37800, 'Duration' => 900, 'ApptType' => '', 'ApptStatus' => '39600', 'ConsultPredicted' => null)
        );
        return $ret;
    }
    
    
    
    
}

?>