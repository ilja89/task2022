<?php

namespace Tests\Unit\Http\Controllers\Api;

use Exception;
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
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\SubmissionService;

class TesterCallbackControllerTest extends TestCase
{
    /** @var Mock|SubmissionService */
    private $submissionService;

    /** @var Mock|CharonGradingService */
    private $charonGradingService;

    /** @var Mock|GitCallbackService */
    private $gitCallbackService;

    /** @var TesterCallbackController */
    private $controller;

    protected function setUp()
    {
        parent::setUp();

        $this->controller = new TesterCallbackController(
            Mockery::mock(Request::class),
            $this->submissionService = Mockery::mock(SubmissionService::class),
            $this->charonGradingService = Mockery::mock(CharonGradingService::class),
            $this->gitCallbackService = Mockery::mock(GitCallbackService::class)
        );
    }

    /**
     * @throws IncorrectSecretTokenException
     */
    public function testIndexSavesSingleUserSubmission()
    {
        $request = new TesterCallbackRequest([
            'uniid' => 'original user',
            'returnExtra' => [
                'token' => 'token hash'
            ]
        ]);

        $callback = new GitCallback();

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

        $this->submissionService
            ->shouldReceive('saveSubmission')
            ->with($request, $callback)
            ->once()
            ->andReturn($submission);

        $this->charonGradingService
            ->shouldReceive('calculateCalculatedResultsForNewSubmission')
            ->with($submission)
            ->once();

        $this->charonGradingService
            ->shouldReceive('gradesShouldBeUpdated')
            ->once()
            ->andReturn(true);

        $this->charonGradingService
            ->shouldReceive('updateGrade')
            ->with($submission)
            ->once();

        $this->controller->index($request);
    }

    /**
     * @throws IncorrectSecretTokenException
     */
    public function testIndexSavesGroupSubmissions()
    {
        $request = new TesterCallbackRequest([
            'uniid' => 'original user',
            'returnExtra' => [
                'usernames' => ['uuid1', 'uuid2'],
                'token' => 'token hash'
            ]
        ]);

        $callback = new GitCallback();

        $this->gitCallbackService
            ->shouldReceive('checkGitCallbackForToken')
            ->with('token hash')
            ->once()
            ->andReturn($callback);

        $usernames = [];

        $this->submissionService
            ->shouldReceive('saveSubmission')
            ->with(
                Mockery::on(function ($argument) use (&$usernames) {
                    $usernames[] = $argument['uniid'];
                    return true;
                }),
                $callback
            )
            ->twice()
            ->andThrow(new Exception());

        $this->controller->index($request);

        $this->assertEquals(['uuid1', 'uuid2'], $usernames);
    }
}
