<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('notifications.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('public-updates', function () {
    return true;
});
