<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventUserListUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The event instance.
     *
     * @var \App\Models\Event
     */
    public $event;

    /**
     * The type of user list that was updated.
     *
     * @var string
     */
    public $listType;

    /**
     * The users in the updated list.
     *
     * @var array
     */
    public $users;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Event  $event
     * @param  string  $listType
     * @return void
     */
    public function __construct(Event $event, string $listType)
    {
        $this->event = $event;
        $this->listType = $listType;

        // Load the appropriate user list based on the list type
        switch ($listType) {
            case 'registered':
                $this->event->load(['registered']);
                $this->users = $this->event->registered;
                break;
            case 'attending':
                $this->event->load(['attending']);
                $this->users = $this->event->attending;
                break;
            case 'inside':
                $this->event->load(['inside']);
                $this->users = $this->event->inside;
                break;
            default:
                $this->users = [];
                break;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('event.' . $this->event->id . '.users');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'user-list-updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'event_id' => $this->event->id,
            'list_type' => $this->listType,
            'users' => $this->users,
        ];
    }
}
