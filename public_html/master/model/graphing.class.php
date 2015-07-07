<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Graphing {
    
    function __construct() {
        require_once ('includes/jpgraph/src/jpgraph.php');
        require_once ('includes/jpgraph/src/jpgraph_scatter.php');
        require_once ('includes/jpgraph/src/jpgraph_line.php');
        require_once ('includes/jpgraph/src/jpgraph_mgraph.php');
    }
    

    public function plotAll($Clinic, $practs) {
        $mgraph = new MGraph();
        $xpos1 = 3;
        $ypos1 = 3;
        foreach($practs as $key=>$val) {
            $practitioner = Practitioner::getInstance($Clinic->OrgID, $val,'FullName');
            $graph = $this->lateness($Clinic, $practitioner);
            if($graph) {
                $mgraph->Add($graph,$xpos1,$ypos1);
                $ypos1 += 420;
            }
        }
        $mgraph->Stroke();
    }
    
    public function lateness($Clinic, $Practitioner) {
        $ret = $this->getPractitionerPlot($Clinic, $Practitioner);
        if(!$ret) {
            return null;
        }
        $x_labels = $ret[0];
        $y_points = $ret[1];
        
        //d($Practitioner->FullName);
        //d($ret);

        if(count($x_labels) == 0) {
            return; 
        }
        $graph = new Graph(1200,400);
        $graph->SetScale("linlin");

        $graph->img->SetMargin(40, 40, 40, 40);
        $graph->SetShadow();

        $graph->title->Set($Practitioner->FullName);
        $graph->subtitle->Set('Lateness on ' . date("d M Y"));
        $graph->title->SetFont(FF_FONT1, FS_BOLD);

        $graph->xaxis->SetTickLabels($x_labels);
        $graph->xgrid->Show(true,false);
        
        //$graph->xaxis->scale->ticks->SupressLast();
        
        $graph->xaxis->title->Set("Time of day");
        $graph->yaxis->title->Set("Minutes late");
        $linePlot = new LinePlot($y_points);
        
        $graph->Add($linePlot);
        return $graph;
    }
    
    
    
    private function getPractitionerPlot($Clinic, $Practitioner) {
        $q = "SELECT DATE_FORMAT(Timestamp,'%l:%i%p') As Timestamp, Late FROM vwDaysLog WHERE OrgID = '$Practitioner->OrgID' AND ClinicName = '$Clinic->ClinicName' AND FullName = '$Practitioner->FullName'";
        //echo $q;
        $sql = MainDb::getInstance();

        $result = $sql->query($q);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $x_array[] = $row->Timestamp;
                $y_array[] = $row->Late ;
            }
            //echo $Clinic->ClinicName . "," . $Practitioner->FullName . $result->num_rows .  " rows <br>";
            return array($x_array,$y_array);
        }
        return null;
    }
    
}