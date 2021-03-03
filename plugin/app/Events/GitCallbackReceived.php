<?php

namespace TTU\Charon\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use TTU\Charon\Models\GitCallback;

class GitCallbackReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var GitCallback */
    public $gitCallback;

    /** @var string */
    public $testerCallbackUrl;

    /** @var array */
    public $requestData;

    /**
     * Create a new event instance.
     *
     * @param GitCallback $gitCallback
     * @param $testerCallbackUrl
     * @param $requestData
     */
    public function __construct(GitCallback $gitCallback, $testerCallbackUrl, $requestData)
    {
        $this->gitCallback = $gitCallback;
        $this->testerCallbackUrl = $testerCallbackUrl;
        $this->requestData = $requestData;
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
