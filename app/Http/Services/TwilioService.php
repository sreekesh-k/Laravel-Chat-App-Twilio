<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            env('TWILIO_ACCOUNT_SID'), 
            env('TWILIO_AUTH_TOKEN')
        );
    }

    public function getClient()
    {
        return $this->twilio;
    }
}
