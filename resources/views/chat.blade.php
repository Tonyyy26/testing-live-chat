<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Laravel Chat App</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Pusher + Echo -->
  <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
  @vite(['resources/js/app.js'])

  <style>
    body {
      margin: 0;
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
      background: #fafafa;
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
      margin-top: 4px;
      text-align: right;
    }

    .date-divider {
      text-align: center;
      margin: 20px 0;
      color: #888;
      font-size: 0.85rem;
      position: relative;
    }

    .date-divider hr {
      border: none;
      border-top: 1px solid #ddd;
      margin: 10px 0;
    }

    .date-divider span {
      background: #fafafa;
      padding: 0 10px;
      position: relative;
      top: -14px;
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
  let conversationId = 1;
  const receiverId = 2;

  const messagesDiv = document.getElementById('messages');
  const input = document.getElementById('message');

  if (!token) location.href = '/';

  function loadMessages() {
    fetch(`/api/chat/${conversationId}/messages`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
      .then(res => res.json())
      .then(renderMessages)
      .catch(err => console.error('Load error:', err));
  }

  function renderMessages(messages) {
    messagesDiv.innerHTML = '';
    messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    let lastDate = null;
    messages.forEach(msg => {
      const date = new Date(msg.created_at).toDateString();
      if (lastDate !== date) {
        appendDateDivider(new Date(msg.created_at));
        lastDate = date;
      }
      appendMessage(msg);
    });
  }

  function appendDateDivider(date) {
    const today = new Date();
    const isToday = date.toDateString() === today.toDateString();

    const divider = document.createElement('div');
    divider.className = 'date-divider';
    divider.innerHTML = `
      <hr>
      <span>${isToday
        ? 'Today'
        : date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          })}</span>`;
    messagesDiv.appendChild(divider);
  }

  function appendMessage(msg) {
    const wrapper = document.createElement('div');
    wrapper.classList.add('chat-message', msg.is_sender ? 'right' : 'left');

    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';

    const time = new Date(msg.created_at).toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });

    bubble.innerHTML = `
      <div>${msg.message}</div>
      <div class="timestamp" data-date="${msg.created_at}">${time}</div>
    `;

    wrapper.appendChild(bubble);
    messagesDiv.appendChild(wrapper);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  function sendMessage() {
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
        const lastMsg = messagesDiv.querySelector('.chat-message:last-of-type');
        const lastDate = lastMsg?.querySelector('.timestamp')?.getAttribute('data-date');
        const newDate = new Date(res.created_at).toDateString();

        if (!lastDate || new Date(lastDate).toDateString() !== newDate) {
          appendDateDivider(new Date(res.created_at));
        }

        appendMessage(res);
      })
      .catch(err => console.error('Send error:', err));
  }

  function listenForMessages() {
    if (window.EchoChannel) {
      window.Echo.leaveChannel(`private-chat.${conversationId}`);
    }

    window.EchoChannel = window.Echo.private(`chat.${conversationId}`)
      .listen('NewMessageEvent', e => {
        if (!e.is_sender) {
          const lastMsg = messagesDiv.querySelector('.chat-message:last-of-type');
          const lastDate = lastMsg?.querySelector('.timestamp')?.getAttribute('data-date');
          const newDate = new Date(e.created_at).toDateString();

          if (!lastDate || new Date(lastDate).toDateString() !== newDate) {
            appendDateDivider(new Date(e.created_at));
          }

          appendMessage(e);
        }
      });
  }

  // Laravel Echo + Pusher
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

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  loadMessages();
  listenForMessages();
</script>
</body>
</html>
