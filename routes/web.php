<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// Route auth bawaan Breeze — wajib ada
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/messages', [ChatController::class, 'store'])->name('chat.store');
});

Route::redirect('/', '/chat');