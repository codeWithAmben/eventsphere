<?php
/**
 * mailer.php - email sending helper. Uses PHPMailer if installed, else falls back to mail().
 */

class Mailer {
    private $fromEmail;
    private $fromName;

    public function __construct($fromEmail = 'no-reply@example.com', $fromName = 'EventSphere') {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send($toEmail, $subject, $body, $altBody = '') {
        // If PHPMailer installed, try to use it
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($toEmail);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                if ($altBody) $mail->AltBody = $altBody;
                $mail->send();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        // Simple fallback to PHP mail()
        $headers = 'From: ' . $this->fromName . ' <' . $this->fromEmail . '>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        return mail($toEmail, $subject, $body, $headers);
    }
}

?>