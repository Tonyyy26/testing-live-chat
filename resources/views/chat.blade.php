<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel Chat App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Echo + Pusher (used by Reverb) -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
    @vite(['resources/js/app.js']) {{-- Required for Echo --}}

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background-color: #f5f5f5;
        }
    
        .chat-container {
            display: flex;
            flex-direction: column;
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
    
        .chat-header {
            padding: 16px;
            background: #3490dc;
            color: white;
            font-weight: bold;
        }
    
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 16px;
            background-color: #fafafa;
        }
    
        .chat-message {
            display: flex;
            margin-bottom: 10px;
        }
    
        .chat-message.left {
            justify-content: flex-start;
        }
    
        .chat-message.right {
            justify-content: flex-end;
        }
    
        .message-bubble {
            max-width: 70%;
            padding: 10px;
            border-radius: 12px;
            background-color: #e2e8f0;
            position: relative;
        }
    
        .chat-message.right .message-bubble {
            background-color: #38c172;
            color: white;
        }
    
        .timestamp {
            font-size: 0.75rem;
            color: #999;
            margin-top: 2px;
            text-align: right;
        }
    
        .chat-input {
            display: flex;
            border-top: 1px solid #ddd;
        }
    
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            outline: none;
        }
    
        .chat-input button {
            padding: 10px 20px;
            background-color: #38c172;
            color: white;
            border: none;
            cursor: pointer;
        }
    
        .chat-input button:hover {
            background-color: #2fa360;
        }
    </style>
    
</head>
<body>

<div class="chat-container">
    <div class="chat-header">Laravel Chat</div>
    <div id="messages" class="chat-messages"></div>
    <div class="chat-input">
        <input type="text" id="message" placeholder="Type a message..." />
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    const userId = {{ auth()->id() ?? 'null' }};
    const conversationId = 1;
    const receiverId = 2;

    const messagesDiv = document.getElementById('messages');
    if (!token) location.href = '/';

    // Fetch and render existing messages
    function loadMessages() {
        fetch(`/api/chat/${conversationId}/messages`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(renderMessages)
        .catch(err => console.error('Failed to load messages:', err));
    }

    function renderMessages(messages) {
        messagesDiv.innerHTML = ''; // clear current
        messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        messages.forEach(msg => appendMessage(msg));
    }

    function appendMessage(msg) {
        const wrapper = document.createElement('div');
        const isSender = msg.sender_id === userId;
        wrapper.classList.add('chat-message', isSender ? 'right' : 'left');

        const bubble = document.createElement('div');
        bubble.classList.add('message-bubble');
        bubble.innerHTML = `
            <div>${msg.message}</div>
            <div class="timestamp">${new Date(msg.created_at).toLocaleTimeString()}</div>
        `;

        wrapper.appendChild(bubble);
        messagesDiv.appendChild(wrapper);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

 
    function sendMessage() {
        const input = document.getElementById('message');
        const message = input.value.trim();
        if (!message) return;

        fetch('/api/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message,
                conversation_id: conversationId,
                receiver_id: receiverId
            })
        })
        .then(res => res.json())
        .then(res => {
            input.value = '';
            // If conversation_id was null and a new one is created
            if (res.conversation_id && res.conversation_id !== conversationId) {
                conversationId = res.conversation_id;
                listenForMessages(); // Re-init channel listener
            }
            appendMessage(res); // Optimistic UI
        })
        .catch(err => console.error('Unexpected:', err));
    }

    // Laravel Echo + Reverb (or Pusher)
    function listenForMessages() {
        if (window.EchoChannel) {
            window.Echo.leaveChannel(`private-chat.${conversationId}`);
        }

        window.EchoChannel = window.Echo.private(`chat.${conversationId}`)
            .listen('NewMessageEvent', e => {
                // Avoid showing same message if it's from sender
                if (e.sender_id !== userId) appendMessage(e);
            });
    }

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'd6cauplwbgdvwi9mkeif',
        wsHost: window.location.hostname,
        wsPort: 8081,
        forceTLS: false,
        encrypted: false,
        disableStats: true,
        enabledTransports: ['ws'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
            }
        }
    });

    // Initial load
    loadMessages();
    listenForMessages();
</script>


</body>
</html>
