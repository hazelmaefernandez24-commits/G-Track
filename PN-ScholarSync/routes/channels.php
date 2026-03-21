<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('users.{userId}.notifications', function ($user, $userId) {
	return (string)($user->user_id ?? $user->id) === (string)$userId;
});
