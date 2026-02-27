<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}', function ($user, $userId) {
    logger('Broadcast auth', [
        'auth_user' => $user->id,
        'channel_user' => $userId,
    ]);
    
    return (int) $user->id === (int) $userId;
});
