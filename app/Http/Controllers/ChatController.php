<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Jobs\SendMessage;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'nullable',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);
        $user = Auth::user();

        // Get conversation by ID (if exists), else create new
        $conversation = null;
        if (!empty($data['conversation_id'])) {
            $conversation = Conversation::find($data['conversation_id']);
        }
    
        if (! $conversation) {
            $conversation = Conversation::create([
                'conversation_type_id' => 1 // assuming personal chat
            ]);
    
            // Only attach receiver (not the sender)
        }

        $conversation->users()->attach($data['receiver_id']);

        // Save the message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $data['message']
        ]);

        broadcast(new NewMessageEvent($message))->toOthers();
        // Queue::push(new SendMessage($message));
        
        return response()->json($message->load('sender'));
    }

    public function fetchMessages($conversationId) {
         $userId = auth()->id();

    return Message::where('conversation_id', $conversationId)
        ->with('sender:id,name') // Optional
        ->get()
        ->map(function ($msg) use ($userId) {
            return [
                'id' => $msg->id,
                'conversation_id' => $msg->conversation_id,
                'sender_id' => $msg->sender_id,
                'message' => $msg->message,
                'created_at' => $msg->created_at,
                'is_sender' => $msg->sender_id === $userId
            ];
        });
    }
}
