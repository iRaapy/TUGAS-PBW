<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($user) {
    // $user sudah pasti terautentikasi, langsung return datanya
    return [
        'id'   => $user->id,
        'name' => $user->name,
    ];
});