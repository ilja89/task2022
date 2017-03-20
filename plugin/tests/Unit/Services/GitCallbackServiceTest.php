<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Services\GitCallbackService;

class GitCallbackServiceTest extends TestCase
{
    public function testChecksCorrectGitCallback()
    {
        $gitCallback = m::mock(GitCallback::class)
                        ->shouldNotReceive('save')
                        ->getMock()
                        ->makePartial();

        $gitCallback->first_response_time = Carbon::now();

        $gitCallbackService = $this->getGitCallbackService('secret_token', $gitCallback);

        $gitCallbackService->checkGitCallbackForToken('secret_token');
    }

    public function testCheckGitCallbackSavesFirstResponseTime()
    {
        $gitCallback = m::mock(GitCallback::class)
                        ->shouldReceive('save')
                        ->once()
                        ->getMock()
                        ->makePartial();

        $gitCallback->first_response_time = null;

        $gitCallbackService = $this->getGitCallbackService('secret_token', $gitCallback);

        $gitCallbackService->checkGitCallbackForToken('secret_token');
    }

    /**
     * @expectedException \TTU\Charon\Exceptions\IncorrectSecretTokenException
     */
    public function testCheckGitCallbackThrowsExceptionWhenTooOld()
    {
        $gitCallback = m::mock(GitCallback::class)
                        ->shouldNotReceive('save')
                        ->getMock()
                        ->makePartial();

        $gitCallback->first_response_time = Carbon::now()->subMinutes(4);

        $gitCallbackService = $this->getGitCallbackService('secret_token', $gitCallback);

        $gitCallbackService->checkGitCallbackForToken('secret_token');
    }

    private function getGitCallbackService($secretToken, $gitCallback)
    {
        return new GitCallbackService(
            m::mock(GitCallbacksRepository::class)
             ->shouldReceive('findByToken')
             ->with($secretToken)
             ->once()
             ->andReturn($gitCallback)
             ->getMock()
        );
    }
}
