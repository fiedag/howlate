<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of apptbooktests
 *
 * @author Alex
 */

class ApptBookTests {
    protected $OrgID;
    protected $ClinicID;
    protected $File;
    
    protected $Iterations;
    
    function __construct($OrgID, $ClinicID) {
        $this->OrgID = $OrgID;
        $this->ClinicID = $ClinicID;
        $this->File = "/home/howlate/public_html/master/logs/" . $this->OrgID . "." . $this->ClinicID . ".log.inc";

        $this->Iterations = $this->read($this->File);
        
    }
    
    private function read($file) {
        include_once($file);
        return array_reverse($appts);
    }
    

    public function runNotify() {
        $this->orgNotify($this->OrgID);
        
    }

    private function orgNotify($OrgID) {
        
        foreach($this->Iterations as $key1=>$iteration) {
            $time_now = $iteration['Time Now'];
            $summary = $iteration['Summary'];

            $results = null;
            foreach($summary as $key2=>$pract_summary) {
                $PractitionerName = $pract_summary['Practitioner'];
                $p = Practitioner::getInstance($this->OrgID,$PractitionerName, "PractitionerName");
                $original = $pract_summary['Original'];
                $p->setAppointmentBook($original, $time_now);
                
                $p->predictConsultTimes();
                d($p->AppointmentBook);
            }
        }
    }
    
    
    public function displayLog() {
        require_once('includes/kint/Kint.class.php');

        foreach($this->Iterations as $key1=>$iteration) {
            $summ = $iteration['Summary'][0];
            $pred = $summ['Predicted'];
            d($summ);
        }
        
    }

    
    public function testNotify() {
        require_once('includes/kint/Kint.class.php');

        //$notifier = new Notifier();
        
        foreach($this->Iterations as $key1=>$iteration) {
            $summ = $iteration['Summary'][0];
            
            $PractitionerName = $summ["Practitioner"];
            $OrgID = $iteration['OrgID'];
            $ClinicID = $iteration['ClinicID'];
            $time_now = $iteration['Time Now'];
            
            $orig = $summ['Original'];
            $notifier = new Notifier($OrgID,$ClinicID, $orig, $time_now);
                    
            $pred = $summ['Predicted'];
            d($iteration);
            
            $Practitioner = Practitioner::getInstance($OrgID,$PractitionerName,"FullName");
            $notifier->processNotifications($Practitioner, $pred);
            
            d($notifier->notified_candidates);
        }
        
    }
    
    
    
    /*
     * 
     * Called from testController
     * Permits us to read 
     */
    public function runAppts() {
        $this->orgAppts($this->OrgID);
    }
    
    private function orgAppts($OrgID) {
        
        foreach($this->Iterations as $key1=>$iteration) {
            $time_now = $iteration['Time Now'];
            $summary = $iteration['Summary'];

            $results = null;
            foreach($summary as $key2=>$pract_summary) {
                $PractitionerName = $pract_summary['Practitioner'];
                $p = Practitioner::getInstance($this->OrgID,$PractitionerName, "PractitionerName");
                $original = $pract_summary['Original'];
                $p->setAppointmentBook($original, $time_now);
                
                $p->predictConsultTimes();
                
                if($p->AppointmentBook->CurrentLate) {
                    if(!$this->expectedLateness($PractitionerName, $time_now, $p->AppointmentBook->CurrentLate)) {
                        //$p->notifyPatients();
                        d($p->AppointmentBook);
                    }
                    
                    
                } 
                $results[] = array("Practitioner" => $p->PractitionerName, "Time Now" => $p->AppointmentBook->time_now, "Current Late" => $p->AppointmentBook->CurrentLate);
            }
            //d($results);
        }
    }
    
    private function expectedLateness($PractitionerName, $TimeNow, $CurrentLate) {
        $msg = '    $expectations[]' . " = array('PractitionerName'=>'$PractitionerName', 'TimeNow'=>$TimeNow, 'Lateness'=>$CurrentLate); <br>";

        $method = "apptExpectations_" . $this->OrgID;
        $expectations = $this->$method();
        foreach($expectations as $key=>$val) {
            if($val['PractitionerName'] == $PractitionerName && $val['TimeNow'] == $TimeNow) {
                if($val['Lateness'] != $CurrentLate) {
                    $msg .= "FAILED, should be " . $val['Lateness'];
                    echo $msg;
                }
                return ($val['Lateness'] == $CurrentLate);
            }
        }
        echo $msg;
    }

    private function apptExpectations_CCEPX() {
        $expectations[] = array('PractitionerName'=>'Dr McPhee (Ruby St)', 'TimeNow'=>53229, 'Lateness'=>1629); 
        return $expectations;
    }
    
    private function apptExpectations_CCECK() {
        $expectations[] = array('PractitionerName' => 'Mrs Tammie Boxhall', 'TimeNow'=>49563, 'Lateness'=>6363);
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>49563, 'Lateness'=>4302);
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>49683, 'Lateness'=>4302);
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>49683, 'Lateness'=>6483); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>49803, 'Lateness'=>4302); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>49803, 'Lateness'=>6603); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>49923, 'Lateness'=>4302); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>49923, 'Lateness'=>6723); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50043, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50043, 'Lateness'=>6843); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50163, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50163, 'Lateness'=>6963); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50283, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50283, 'Lateness'=>7083); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>50403, 'Lateness'=>3); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50403, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50403, 'Lateness'=>7203); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>50523, 'Lateness'=>123); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50403, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50523, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50523, 'Lateness'=>7323); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>50643, 'Lateness'=>243); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50643, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50643, 'Lateness'=>7443); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>50763, 'Lateness'=>363); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50763, 'Lateness'=>5837); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50763, 'Lateness'=>7563); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>50883, 'Lateness'=>483); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>50883, 'Lateness'=>483); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>50883, 'Lateness'=>7683); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51003, 'Lateness'=>603); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>51003, 'Lateness'=>603); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51003, 'Lateness'=>7803); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51123, 'Lateness'=>723); 
        
        /*
         * this one is funny because patient is waiting but for a much later appt.  may not get seen first
         */
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>51123, 'Lateness'=>723); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51123, 'Lateness'=>7923); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51243, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>51243, 'Lateness'=>843); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51243, 'Lateness'=>8043); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51363, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>51363, 'Lateness'=>963); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51363, 'Lateness'=>8163); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51483, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51483, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51603, 'Lateness'=>729); 
        
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51603, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51723, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51723, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>51843, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>51843, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>52083, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>52083, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>52203, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>52203, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>52323, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>52323, 'Lateness'=>1923); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>52323, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>52443, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>52443, 'Lateness'=>2043); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>52443, 'Lateness'=>729);
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>52443, 'Lateness'=>1052); 
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>52563, 'Lateness'=>729); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>52563, 'Lateness'=>2163); 
        $expectations[] = array('PractitionerName'=>'Mrs Tammie Boxhall', 'TimeNow'=>52563, 'Lateness'=>1052); 

        
        // from 9 June
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>47909, 'Lateness'=>5266); 
        $expectations[] = array('PractitionerName'=>'Ms Sandra Turvey', 'TimeNow'=>47909, 'Lateness'=>2564); 
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>47789, 'Lateness'=>5266); 
        $expectations[] = array('PractitionerName'=>'Ms Sandra Turvey', 'TimeNow'=>47789, 'Lateness'=>2564); 
        $expectations[] = array('PractitionerName'=>'Ms Sandra Turvey', 'TimeNow'=>47549, 'Lateness'=>2564); 
        $expectations[] = array('PractitionerName'=>'Ms Sandra Turvey', 'TimeNow'=>47669, 'Lateness'=>2564); 
        
        $expectations[] = array('PractitionerName'=>'Dr Daniel Oliveira', 'TimeNow'=>57630, 'Lateness'=>1830);
        $expectations[] = array('PractitionerName'=>'Dr Campbell', 'TimeNow'=>57630, 'Lateness'=>3576); 
        
        
        
        return $expectations;
    }
    

    private function apptExpectations_AAADD() {
        //$expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>45776, 'Lateness'=>1676); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>45896, 'Lateness'=>1796); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>46016, 'Lateness'=>1916); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>46136, 'Lateness'=>2036); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>46256, 'Lateness'=>2156); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>46376, 'Lateness'=>2276); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>46496, 'Lateness'=>2396); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47228, 'Lateness'=>4028); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47348, 'Lateness'=>4148); 
        
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47468, 'Lateness'=>4268); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47588, 'Lateness'=>2588); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47708, 'Lateness'=>1808);
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47828, 'Lateness'=>1928); 
        $expectations[] = array('PractitionerName'=>'Dr Anthony Alvano', 'TimeNow'=>47948, 'Lateness'=>2048); 

        
        
        return $expectations;
    }
    
    
    
}
