<?php

Class testController Extends baseController {

    public function index() {

        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
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

        $email = filter_input(INPUT_POST,"email");
        $to = filter_input(INPUT_POST,"to");
        $subject = filter_input(INPUT_POST,"subject");
        $body = filter_input(INPUT_POST,"body");
        $from = filter_input(INPUT_POST,"from");
        $fromName = filter_input(INPUT_POST,"fromName");
        
        $mailer = new howlate_mailer();
        
        
        $mailer->send2($email, $to, $subject, $body, $from, $fromName);
        
        $this->registry->template->controller = $this;
        $this->registry->template->show('test_index');
    }
    
    public function usage() {
        $OrgID = filter_input(INPUT_POST,"OrgID");
        $billing = new billing();
        
        $billing->recordOrgUsage($OrgID);
        
    }
    
    public function package() {
        $OrgID = filter_input(INPUT_POST,"OrgID");
        $billing = new billing();
        
        $organisation = organisation::getInstance($OrgID, 'OrgID');
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
        
        $OrgID = filter_input(INPUT_POST,"OrgID");
        $billing = new billing();
        
        $organisation = organisation::getInstance($OrgID, 'OrgID');
        $res = $billing->getHowLateDetails($organisation->OrgID);

        d($res);
        
        $package = $billing->getChargeoverDetails($organisation->OrgID);

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
        $package_id = filter_input(INPUT_POST,"package_id");
        $line_item_id = filter_input(INPUT_POST,"line_item_id");
        $external_key = filter_input(INPUT_POST,"external_key");
        $descrip = filter_input(INPUT_POST, "descrip");
        
        $billing = new billing();
        $response = $billing->upgradePackageLine($package_id, $line_item_id, $descrip, $external_key);
        
    }
    
}

?>