<?php

namespace App\Http\Controllers;

use App\Jobs\SendPublicNotificationJob;
use App\Notifications\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class NotificationController extends Controller
{
    public function send()
    {
        Queue::push(new SendPublicNotificationJob([
            'message' => 'Wow! 🚀 Public Notification!! 🚀'
        ]));

        return response()->json(['message' => 'Notification sent']);
    }
}
