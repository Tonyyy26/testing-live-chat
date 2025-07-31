<?php

namespace App\Http\Controllers;

use App\Notifications\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function send()
    {
        Notification::route('broadcast', 'public-updates')
            ->notify(new NewNotification('Tangina mo 🚀 Public Notification!!'));

        return response()->json(['message' => 'Notification sent']);
    }
}
