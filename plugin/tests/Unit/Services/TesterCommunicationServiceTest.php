<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Services\HttpCommunicationService;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Services\TesterCommunicationService;

class TesterCommunicationServiceTest extends TestCase
{
    /** @var GitCallback */
    private $gitCallback;

    /** @var Mock|HttpCommunicationService  */
    private $communicator;

    /** @var TesterCommunicationService  */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gitCallback = new GitCallback(['secret_token' => 'Very secret token']);
        $this->communicator = Mockery::mock(HttpCommunicationService::class);
        $this->service = new TesterCommunicationService($this->communicator);
    }

    public function testSendsGitCallbackWithoutExtra()
    {
        $this->communicator->shouldReceive('postToTester')->with([
            'returnUrl' => 'tester callback url',
            'returnExtra' => ['token' => 'Very secret token'],
            'regular' => 'param'
        ]);

        $this->service->sendGitCallback($this->gitCallback, 'tester callback url', ['regular' => 'param']);
    }

    public function testSendsGitCallbackWithExtra()
    {
        $this->communicator->shouldReceive('postToTester')->with([
            'returnUrl' => 'tester callback url',
            'returnExtra' => ['extra' => 'param', 'token' => 'Very secret token'],
            'regular' => 'param'
        ]);

        $this->service->sendGitCallback(
            $this->gitCallback,
            'tester callback url',
            ['regular' => 'param', 'returnExtra' => ['extra' => 'param']]
        );
    }
}
