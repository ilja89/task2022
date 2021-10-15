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
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\AreteResponseParser;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradebookService;

class SubmissionServiceTest extends TestCase
{
    /** @var Mock|Submission */
    private $submission;

    /** @var Mock|GradebookService */
    private $gradebookService;

    /** @var Mock|CharonGradingService */
    private $charonGradingService;

    /** @var Mock|AreteResponseParser */
    private $requestHandlingService;

    /** @var Mock|SubmissionsRepository */
    private $submissionsRepository;

    /** @var Mock|UserRepository */
    private $userRepository;

    /** @var Mock|GrademapService */
    private $grademapService;

    /** @var Mock|CharonRepository */
    private $charonRepository;

    /** @var SubmissionService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->submission = Mockery::mock(Submission::class)->makePartial();

        $this->service = new SubmissionService(
            $this->gradebookService = Mockery::mock(GradebookService::class),
            $this->charonGradingService = Mockery::mock(CharonGradingService::class),
            $this->requestHandlingService = Mockery::mock(AreteResponseParser::class),
            $this->submissionsRepository = Mockery::mock(SubmissionsRepository::class),
            $this->userRepository = Mockery::mock(UserRepository::class),
            $this->grademapService = Mockery::mock(GrademapService::class),
            $this->charonRepository = Mockery::mock(CharonRepository::class)
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
        $charon = new Charon();

        $request['gitCallBackId'] = $callback->id;

        $this->requestHandlingService
            ->shouldReceive('getSubmissionFromRequest')
            ->with($request, $charon, 5)
            ->andReturn($this->submission);

        $this->submission->shouldReceive('save');

        $actual = $this->service->saveSubmission($request, $charon, 5);

        $this->assertEquals(3, $actual->git_callback_id);
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
        $this->charonGradingService->shouldReceive('updateGrade')->with($this->submission, 7);

        /** @var Charon $charon */
        $charon = Mockery::mock(Charon::class, ['submissions' => $submissions])->makePartial();
        $charon->grademaps = [new Grademap(['grade_type_code' => 1]), new Grademap(['grade_type_code' => 101])];

        $actual = $this->service->addNewEmptySubmission($charon, 7);

        $this->assertSame($this->submission, $actual);
    }

    public function testCalculateSubmissionTotalGradeIfFormulaPresent()
    {
        GradeItem::unguard();

        $gradeItem = new GradeItem(['calculation' => '=##gi3## * ##gi5##']);

        /** @var Charon $charon */
        $charon = Mockery::mock('Charon');
        $charon->category = Mockery::mock('Category', ['getGradeItem' => $gradeItem]);

        $this->submission->charon = $charon;
        $this->submission->user_id = 7;
        $this->submission->results = [
            $this->makeResult('Tests', 0.5, 3),
            $this->makeResult('Style', 1, 5),
        ];

        $this->grademapService
            ->shouldReceive('findFormulaParams')
            ->with('=##gi3## * ##gi5##', $this->submission->results, 7)
            ->once()
            ->andReturn(['gi3' => 0.5, 'gi5' => 1]);

        $this->gradebookService
            ->shouldReceive('calculateResultWithFormulaParams')
            ->with('=##gi3## * ##gi5##', ['gi3' => 0.5, 'gi5' => 1])
            ->andReturn(0.5009);

        $result = $this->service->calculateSubmissionTotalGrade($this->submission, 7);

        $this->assertEquals(0.501, $result);
    }

    public function testCalculateSubmissionTotalGradeIfFormulaMissing()
    {
        GradeItem::unguard();

        $gradeItem = new GradeItem();

        /** @var Charon $charon */
        $charon = Mockery::mock('Charon');
        $charon->category = Mockery::mock('Category', ['getGradeItem' => $gradeItem]);

        $this->submission->charon = $charon;
        $this->submission->results = [
            $this->makeResult('Tests', 0.5009, 0, 7),
            $this->makeResult('Tests', 0.9, 0, 0),
            $this->makeResult('Style', 1.004, 0, 7),
        ];

        $result = $this->service->calculateSubmissionTotalGrade($this->submission, 7);

        $this->assertEquals(1.505, $result);
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

    private function makeResult($identifier, $calculatedResult, $gradeItemId = 1, $userId = 0)
    {
        $gradeItem = new GradeItem(['idnumber' => $identifier]);
        $gradeItem->id = $gradeItemId;
        $grademap = new Grademap(['gradeItem' => $gradeItem]);

        /** @var Mock|Result $result */
        $result = Mockery::mock('Result', ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;
        $result->user_id = $userId;

        return $result;
    }
}
