<?php
declare(strict_types = 1);

namespace Report\Helpers;

use Smsapi\Client\SmsapiHttpClient;
use Smsapi\Client\Feature\Sms\Bag\SendSmsBag;

class SendSms
{
    private static $instance;
    private \Smsapi\Client\Service\SmsapiPlService $service;

    private function __construct ()
    {
        $this->service = (new SmsapiHttpClient())->smsapiPlService($_ENV['SMSAPI_TOKEN']);
    }

    public static function getInstance (): SendSms
    {
        if ( ! self::$instance instanceof self ) {
            self::$instance = new SendSms();
        }

        return self::$instance;
    }

    public function send (string $message): void
    {
        $sendSmsBag = SendSmsBag::withMessage($_ENV['REPORT_PHONE'], $message);
        $this->service->smsFeature()->sendSms($sendSmsBag);
    }
}
