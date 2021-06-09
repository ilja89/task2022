<?php

namespace TTU\Charon\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection;
use TTU\Charon\Models\Charon;

class CharonUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Charon */
    public $charon;

    /**
     * Create a new event instance.
     *
     * @param Charon $charon
     */
    public function __construct(Charon $charon)
    {
        $this->charon = $charon;
    }
}
