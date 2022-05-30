<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\AreteResponseParser;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\SubmissionCalculatorService;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\User;

class SubmissionServiceTest extends TestCase
{
    /** @var Mock|Submission */
    private $submission;

    /** @var Mock|CharonGradingService */
    private $charonGradingService;

    /** @var Mock|AreteResponseParser */
    private $requestHandlingService;

    /** @var Mock|SubmissionsRepository */
    private $submissionsRepository;

    /** @var Mock|UserRepository */
    private $userRepository;

    /** @var Mock|CharonRepository */
    private $charonRepository;

    /** @var SubmissionService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->submission = Mockery::mock(Submission::class)->makePartial();

        $this->service = new SubmissionService(
            $this->charonGradingService = Mockery::mock(CharonGradingService::class),
            $this->requestHandlingService = Mockery::mock(AreteResponseParser::class),
            $this->submissionsRepository = Mockery::mock(SubmissionsRepository::class),
            $this->charonRepository = Mockery::mock(CharonRepository::class),
            $this->userRepository = Mockery::mock(UserRepository::class),
            Mockery::mock(SubmissionCalculatorService::class)
        );
    }

    /**
     * @throws Exception
     */
    public function testSaveSubmissionSavesWithGitCallback()
    {
        GitCallback::unguard();

        $request = new Request();
        $callback = new GitCallback(['id' => 3, 'repo' => 'iti2020']);

        $this->requestHandlingService
            ->shouldReceive('getSubmissionFromRequest')
            ->with($request, 'iti2020', 5)
            ->andReturn($this->submission);

        $this->submission->shouldReceive('save');

        $actual = $this->service->saveSubmission($request, $callback, 5);

        $this->assertEquals(3, $actual->git_callback_id);
    }

    /**
     * @throws Exception
     */
    public function testSaveSubmissionSavesWithoutGitCallback()
    {
        GitCallback::unguard();

        $request = new Request();
        $callback = new GitCallback();

        $this->requestHandlingService
            ->shouldReceive('getSubmissionFromRequest')
            ->with($request, '', 5, 1)
            ->andReturn($this->submission);

        $this->submission->shouldReceive('save');

        $actual = $this->service->saveSubmission($request, $callback, 5, 1);

        $this->assertEquals(null, $actual->git_callback_id);
    }

    public function testAddsNewSubmission()
    {
        $student = new User();
        $this->submission->id = 1;

        $now = Carbon::create(2020, 11, 16, 12);
        Carbon::setTestNow($now);

        $this->userRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with(7)
            ->andReturn($student);

        $submissions = Mockery::mock('Submissions')
            ->shouldReceive('create')
            ->with([
                'user_id' => 7,
                'git_hash' => '',
                'git_timestamp' => $now,
                'created_at' => $now,
                'updated_at' => $now,
                'stdout' => 'Manually created by teacher',
            ])
            ->andReturn($this->submission)
            ->getMock();

        /** @var Mock|BelongsToMany $users */
        $users = Mockery::mock(BelongsToMany::class);

        $this->submission
            ->shouldReceive('users')
            ->andReturn($users);

        $users->shouldReceive('save')
            ->once()
            ->with($student);

        $this->submissionsRepository->shouldReceive('saveNewEmptyResult')->with(1, 7, 1, '');
        $this->submissionsRepository->shouldReceive('saveNewEmptyResult')->with(1, 7, 101, '');
        $this->charonGradingService->shouldReceive('gradesShouldBeUpdated')->with($this->submission, 7)->andReturn(true);
        $this->charonGradingService->shouldReceive('updateGrades')->with($this->submission, 7);

        /** @var Charon $charon */
        $charon = Mockery::mock(Charon::class, ['submissions' => $submissions])->makePartial();
        $charon->grademaps = [new Grademap(['grade_type_code' => 1]), new Grademap(['grade_type_code' => 101])];

        $actual = $this->service->addNewEmptySubmission($charon, 7);

        $this->assertSame($this->submission, $actual);
    }

    public function testIncludeUnsentGradesDifferentiatesPersistentGrades()
    {
        /** @var Charon $charon */
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->grademaps = [
            new Grademap(['grade_type_code' => 101]),
            new Grademap(['grade_type_code' => 1001, 'persistent' => 1])
        ];

        $this->submission->charon = $charon;
        $this->submission->id = 3;
        $this->submission->user_id = 5;
        $this->submission->charon_id = 7;

        $this->submission
            ->shouldReceive('results->where->where->count')
            ->twice()
            ->andReturn(0);

        $this->submissionsRepository
            ->shouldReceive('carryPersistentResult')
            ->with(3, 5, 7, 1001)
            ->once();

        $this->submissionsRepository
            ->shouldReceive('saveNewEmptyResult')
            ->with(3, 5, 101, 'This result was automatically generated')
            ->once();

        $this->service->includeUnsentGrades($this->submission, 5);
    }

    public function testSaveFilesSuccessful()
    {
        $submissionId = 1;
        $filesRequest = [[1],[2]];
        $submissionFile = Mockery::mock(SubmissionFile::class);

        $this->requestHandlingService
            ->shouldReceive('getFileFromRequest')
            ->once()
            ->with($submissionId, [1], false)
            ->andReturn($submissionFile);
        $this->requestHandlingService
            ->shouldReceive('getFileFromRequest')
            ->once()
            ->with($submissionId, [2], false)
            ->andReturn($submissionFile);

        $submissionFile->shouldReceive('save')->twice();

        $this->service->saveFiles($submissionId, $filesRequest);
    }

    public function testFindSubmissionByHashSuccessful()
    {
        $this->submission->git_hash = 'commit hash';

        $this->submissionsRepository
            ->shouldReceive('findSubmissionByHash')
            ->with('commit hash')
            ->once()
            ->andReturn($this->submission);

        $this->service->findSubmissionByHash('commit hash');
    }

    public function testFindSubmissionByHashUnSuccessful()
    {
        $this->submissionsRepository
            ->shouldNotReceive('findSubmissionByHash');

        $this->service->findSubmissionByHash('');
    }

    public function testGetSubmissionForEachStudent()
    {
        $charon = new Charon();
        $charon->id = 999;

        $this->submissionsRepository
            ->shouldReceive('getSubmissionForEachStudentAndGivenCharon')
            ->with($charon->id);

        $this->service->getSubmissionForEachStudent($charon->id);
    }

    public function testGetSubmissionById()
    {
        $this->submission->id = 999;

        $this->submissionsRepository
            ->shouldReceive('find')
            ->with(999)
            ->once()
            ->andReturn($this->submission);

        $this->service->getSubmissionById(999);
    }
}
