<?php
declare(strict_types = 1);

namespace Report\Helpers;

use PHPMailer\PHPMailer\{Exception, PHPMailer};

class SendEmail
{
    private static $instance;

    private PHPMailer $mail;

    /**
     * SendEmail constructor.
     *
     * @throws Exception
     */
    private function __construct ()
    {
        $this->mail = new PHPMailer();
        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->Host = $_ENV['EMAIL_ACCOUNT_HOST'];
        $this->mail->Username = $_ENV['EMAIL_ACCOUNT_USER'];
        $this->mail->Password = $_ENV['EMAIL_ACCOUNT_PASS'];
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->setFrom($_ENV['EMAIL_ACCOUNT_ADDRESS']);
        $this->mail->isHTML(true);
        $this->mail->addAddress($_ENV['REPORT_EMAIL']);
    }

    public static function getInstance (): SendEmail
    {
        if ( ! self::$instance instanceof self ) {
            self::$instance = new SendEmail();
        }

        return self::$instance;
    }

    /**
     * @param $subject
     * @param $message
     *
     * @throws Exception
     */
    public function send (string $subject = '', string $message = ''): void
    {
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($message);
        $this->mail->send();
    }
}
