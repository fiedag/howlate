<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-08 at 15:53:53.
 */
class AgentApiTest extends PHPUnit_Framework_TestCase {

    /**
     * @var AgentApi
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $Organisation = Organisation::getInstance('CCEOW', 'OrgID');
        $Clinic = Clinic::getInstance($Organisation->OrgID, 139);

        $this->object = new AgentApi($Organisation, $Clinic);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers AgentApi::verb2
     * @todo   Implement testVerb2().
     */
    public function testNotEmpty() {
        // Remove the following lines when you implement this test.
        $this->assertNotEmpty($this->object);
    }

    // tests a simple lateness update
    public function testupd() {
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'Practitioner' => 'Dr Joseph Lister', 'NewLate' => 55);
        $ret = $this->object->upd();
        $this->assertEquals($ret['PractitionerName'], 'Dr Lister');
        $this->assertEquals($ret['New Late'], '55');
    }

    // does exception handling work ok
    public function testException() {
        // test that exception handling is ok
        try {
            $ret = $this->object->throwex();
            $this->assertTrue(false);
        } catch (Exception $ex) {
            $this->assertTrue(true);
        }
    }

    // session record update.
    public function testsess() {
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'Day' => 'Friday', 'StartTime' => 32400, 'EndTime' => 61200, 'Practitioner' => 'Dr Joseph Lister');
        $ret = $this->object->sess();
        $this->assertEquals($ret, 1);  // ensures one row was updated
    }

    public function testappttype() {
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'TypeCode' => 'Meeting', 'TypeDescr' => 'Meeting');
        $ret = $this->object->appttype();
    }

    public function testapptstatus() {
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'StatusCode' => 'Away', 'StatusDesc' => 'Away');
        $ret = $this->object->apptstatus();
    }

    // one appointment, not begun.  Should start at $time_now
    public function testappt1() {

        $appt_list = array(array(
                'Provider' => 'Dr Joseph Lister',
                'ApptStatus' => 'Booked',
                'ApptType' => '',
                'ArrivalTime' => '0',
                'AppointmentTime' => '27900',
                'ConsultationTime' => '0',
                'ConsultPredicted' => '',
                'Duration' => '900',
                'MobilePhone' => '',
                'ConsentSMS' => '0'
        ));

        $time_now = 36500;
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();


        $this->assertEquals($ret->Appointments[0]['Sequence'], 0, 'Other is configured to be auto-consulted');
        $this->assertEquals($ret->Appointments[0]['ConsultPredicted'], $time_now, 'Assume the appt occurs any moment now');
    }

    // one appt, started right now at $time_now, checking status is WITHDOCTOR
    public function testappt2() {
        $appt_list = array(
            array('Provider' => 'Dr Joseph Lister', 'ApptStatus' => 'Booked', 'ApptType' => '', 'ArrivalTime' => '0', 'AppointmentTime' => '32400',
                'ConsultationTime' => '32400', 'ConsultPredicted' => '', 'Duration' => '900', 'MobilePhone' => '', 'ConsentSMS' => '0')
        );

        $time_now = 32400;
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();
        $this->assertEquals($ret->Appointments[0]['Sequence'], 0, 'Sequence');
        $this->assertEquals($ret->Appointments[0]['Processing'], 'WITHDOCTOR', 'Processing Status');
        $this->assertEquals($ret->Appointments[0]['ConsultPredicted'], 32400, 'Consultation time is already given');
    }

    // one appt, started 900 seconds ago and so should be complete at $time_now
    public function testappt3() {
        $appt_list = array(
            array('Provider' => 'Dr Joseph Lister', 'ApptStatus' => 'Booked', 'ApptType' => '', 'ArrivalTime' => '0', 'AppointmentTime' => '32400',
                'ConsultationTime' => '32400', 'ConsultPredicted' => '', 'Duration' => '900', 'MobilePhone' => '', 'ConsentSMS' => '0')
        );

        $time_now = 33300;
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();
        $this->assertEquals($ret->Appointments[0]['Sequence'], 0, 'Sequence');
        $this->assertEquals($ret->Appointments[0]['Processing'], 'WITHDOCTOR', 'Processing Status');
        $this->assertEquals($ret->Appointments[0]['ConsultPredicted'], 32400, 'Consultation time is already given');
    }

    // one appt, assumed complete at 33300, so mark as done
    public function testappt4() {
        $appt_list = array(
            array('Provider' => 'Dr Joseph Lister', 'ApptStatus' => 'Booked', 'ApptType' => '', 'ArrivalTime' => '0', 'AppointmentTime' => '32400',
                'ConsultationTime' => '32400', 'ConsultPredicted' => '', 'Duration' => '900', 'MobilePhone' => '', 'ConsentSMS' => '0')
        );

        $time_now = 33301;
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();
        $this->assertEquals($ret->Appointments[0]['Sequence'], -1, 'Sequence');
        $this->assertEquals($ret->Appointments[0]['Processing'], 'DONE', 'Processing Status');
    }

    // two appts for same dr.  second appt should be starting $time_now, making it 1 minute late
    public function testappt5() {
        $appt_list = array(
            array('Provider' => 'Dr Joseph Lister', 'ApptStatus' => 'Booked', 'ApptType' => '', 'ArrivalTime' => '0', 'AppointmentTime' => '32400',
                'ConsultationTime' => '32400', 'ConsultPredicted' => '', 'Duration' => '900', 'MobilePhone' => '', 'ConsentSMS' => '0'),
            array('Provider' => 'Dr Joseph Lister', 'ApptStatus' => 'Booked', 'ApptType' => '', 'ArrivalTime' => '0', 'AppointmentTime' => '33400',
                'ConsultationTime' => '', 'ConsultPredicted' => '', 'Duration' => '900', 'MobilePhone' => '', 'ConsentSMS' => '0')
        );

        $time_now = 33460;
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();

        $this->assertEquals($ret->Appointments[0]['Processing'], 'DONE', 'Processing Status');
        $this->assertEquals($ret->Appointments[0]['Sequence'], -1, 'Processing Status');
        $this->assertEquals($ret->Appointments[1]['Sequence'], 0, 'Processing Status');
        $this->assertEquals($ret->Appointments[1]['ConsultPredicted'], $time_now, "This appt should start at $time_now.");
        $this->assertEquals($ret->Appointments[1]['SecondsLate'], 60);
    }

    // sixteen appts, array is out of order. must still work.  appt with Lister at 34400 must be 850s late
    public function testappt7() {
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
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();

        $specific = array_filter($ret->Appointments, 
            function($item) { 
            return ($item['Provider'] == 'Dr Joseph Lister' 
                    && $item['AppointmentTime'] == '34400');});
        $first_row = array_values($specific)[0];
        $this->assertEquals($first_row['SecondsLate'],850);
    }
    
    
    // appointments spaced at 10 minute intervals with durations of 15 minutes.
    // duration should not interfere with anything.
    // and the dr should be on time afterwards
    public function testappt8() {
        $appt_list = array(
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '32400',
                'ConsultationTime' => '32400','Duration' => '900','MobilePhone' => '49666765765','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '33000',
                'ConsultationTime' => '33000','Duration' => '50','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '33600',
                'ConsultationTime' => '33600','Duration' => '50','MobilePhone' => '61405149704','ConsentSMS' => '1')
            );

        $time_now = 43200;  // noon
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();

   
        $specific = array_filter($ret->Appointments, 
            function($item) { 
            return ($item['Provider'] == 'Dr Joseph Lister' 
                    && $item['AppointmentTime'] == '33600');});
        $first_row = array_values($specific)[0];
        $this->assertEquals($first_row['SecondsLate'],'');
    
    }

    // first and second appt are concurrent
    // aggregate duration = 900
    public function testappt9() {
        $appt_list = array(
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '0','AppointmentTime' => '32400',
                'ConsultationTime' => '32400','Duration' => '900','MobilePhone' => '49666765765','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '32400',
                'ConsultationTime' => '33000','Duration' => '50','MobilePhone' => '61405149704','ConsentSMS' => '1'),
            array ('Provider' => 'Dr Joseph Lister','ApptStatus' => 'Booked','ApptType' => '','ArrivalTime' => '','AppointmentTime' => '33600',
                'ConsultationTime' => '','Duration' => '50','MobilePhone' => '61405149704','ConsentSMS' => '1')
            );

        $time_now = 34600;
        $_POST['appt'] = $appt_list;
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();

        var_dump($ret->Appointments);
        
   
        $specific = array_filter($ret->Appointments, 
            function($item) { 
            return ($item['Provider'] == 'Dr Joseph Lister' 
                    && $item['AppointmentTime'] == '33600');});
        $first_row = array_values($specific)[0];
        $this->assertEquals($first_row['SecondsLate'],1000);
    
    }

    
    // first and second appt are concurrent
    // aggregate duration = 900
    public function testappt10() {
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
        $_POST = array('credentials' => 'demo.e8dc4081b13434b45189a720b77b6818', 'time_now' => $time_now, 'appt' => $appt_list);

        $ret = $this->object->appt();

        var_dump($ret->Appointments);
        
   
        $specific = array_filter($ret->Appointments, 
            function($item) { 
            return ($item['AppointmentTime'] == '58500');});
        $first_row = array_values($specific)[0];
            $this->assertEquals($first_row['SecondsLate'],400);
    
    }

}
