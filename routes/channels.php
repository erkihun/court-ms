<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin-chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->hasPermission('cases.view');
});

Broadcast::channel('admin-chat-presence', function ($user) {
    if (! $user->hasPermission('cases.view')) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
