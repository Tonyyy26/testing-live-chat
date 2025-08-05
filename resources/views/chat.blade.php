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
        body { margin: 0; padding: 0; font-family: sans-serif; background-color: #f5f5f5; }
        .chat-container { display: flex; flex-direction: column; max-width: 600px; margin: 40px auto; background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .chat-header { padding: 16px; background: #3490dc; color: white; font-weight: bold; }
        .chat-messages { height: 400px; overflow-y: auto; padding: 16px; background-color: #fafafa; }
        .chat-message { margin-bottom: 10px; }
        .chat-message .user { font-weight: bold; color: #555; }
        .chat-message .text { margin-left: 10px; }
        .chat-input { display: flex; border-top: 1px solid #ddd; }
        .chat-input input { flex: 1; padding: 10px; border: none; outline: none; }
        .chat-input button { padding: 10px 20px; background-color: #38c172; color: white; border: none; cursor: pointer; }
        .chat-input button:hover { background-color: #2fa360; }
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
    const userName = "{{ auth()->user()->name ?? 'Guest' }}";
    const conversationId = 1;
    const receiverId = 2;

    const messagesDiv = document.getElementById('messages'); // ✅ fixed ID (was wrong before)

    if (!token) location.href = '/';

        fetch(`/api/chat/${conversationId}/messages`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(messages => {
        messages.forEach(msg => {
            appendMessage(msg.sender.name, msg.message);
        });
    })
    .catch(err => console.error('Failed to load messages:', err));

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
                'Accept': 'application/json' // ✅ ensures Laravel returns JSON
            },
            body: JSON.stringify({ 
                message,
                conversation_id: conversationId,
                receiver_id: receiverId
             })
        })
        .then(async res => {
            if (!res.ok) {
                const err = await res.text();
                console.error('Error:', err);
                return;
            }
            return res.json();
        })
        .then(() => input.value = '')
        .catch(err => console.error('Unexpected:', err));
    }

    function appendMessage(user, text) {
        const el = document.createElement('div');
        el.classList.add('chat-message');
        el.innerHTML = `<span class="user">${user}:</span><span class="text">${text}</span>`;
        messagesDiv.appendChild(el);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Setup Echo
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
                Accept: 'application/json' // ✅ tell Laravel we want JSON
            }
        }
    });

    // Listen for new messages
    window.Echo.private(`chat.${conversationId}`)
        .listen('NewMessageEvent', e => {
            appendMessage(e.sender, e.message);
        });
</script>

</body>
</html>
