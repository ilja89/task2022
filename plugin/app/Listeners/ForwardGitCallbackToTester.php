<?php

namespace TTU\Charon\Listeners;

use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Services\TesterCommunicationService;

class ForwardGitCallbackToTester
{
    /** @var TesterCommunicationService */
    private $testerCommunicationService;

    /**
     * Create the event listener.
     *
     * @param TesterCommunicationService $testerCommunicationService
     */
    public function __construct(TesterCommunicationService $testerCommunicationService)
    {
        $this->testerCommunicationService = $testerCommunicationService;
    }

    /**
     * Handle the event.
     *
     * @param  GitCallbackReceived  $event
     * @return void
     */
    public function handle(GitCallbackReceived $event)
    {
        $this->testerCommunicationService->sendGitCallback(
            $event->gitCallback,
            $event->testerCallbackUrl,
            $event->requestData
        );
    }
}
