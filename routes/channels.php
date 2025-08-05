<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('public-updates', function () {
    return true;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    // You can add access checks here based on the conversation if needed
    return true;
});
