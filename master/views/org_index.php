
<?php $controller->get_header(); ?>


<style>
.btn-file {
    position: relative;
    overflow: hidden;
}
.btn-file input[type=file] {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 100%;
    min-height: 100%;
    font-size: 100px;
    text-align: right;
    filter: alpha(opacity=0);
    opacity: 0;
    outline: none;
    background: white;
    cursor: inherit;
    display: block;
}    
    
</style>

<div class='container'>
    <form name="reset" action="/org/upload_logo" method="post" enctype="multipart/form-data">
        <div class="input-group">
            <label for="fileToUpload">Company Logo</label><br>
            <span class="btn btn-default btn-file">Browse...<input type="file" accept="image/*" size=90 name="fileToUpload" id="fileToUpload">
            </span>
            <button class="btn btn-primary" type="submit"name="Submit" value="Submit">Upload</button>
        </div>
    </form>

    <div class="col-xs-12" style="height:50px;"></div>
    
    <form id="org" name="org" method="post" action="/org/update">
        <div class="input-group">
            <label for="OrgName">Organisation Name</label>
            <input type="text" class="form-control" id="OrgName" name="OrgName" size="50" placeholder="Enter Organisation Name" value="<?php echo $controller->org->OrgName; ?>">
        </div>
        <div class="input-group">
            <label for="OrgShortName"">Short Name</label>
            <input type="text" class="form-control" id="OrgShortName" name="OrgShortName" placeholder="Enter Short Name < 24 characters" size="24" value="<?php echo $controller->org->OrgShortName; ?>">
        </div>
        <div class="input-group">
            <label for="Address1"">Address</label>
            <input type="text" class="form-control" id="Address1" name="Address1" size="50" placeholder="Enter Address Line 1" value="<?php echo $controller->org->Address1; ?>">
        </div>   
        <div class="input-group">
            <input type="text" class="form-control" id="Address2" name="Address2" size="50" placeholder="Enter Address Line 2" value="<?php echo $controller->org->Address2; ?>">
        </div>   
        <div class="input-group">
            <label for="City"">City</label>
            <input type="text" class="form-control" id="City" name="City" placeholder="Enter City" size="25" value="<?php echo $controller->org->City; ?>">
        </div>
        <div class="input-group">
            <label for="State"">State</label>
            <input type="text" class="form-control" id="State" name="State" placeholder="Enter State or Territory" size="25" value="<?php echo $controller->org->State; ?>">
        </div>
        <div class="input-group">
            <label for="Zip"">Zip</label>
            <input type="text" class="form-control" id="Zip" name="Zip" placeholder="Enter Zip or Postcode" size="7" value="<?php echo $controller->org->Zip; ?>">
        </div>
        <div class="input-group">
            <label for="Country"">Country</label>
            <select name="Country" id="Country" class="form-control" value="<?php echo $controller->org->Country; ?>" >
                <?php $controller->get_country_options(); ?>
            </select>

        </div>
        <div class="input-group">
            <label for="Timezone"">Time Zone</label>
            <select name="Timezone" id="Timezone" class="form-control" value="<?php echo $controller->org->Timezone; ?>" >
                <?php $controller->get_tz_options(); ?>
            </select>

        </div>
        <button class="btn btn-primary" type="submit" name="Submit" value="Submit">Save Changes</button>

        <input type="hidden" readonly="readonly" id="OrgID" name="OrgID" value="<?php echo $controller->org->OrgID; ?>"><br>
        <input type="hidden" readonly="readonly" id="Subdomain" name="Subdomain" value="<?php echo $controller->org->Subdomain; ?>"><br>
        <input type="hidden" readonly="readonly" id="FQDN" name="FQDN" value="<?php echo $controller->org->FQDN; ?>"><br>
        <input type="hidden" readonly="readonly" id="UpdIndic" name="UpdIndic" value="<?php echo $controller->org->UpdIndic; ?>"><br>

    </form>


</div>
<script>

    var timer;
    var delay = 1000;
    $("#logo-container").hover(
            function() {
                // on mouse in, start a timeout
                timer = setTimeout(function() {
                    $("#nav-upload-logo").css("display", "inline-block");
                }, delay);
            },
            function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    $("#nav-upload-logo").css("display", "none");
                }, 1.5 * delay);
            }
    );


</script>        


<?php $controller->get_footer(); ?>


