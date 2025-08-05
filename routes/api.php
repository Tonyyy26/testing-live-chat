<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Notifications\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['auth:api']]);

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('api')->group(function () {
    Route::post('/chat/send', [ChatController::class, 'send']);
    Route::get('/chat/{conversationId}/messages', [ChatController::class, 'fetchMessages']);
});

Route::post('/send-public-notification', [NotificationController::class, 'send']);