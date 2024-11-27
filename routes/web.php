<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Services\TwilioService;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TokenController;

Route::prefix('chat-app')->group(function () {
    // Home route for chat app
    Route::get('/', function () {
        return view('welcome'); // Make sure this view exists in resources/views
    })->name('home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::get('/test-twilio', function (TwilioService $twilioService) {
        $conversations = $twilioService->getConversations();
        $data = [];
        foreach ($conversations as $conversation) {
            $data[] = [
                'sid' => $conversation->sid,
                'friendly_name' => $conversation->friendlyName,
                'created_at' => $conversation->dateCreated->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json($data);
    });

    Route::get('/create-conversation', function (TwilioService $twilioService) {
        $conversation = $twilioService->createConversation('chat-room');
        return response()->json([
            'sid' => $conversation->sid,
            'friendly_name' => $conversation->friendlyName,
            'created_at' => $conversation->dateCreated->format('Y-m-d H:i:s'),
        ]);
    });

    Route::post('/sms', [MessageController::class, 'handle']);

    Route::get('/delete-conversation/{sid}', function ($sid, TwilioService $twilioService) {
        $twilioService->deleteConversation($sid);
        return response()->json(['message' => 'Conversation deleted']);
    });

    Route::post('/send-message', [MessageController::class, 'send'])->middleware('auth');
    Route::get('/messages', [MessageController::class, 'index'])->middleware('auth');
    Route::post('/webhooks/twilio', [WebhookController::class, 'handleWebhook']);

    Route::get('/generate-token', [TokenController::class, 'generateToken']);
});



// Include auth routes
require __DIR__ . '/auth.php';
