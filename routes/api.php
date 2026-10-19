<?php

use App\Http\Controllers\DemoChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/chat', [DemoChatController::class, 'index'])->name('chat.index');
    Route::get('/conversations/{conversation}/messages', [DemoChatController::class, 'messages'])
        ->name('conversations.messages.index');
    Route::post('/conversations/{conversation}/messages', [DemoChatController::class, 'storeMessage'])
        ->name('conversations.messages.store');
    Route::post('/conversations/{conversation}/read', [DemoChatController::class, 'markRead'])
        ->name('conversations.read');
});
