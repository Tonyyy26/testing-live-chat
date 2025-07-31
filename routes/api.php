<?php

use App\Http\Controllers\NotificationController;
use App\Notifications\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-public-notification', [NotificationController::class, 'send']);