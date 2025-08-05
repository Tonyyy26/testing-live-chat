<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'required|exists:conversations_id',
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'conversation_id' => $data['conversation_id'],
            'sender_id' => Auth::user()->id,
            'message' => $data['message']
        ]);

        broadcast();
        
        return response()->json($message);
    }

    public function fetchMessages($conversationId) {
        return Message::where('conversation_id', $conversationId)->with('sender')->get();
    }
}
