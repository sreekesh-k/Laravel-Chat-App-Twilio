<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class MessageController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function send(Client $client, Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Replace with your conversation SID
        $conversationSid = env('TWILIO_CHAT_SID'); // Update this with the correct Conversation SID

        // Send the message using the Twilio API
        $toNumber = env('MY_NUMBER');
        $twilioNumber = env('TWILIO_NUMBER');
        $client->messages->create(
            $toNumber, // Text any number
            [
                'from' => $twilioNumber, // From a Twilio number in your account
                'body' => $request->message
            ]
        );
        $this->twilioService->sendMessage($conversationSid, $user->name, $request->message);

        return response('OK', 200);
    }

    public function handle(Request $request)
    {
        // Log the incoming SMS details
        Log::info('Incoming SMS request data:', $request->all());
        $from = $request->input('From'); // Sender's phone number
        $body = $request->input('Body'); // Message content

        
        $conversationSid = env('TWILIO_CHAT_SID');
        $this->twilioService->sendMessage($conversationSid, $from, $body);
        return response()->json($request->all());
    }


    public function index()
    {
        // Fetch messages from the Twilio conversation
        // Replace with your conversation SID
        $conversationSid = env('TWILIO_CHAT_SID'); // Update this with the correct Conversation SID

        $messages = $this->twilioService->getMessages($conversationSid);

        return response()->json($messages);
    }
}
