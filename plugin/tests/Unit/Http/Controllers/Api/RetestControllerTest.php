<?php

namespace Tests\Unit\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Facades\MoodleCron;
use TTU\Charon\Http\Controllers\Api\RetestController;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\TesterType;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\TesterCommunicationService;
use TTU\Charon\Tasks\RetestSubmissions;
use Zeizig\Moodle\Models\User;

class RetestControllerTest extends TestCase
{
    /** @var Mock|TesterCommunicationService */
    private $testerCommunicationService;

    /** @var Mock|GitCallbacksRepository */
    private $gitCallbacksRepository;

    /** @var Mock|CourseSettingsRepository */
    private $courseSettingsRepository;

    /** @var Mock|CourseSettingsRepository */
    private $request;

    /** @var Mock|SubmissionsRepository */
    protected $submissionRepository;

    /** @var Mock|MoodleCron */
    private $cron;

    /** @var RetestController */
    private $controller;

    protected function setUp()
    {
        parent::setUp();

        $this->controller = new RetestController(
            $this->testerCommunicationService = Mockery::mock(TesterCommunicationService::class),
            $this->request = Mockery::mock(Request::class),
            $this->gitCallbacksRepository = Mockery::mock(GitCallbacksRepository::class),
            $this->courseSettingsRepository = Mockery::mock(CourseSettingsRepository::class),
            $this->submissionRepository = Mockery::mock(SubmissionsRepository::class),
            $this->cron = Mockery::mock(MoodleCron::class)
        );
    }

    /**
     * @throws SubmissionNoGitCallbackException
     */
    public function testIndexThrowsIfNoCallback()
    {
        $this->expectException(SubmissionNoGitCallbackException::class);

        $submission = new Submission();
        $submission->id = 3;
        $submission->gitCallback = null;

        $this->controller->index($submission);

        $this->gitCallbacksRepository->shouldNotHaveReceived('save');
    }

    /**
     * @throws SubmissionNoGitCallbackException
     */
    public function testIndexSendsNewCallback()
    {
        /** @var Mock|Charon $charon */
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->course = 5;
        $charon->docker_content_root = 'root/content';
        $charon->docker_test_root = 'root/test';
        $charon->tester_extra = 'tester,extra';
        $charon->system_extra = 'system,extra';
        $charon->docker_timeout = 7;
        $charon->testerType = new TesterType(['name' => 'pythonTester']);

        /** @var Mock|Submission $submission */
        $submission = Mockery::mock(Submission::class)->makePartial();
        $submission->id = 3;
        $submission->gitCallback = new GitCallback(['repo' => 'python-2020', 'user' => 'firstUser']);
        $submission->users = collect([new User(['username' => 'firstUser']), new User(['username' => 'secondUser'])]);
        $submission->git_hash = '#somehash';
        $submission->git_timestamp = new Carbon(1584083926);
        $submission->charon = $charon;

        $this->request->shouldReceive('fullUrl')->once()->andReturn('repo/url');
        $this->request->shouldReceive('getUriForPath')->with('/api/tester_callback')->once()->andReturn('callback/url');

        $gitCallback = new GitCallback(['repo' => 'python-2021']);

        $this->gitCallbacksRepository
            ->shouldReceive('save')
            ->with('repo/url', 'python-2020', 'firstUser')
            ->once()
            ->andReturn($gitCallback);

        $this->courseSettingsRepository
            ->shouldReceive('getCourseSettingsByCourseId')
            ->with(5)
            ->once()
            ->andReturn(new CourseSettings(['unittests_git' => 'unit/test/repo']));

        $this->testerCommunicationService
            ->shouldReceive('sendGitCallback')
            ->with($gitCallback, 'callback/url', [
                'dockerContentRoot' => 'root/content',
                'dockerExtra' => 'tester,extra',
                'dockerTestRoot' => 'root/test',
                'dockerTimeout' => 7,
                'gitStudentRepo' => 'python-2021',
                'gitTestRepo' => 'unit/test/repo',
                'hash' => '#somehash',
                'systemExtra' => ['system', 'extra'],
                'returnExtra' => ['usernames' => ['firstUser', 'secondUser']],
                'testingPlatform' => 'pythonTester',
                'timestamp' => 1584076726
            ])
            ->once();

        $actual = $this->controller->index($submission);

        $this->assertEquals(200, $actual->getData()->status);
    }

    public function testRetestByCharonEnqueuesTasks()
    {
        $charon = new Charon();
        $charon->id = 3;

        $this->submissionRepository
            ->shouldReceive('findLatestByCharon')
            ->once()
            ->with(3)
            ->andReturn([5, 7]);

        Config::set('queue.moodle.retest_delay', '31');

        $this->request->shouldReceive('fullUrl')->once()->andReturn('repo/url');
        $this->request->shouldReceive('getUriForPath')->with('/api/tester_callback')->once()->andReturn('callback/url');

        $this->cron
            ->shouldReceive('enqueue')
            ->with(
                RetestSubmissions::class,
                ['id' => 5, 'charon' => 3, 'total' => 2, 'requestUrl' => 'repo/url', 'callbackUrl' => 'callback/url'],
                0
            );

        $this->cron
            ->shouldReceive('enqueue')
            ->with(
                RetestSubmissions::class,
                ['id' => 7, 'charon' => 3, 'total' => 2, 'requestUrl' => 'repo/url', 'callbackUrl' => 'callback/url'],
                31
            );

        $actual = $this->controller->retestByCharon($charon);

        $this->assertEquals('Retesting has been triggered for 2 Submissions', $actual->getData()->message);
    }
}
