<?php

namespace TTU\Charon\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
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
}
