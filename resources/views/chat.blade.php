<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 80vh;
        }

        .chat-header {
            padding: 16px;
            background: #007bff;
            color: white;
            font-size: 18px;
        }

        .chat-messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            background: #fafafa;
        }

        .message {
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 5px;
            max-width: 80%;
        }

        .message.you {
            background: #d1e7dd;
            align-self: flex-end;
        }

        .message.other {
            background: #f8d7da;
            align-self: flex-start;
        }

        .chat-input {
            display: flex;
            border-top: 1px solid #ccc;
        }

        .chat-input input {
            flex: 1;
            padding: 12px;
            border: none;
            font-size: 14px;
        }

        .chat-input button {
            padding: 12px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .chat-input button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        Reverb Chat (Conversation ID: <span id="convo-id">1</span>)
    </div>
    <div class="chat-messages" id="messages">
        <!-- Messages will be appended here -->
    </div>
    <div class="chat-input">
        <input type="text" id="msg" placeholder="Type a message...">
        <button id="send-btn">Send</button>
    </div>
</div>

<script type="module">
    import Echo from 'laravel-echo';
    import Pusher from 'pusher-js';

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        host: window.location.hostname + ':6001',
    });

    const conversationId = 1; // replace this dynamically as needed
    const userId = {{ auth()->id() }}; // logged-in user ID
    const token = "{{ auth()->user()->createToken('chat-token')->plainTextToken }}";

    const messagesBox = document.getElementById('messages');
    const msgInput = document.getElementById('msg');
    const sendBtn = document.getElementById('send-btn');

    // Load past messages
    fetch(`/api/chat/${conversationId}/messages`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            data.forEach(m => renderMessage(m));
        });

    // Listen to new messages via Reverb
    window.Echo.private(`chat.${conversationId}`)
        .listen('NewMessageEvent', (e) => {
            renderMessage(e, e.sender_id === userId ? 'you' : 'other');
        });

    // Send new message
    sendBtn.addEventListener('click', async () => {
        const content = msgInput.value.trim();
        if (!content) return;

        await fetch('/api/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                message: content
            })
        });

        msgInput.value = '';
    });

    // Render message
    function renderMessage(data, type = null) {
        const msgEl = document.createElement('div');
        msgEl.className = `message ${type || (data.sender_id === userId ? 'you' : 'other')}`;
        msgEl.textContent = `${data.sender}: ${data.message}`;
        messagesBox.appendChild(msgEl);
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }
</script>
</body>
</html>
