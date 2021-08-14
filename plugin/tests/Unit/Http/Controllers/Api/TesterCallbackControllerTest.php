<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use Tests\TestCase;
use TTU\Charon\Http\Controllers\Api\TesterCallbackController;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\Flows\SaveTesterCallback;
use TTU\Charon\Services\GitCallbackService;

class TesterCallbackControllerTest extends TestCase
{
    /** @var Mock|GitCallbackService */
    private $gitCallbackService;

    /** @var Mock|SaveTesterCallback */
    private $saveCallbackFlow;

    /** @var TesterCallbackController */
    private $controller;

    protected function setUp()
    {
        parent::setUp();

        $this->controller = new TesterCallbackController(
            Mockery::mock(Request::class),
            $this->gitCallbackService = Mockery::mock(GitCallbackService::class),
            $this->saveCallbackFlow = Mockery::mock(SaveTesterCallback::class)
        );
    }

    /**
     * @throws IncorrectSecretTokenException
     */
    public function testIndexPassesAvailableUsernamesToFlow()
    {
        $this->markTestSkipped('Out of date, needs attention');

        $callback = new GitCallback();

        $request = new TesterCallbackRequest([
            'uniid' => 'Original user',
            'returnExtra' => [
                'usernames' => ['uuid1', 'original user', 'uuid2'],
                'token' => 'token hash'
            ]
        ]);

        /** @var Mock|Submission $submission */
        $submission = Mockery::mock(Submission::class)->makePartial();
        $submission->shouldReceive('makeHidden')->with('charon')->once();
        $submission->results = collect([
            Mockery::mock(Result::class)->shouldReceive('makeHidden')->with('submission')->once()->getMock(),
            Mockery::mock(Result::class)->shouldReceive('makeHidden')->with('submission')->once()->getMock()
        ]);

        $this->gitCallbackService
            ->shouldReceive('checkGitCallbackForToken')
            ->with('token hash')
            ->once()
            ->andReturn($callback);

        $this->saveCallbackFlow
            ->shouldReceive('run')
            ->with($request, $callback, ['original user', 'uuid1', 'uuid2'])
            ->once()
            ->andReturn($submission);

        $this->controller->index($request);
    }
}
