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
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\AreteResponseParser;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
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

    /** @var SubmissionService */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $this->submission = Mockery::mock(Submission::class)->makePartial();

        $this->service = new SubmissionService(
            $this->gradebookService = Mockery::mock(GradebookService::class),
            $this->charonGradingService = Mockery::mock(CharonGradingService::class),
            $this->requestHandlingService = Mockery::mock(AreteResponseParser::class),
            $this->submissionsRepository = Mockery::mock(SubmissionsRepository::class),
            $this->userRepository = Mockery::mock(UserRepository::class)
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

        $this->submissionsRepository->shouldReceive('saveNewEmptyResult')->with(1, 1, '');
        $this->submissionsRepository->shouldReceive('saveNewEmptyResult')->with(1, 101, '');
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

        $gradeItem = new GradeItem(['calculation' => '=##gi1## * ##gi2##']);

        /** @var Charon $charon */
        $charon = Mockery::mock('Charon');
        $charon->course = 3;
        $charon->category = Mockery::mock('Category', ['getGradeItem' => $gradeItem]);

        $this->submission->charon = $charon;
        $this->submission->results = [
            $this->makeResult('Tests', 0.5),
            $this->makeResult('Style', 1),
        ];

        $this->gradebookService
            ->shouldReceive('calculateResultFromFormula')
            ->with('=##gi1## * ##gi2##', ['tests' => 0.5, 'style' => 1], 3)
            ->andReturn(0.5009);

        $result = $this->service->calculateSubmissionTotalGrade($this->submission);

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
            $this->makeResult('Tests', 0.5009),
            $this->makeResult('Style', 1.004),
        ];

        $result = $this->service->calculateSubmissionTotalGrade($this->submission);

        $this->assertEquals(1.505, $result);
    }

    private function makeResult($identifier, $calculatedResult)
    {
        $gradeItem = new GradeItem(['idnumber' => $identifier]);
        $grademap = new Grademap(['gradeItem' => $gradeItem]);

        /** @var Result $result */
        $result = Mockery::mock('Result', ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;

        return $result;
    }
}
