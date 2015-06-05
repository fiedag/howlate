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
        return $appts;
    }
    
    
    public function test1() {
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
                
                //$seconds_late = $p->AppointmentBook->getLateness($time_now);
                //echo $p->PractitionerName  . " after lateness predictions <br>";
                if($p->AppointmentBook->CurrentLate) {
                    if(!$this->expectedLateness($PractitionerName, $time_now, $p->AppointmentBook->CurrentLate)) {
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
        
        foreach($expectations as $key=>$val) {
            if($val['PractitionerName'] == $PractitionerName && $val['TimeNow'] == $TimeNow) {
                if($val['Lateness'] != $CurrentLate) {
                    $msg .= "FAILED";
                    echo $msg;
                }
                return ($val['Lateness'] == $CurrentLate);
            }
        }
        echo $msg;
    }
    
}
