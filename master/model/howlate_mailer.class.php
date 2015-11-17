<?php

class Howlate_Mailer {
    private $Host;
    private $Username;
    private $Password;
    private $mail;

    function __construct($Host = 'how-late.com',$Username = 'alex@how-late.com', $Password = 'd5yJHg7EPd') {
        $this->Host = $Host;
        $this->Username = $Username;
        $this->Password = $Password;
        include_once(__SITE_PATH . '/includes/PHPMailer-master/PHPMailerAutoload.php');
    }

    public function send($toEmail, $toName, $subject, $body, $from, $fromName) {
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = 'utf-8';

        if (!PHPMailer::validateAddress($toEmail)) {
            throw new phpmailerAppException("Email address " . $toEmail . " is invalid -- aborting!");
        }
        $this->mail->isSMTP();
        $this->mail->isHTML(false);
        $this->mail->SMTPDebug = false;  // change to true and expect a lot of output

        $this->mail->Host = $this->Host;
        $this->mail->Port = "25";
        //$this->mail->SMTPSecure = "tls";  neither tls nor SSL will work
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->Username;
        $this->mail->Password = $this->Password;
        //$this->mail->addReplyTo($this->Username, "How-Late.Com");
        $this->mail->addReplyTo($from, $this->Host);
        $this->mail->From = $from;
        $this->mail->FromName = $fromName;
        $this->mail->addAddress($toEmail, $toName);
        $this->mail->Subject = $subject;

        $this->mail->WordWrap = 80;
        $this->mail->Body = $body;
        
        $this->mail->send();
    }

    public function sendHtml($toEmail, $toName, $subject, $body, $from, $fromName) {
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = 'utf-8';

        if (!PHPMailer::validateAddress($toEmail)) {
            throw new phpmailerAppException("Email address " . $toEmail . " is invalid -- aborting!");
        }
        $this->mail->isSMTP();
        $this->mail->isHTML(true);

        $this->mail->Host = $this->Host;
        $this->mail->Port = "25";
        //$this->mail->SMTPSecure = "tls";
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->Username;
        $this->mail->Password = $this->Password;
        $this->mail->addReplyTo($from, $this->Host);
        $this->mail->From = $from;
        $this->mail->FromName = $fromName;
        $this->mail->addAddress($toEmail, $toName);
        $this->mail->Subject = $subject;

        $this->mail->WordWrap = 80;
        $this->mail->Body = $body;
        
        $this->mail->send();
    }


}

?>