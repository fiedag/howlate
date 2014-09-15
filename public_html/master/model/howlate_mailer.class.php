<?php
class howlate_mailer {

    private $Host = "localhost";
    private $Username;
    private $Password;

    private $mail;
    
    function __construct() {
        
        $this->Username = howlate_util::noreplySmtpUsername();
        $this->Password = howlate_util::noreplySmtpPassword();
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
        //$mail->SMTPDebug = 2;
        $this->mail->Host = $this->Host;
        $this->mail->Port = "25";
        $this->mail->SMTPSecure = "none";
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->Username;
        $this->mail->Password = $this->Password;
        $this->mail->addReplyTo($this->Username, "How-Late.Com");
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