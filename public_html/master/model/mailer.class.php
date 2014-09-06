<?php
class mailer {

    private $Host = "localhost";
    private $Username = "noreply@how-late.com";
    private $Password = "Kh6z9z6y6c";

    private $mail;
    
    function __construct() {
        include('includes/PHPMailer-master/PHPMailerAutoload.php');
    }

    public function send($toEmail, $toName, $subject, $body, $from = 'noreply@how-late.com', $fromName = 'noreply@how-late.com') {
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
        $this->mail->addReplyTo("noreply@how-late.com", "How-Late.Com");
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