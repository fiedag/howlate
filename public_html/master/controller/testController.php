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
    
    
    
}

?>