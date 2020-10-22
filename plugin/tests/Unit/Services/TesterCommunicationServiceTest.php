<?php

namespace Tests\Unit\Services;

use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Services\HttpCommunicationService;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Services\TesterCommunicationService;

class TesterCommunicationServiceTest extends TestCase
{

    public function testSendsGitCallback()
    {
        $gitCallback = m::mock(GitCallback::class)->makePartial();
        $gitCallback->secret_token = 'Very secret token';

        $httpCommunicator = m::mock(HttpCommunicationService::class)
            ->shouldReceive('postToTester')->with([
                'returnUrl' => 'tester callback url',
                'returnExtra' => ['token' => 'Very secret token'],
                'extra' => 'param'
            ])->getMock();
        $testerCommunicationService = new TesterCommunicationService($httpCommunicator);

        $testerCommunicationService->sendGitCallback($gitCallback, 'tester callback url', ['extra' => 'param']);
    }
}
