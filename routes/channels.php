<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Chat;

/**
 * Authorizes users to access a private chat channel based on the channel ID.
 * The channel ID is expected to be in the format 'chat-channel.userId1_userId2'.
 * Only users with IDs matching userId1 or userId2 are authorized to join this channel.
 * This ensures that only the two users involved in a chat can listen to the channel.
 * DOC: https://laravel.com/docs/11.x/broadcasting#example-application-authorizing-channels
 *
 * @param User $user The currently authenticated user.
 * @param string $ids A string containing the user IDs involved in the chat, separated by an underscore.
 * @return array|bool Returns user details if authorized, otherwise false.
 */
Broadcast::channel('chat-channel.{ids}', function (User $user, $ids) {
    [$userId1, $userId2] = explode('_', $ids);
    if ($user->id == $userId1 || $user->id == $userId2) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }
    return false;
}, ['guards' => ['web', 'auth']]);

/**
 * Authorizes users to access the 'users-online' presence channel.
 * This channel is used to track which users are currently online.
 * Only authenticated users are allowed to join this channel.
 * Each user's ID and name are broadcast to others in the channel.
 *
 * @param User $user The currently authenticated user.
 * @return array|bool Returns user details if authorized, otherwise false.
 */
Broadcast::channel('users-online', function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name
    ];
}, ['guards' => ['web', 'auth']]);
