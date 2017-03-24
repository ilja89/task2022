<?php

namespace TTU\Charon\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use TTU\Charon\Models\Charon;

class CharonCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $charon;

    /**
     * Create a new event instance.
     *
     * @param  Charon  $charon
     */
    public function __construct(Charon $charon)
    {
        $this->charon = $charon;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
