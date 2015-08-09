<div class='container primary-content'>

    
    <table>        
    
<?php 
  if($tests) {
      
      
      foreach($tests->Iterations as $test) {
          $summary = $test['Summary'];
          //d($summary);
          
          $controller->displaySummary($summary);
          
          
          
      }
  } 
  
?>

    </table>
        
</div>

