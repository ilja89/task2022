<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;

/**
 * Class TesterCommunicationService.
 *
 * @package TTU\Charon\Services
 */
class TesterCommunicationService
{
    /** @var HttpCommunicationService */
    private $httpCommunicationService;

    /**
     * TesterCommunicationService constructor.
     *
     * @param HttpCommunicationService $httpCommunicationService
     */
    public function __construct(HttpCommunicationService $httpCommunicationService)
    {
        $this->httpCommunicationService = $httpCommunicationService;
    }

    /**
     * Send git callback info to the tester.
     *
     * @param GitCallback $gitCallback
     * @param $testerCallbackUrl
     * @param $extraParameters
     */
    public function sendGitCallback(GitCallback $gitCallback, $testerCallbackUrl, $extraParameters)
    {
        $secret_token = $gitCallback->secret_token;
        $params = [
            'returnUrl' => $testerCallbackUrl,
            'returnExtra' => ['token' => $secret_token]
        ];

        $params = array_merge($extraParameters, $params);

        $this->httpCommunicationService->postToTester('test', $params);
    }
}
