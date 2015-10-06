<div class='container primary-content'>
<script>
    
    $( document ).ready(function() {
      $(":button").click(
          function() {
              retrieveTest($( this ).attr("data-OrgID"), $( this ).attr("data-ClinicID"), $( this ).attr("data-TestIndex") );
          } 
      );
    }
    );
      
    function retrieveTest(OrgID, ClinicID, TestIndex) {
        url = "https://admin.how-late.com/testing/retrieveTest?OrgID=" + OrgID + "&ClinicID=" + ClinicID + "&TestIndex=" + TestIndex;
        //alert(url);
        $("#apptbulk-" + TestIndex).html(url);
        
        $.get(url, function(result) {
            $('#apptbulk-' + TestIndex).html(result);
            //alert(result);
        });
    }
    
    function calcLateness(OrgID, ClinicID, TestIndex) {
        url = "https://admin.how-late.com/testing/calcLateness?OrgID=" + OrgID + "&ClinicID=" + ClinicID + "&TestIndex=" + TestIndex;
        //alert(url);
        $("#lateness-" + TestIndex).html(url);
        
        $.get(url, function(result) {
            $('#lateness-' + TestIndex).html(result);
            
        });
        
        
        
    }
    
    
</script>    
    
<br>

    