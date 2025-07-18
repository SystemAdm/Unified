<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendEventToDiscord
{
    use Dispatchable, SerializesModels;

    /**
     * The event instance.
     *
     * @var \App\Models\Event
     */
    public $event;

    /**
     * The action type.
     *
     * @var string
     */
    public $action;

    /**
     * Additional data for the action.
     *
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Event  $event
     * @param  string  $action
     * @param  array  $data
     * @return void
     */
    public function __construct(Event $event, string $action = 'create', array $data = [])
    {
        $this->event = $event;
        $this->action = $action;
        $this->data = $data;
    }
}
