<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;
    protected $serviceSid;

    public function __construct()
    {
        $this->twilio = new Client(
            env('TWILIO_ACCOUNT_SID'),
            env('TWILIO_AUTH_TOKEN')
        );
        $this->serviceSid = env('TWILIO_SERVICE_SID');
    }

    public function getConversations()
    {
        return $this->twilio->conversations->v1->services($this->serviceSid)
            ->conversations
            ->read();
    }
    public function createConversation($friendlyName)
    {
        return $this->twilio->conversations->v1->services($this->serviceSid)
            ->conversations
            ->create([
                'friendlyName' => $friendlyName
            ]);
    }

    public function deleteConversation($conversationSid)
    {
        return $this->twilio->conversations->v1->services($this->serviceSid)
            ->conversations($conversationSid)
            ->delete();
    }


}
