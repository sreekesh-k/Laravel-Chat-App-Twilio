<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Services\TwilioService;

Route::get('/', function () {
    return view('welcome');
});

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

Route::get('/delete-conversation/{sid}', function ($sid, TwilioService $twilioService) {
    $twilioService->deleteConversation($sid);
    return response()->json(['message' => 'Conversation deleted']);
});



require __DIR__ . '/auth.php';
