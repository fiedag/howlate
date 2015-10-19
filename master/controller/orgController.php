<?php

Class OrgController Extends baseController {

    public function index() {

        $this->registry->template->controller = $this;
        $this->registry->template->show('org_index');
    }
    
    public function update() {

        foreach ($_POST as $key => $value) {
            if (isset($this->Organisation->$key)) {
                $org[$key] = $value;
            }
        }

        $this->Organisation->update_org($org);
        $this->Organisation = Organisation::getInstance(__SUBDOMAIN);

        // create or update billing record
        $default_user = OrgUser::getInstance($this->Organisation->OrgID, $_SESSION["USER"]);

        $this->registry->template->controller = $this;       
        $this->registry->template->show('org_index');
    }
    
    public function get_tz_options() {
        $tz = $this->Organisation->getTimezones();
        foreach ($tz as $val) {
            echo "<option value='" . $val . "'";
            
            if ($val == $this->Organisation->Timezone) {
                echo "selected";
            }
            echo ">$val</option>";
        }
    }
    
    public function get_country_options() {
        $c = $this->Organisation->getCountries();
        foreach ($c as $val) {
            echo "<option value='" . $val . "'";
            
            if ($val == $this->Organisation->Country) {
                echo "selected";
            }
            echo ">$val</option>";
        }
    }
    
    
    public function upload_logo() {

        $filename = $_FILES["fileToUpload"]["tmp_name"];
        $target_file = "pri/logos/" . __SUBDOMAIN . ".png";

        $uploadOk = 1;
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
            $ret .= "only (JPG,JPEG,PNG,GIF) files allowed, ";
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
            $ret .= "Sorry, your file was not uploaded:" . $ret;
        } else {
            if ($imageFileType == "png") {
                $ret .= "moving uploaded file $filename to $target_file .";
                $result = move_uploaded_file($filename, $target_file);
            }
            else {
                $result = imagepng(imagecreatefromstring(file_get_contents($filename)), $target_file);
            }
            if ($result) {
                $ret .= "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";
            } else {
                $ret .= "Sorry, there was an error uploading your file.";
            }
        }
        $this->registry->template->fadingmessage = $ret;
        $this->registry->template->controller = $this;
        $this->registry->template->show('org_index');
        
    }
    
    
    
    
}

?>