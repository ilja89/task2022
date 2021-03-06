<?php

namespace TTU\Charon\Services;

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
     * @param $params
     */
    public function sendGitCallback(GitCallback $gitCallback, $testerCallbackUrl, $params)
    {
        $secret_token = $gitCallback->secret_token;

        $params['returnUrl'] = $testerCallbackUrl;
        if (isset($params['returnExtra'])) {
            $params['returnExtra'] = array_merge($params['returnExtra'], ['token' => $secret_token]);
        } else {
            $params['returnExtra'] = ['token' => $secret_token];
        }

        $this->httpCommunicationService->postToTester($params);
    }
}
