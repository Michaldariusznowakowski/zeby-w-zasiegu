<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chatroom;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chatroom.{chatroom_id}', function ($user, $chatroom_id) {
    $chatroom = Chatroom::find($chatroom_id);
    if (!$chatroom) {
        return false;
    }
    return $chatroom->dentist_id == $user->id || $chatroom->patient_id == $user->id;
});
