<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Event;

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

// Channel for event user updates
Broadcast::channel('event.{eventId}.users', function ($user, $eventId) {
    // Check if the user has permission to access this event's user list
    return $user->can('admin_event');
});
