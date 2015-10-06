<?php

class Howlate_Mailer {
    private $Host;
    private $Username;
    private $Password;
    private $mail;

    function __construct($Host = null,$Username = null, $Password = null) {
        if(empty($Host)) {
            $this->Host = 'how-late.com';
        }
        else {
            $this->Host = $Host;
        }
        
        if(empty($Username)) {
            $this->Username = HowLate_Util::noreplySmtpUsername();
        }
        else {
            $this->Username = $Username;
        }
        if(empty($Password)) {
            $this->Password = HowLate_Util::noreplySmtpPassword();
        }
        else {
            $this->Password = $Password;
        }
        include('includes/PHPMailer-master/PHPMailerAutoload.php');
    }

    public function send($toEmail, $toName, $subject, $body, $from, $fromName) {
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = 'utf-8';

        if (!PHPMailer::validateAddress($toEmail)) {
            throw new phpmailerAppException("Email address " . $toEmail . " is invalid -- aborting!");
        }
        $this->mail->isSMTP();
        $this->mail->isHTML(false);
        $this->mail->SMTPDebug = true;

        $this->mail->Host = $this->Host;
        $this->mail->Port = "25";
        $this->mail->SMTPSecure = "tls";
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
        
        
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );        
        
        
        
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
        $this->mail->SMTPSecure = "tls";
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