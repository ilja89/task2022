<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\ResultRepository;
use TTU\Charon\Services\RequestHandlingService;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Services\TestSuiteService;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\GradebookService;

class SubmissionServiceTest extends TestCase
{
    /** @var Mock|Submission */
    private $submission;

    /** @var Mock|GradebookService */
    private $gradebookService;

    /** @var Mock|CharonGradingService */
    private $charonGradingService;

    /** @var Mock|RequestHandlingService */
    private $requestHandlingService;

    /** @var Mock|SubmissionsRepository */
    private $submissionsRepository;

    /** @var Mock|TestSuiteService */
    private $testSuiteService;

    /** @var Mock|ResultRepository */
    private $resultRepository;

    /** @var SubmissionService */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $this->submission = Mockery::mock(Submission::class)->makePartial();

        $this->service = new SubmissionService(
            $this->gradebookService = Mockery::mock(GradebookService::class),
            $this->charonGradingService = Mockery::mock(CharonGradingService::class),
            $this->requestHandlingService = Mockery::mock(RequestHandlingService::class),
            $this->submissionsRepository = Mockery::mock(SubmissionsRepository::class),
            $this->testSuiteService = Mockery::mock(TestSuiteService::class),
            $this->resultRepository = Mockery::mock(ResultRepository::class)
        );
    }

    /**
     * @throws Exception
     */
    public function testSaveSubmissionSavesWithResults()
    {
        GitCallback::unguard();

        $request = new Request(['style' => 100, 'testSuites' => ['suites inside']]);

        $callback = new GitCallback(['id' => 17]);

        $this->submission->id = 5;
        $this->submission->charon = null;
        $this->submission->shouldReceive('save');

        $this->requestHandlingService
            ->shouldReceive('getSubmissionFromRequest')
            ->with($request, $callback)
            ->andReturn($this->submission);

        $this->resultRepository->shouldReceive('saveIfGrademapPresent')->with([
            'submission_id' => 5,
            'grade_type_code' => 101,
            'percentage' => 1,
            'calculated_result' => 0,
            'stdout' => null,
            'stderr' => null,
        ]);

        $this->testSuiteService->shouldReceive('saveSuites')->with(['suites inside'], 5);

        $actual = $this->service->saveSubmission($request, $callback);

        $this->assertEquals(17, $actual->git_callback_id);
    }

    /**
     * @throws Exception
     */
    public function testSaveSubmissionCreatesUnsentResultsOnSubmission()
    {
        GitCallback::unguard();

        $request = new Request(['style' => 100, 'testSuites' => ['suites inside']]);

        $callback = new GitCallback(['id' => 17]);

        $charon = new Charon();
        $charon->grademaps = [new Grademap(['grade_type_code' => 1001]), new Grademap(['grade_type_code' => 101])];

        $this->submission->id = 5;
        $this->submission->charon = $charon;
        $this->submission->results = collect([new Result(['grade_type_code' => 101])]);
        $this->submission->shouldReceive('save');

        $this->requestHandlingService
            ->shouldReceive('getSubmissionFromRequest')
            ->with($request, $callback)
            ->andReturn($this->submission);

        $this->submissionsRepository
            ->shouldReceive('saveNewEmptyResult')
            ->with(5, 1001, 'This result was automatically generated')
            ->once();

        $this->submissionsRepository
            ->shouldReceive('saveNewEmptyResult')
            ->with(5, 101, 'This result was automatically generated')
            ->never();

        $this->resultRepository->shouldReceive('saveIfGrademapPresent');
        $this->testSuiteService->shouldReceive('saveSuites');

        $this->service->saveSubmission($request, $callback);
    }

    /**
     * @throws Exception
     */
    public function testSaveSubmissionSavesFiles()
    {
        GitCallback::unguard();

        $request = new Request(['style' => 100, 'testSuites' => [], 'files' => [
            ['f1'],
            ['f2']
        ]]);

        $callback = new GitCallback(['id' => 17]);

        /** @var Mock $submissionFile */
        $submissionFile = Mockery::mock(SubmissionFile::class);

        $this->submission->id = 5;
        $this->submission->charon = null;
        $this->submission->shouldReceive('save');

        $this->requestHandlingService
            ->shouldReceive('getSubmissionFromRequest')
            ->with($request, $callback)
            ->andReturn($this->submission);

        $this->requestHandlingService
            ->shouldReceive('getFileFromRequest')
            ->with(5, ['f1'], false)
            ->andReturn($submissionFile);

        $this->requestHandlingService
            ->shouldReceive('getFileFromRequest')
            ->with(5, ['f2'], false)
            ->andReturn($submissionFile);

        $submissionFile->shouldReceive('save')->twice();

        $this->resultRepository->shouldReceive('saveIfGrademapPresent');
        $this->testSuiteService->shouldReceive('saveSuites');

        $this->service->saveSubmission($request, $callback);
    }

    public function testUpdateSubmissionCalculatedResultsThrowsIfMissingResults()
    {
        $this->expectException(ResultPointsRequiredException::class);

        $result = [['calculated_result' => '', 'id' => 3]];

        $this->service->updateSubmissionCalculatedResults($this->submission, $result);
    }

    /**
     * @throws ResultPointsRequiredException
     */
    public function testUpdateSubmissionCalculatedResultsOnExisting()
    {
        Result::unguard();

        /** @var Mock|Result $result1 */
        $result1 = Mockery::mock(Result::class)->makePartial();
        $result1->id = 1;
        $result1->shouldReceive('setAttribute')->with('calculated_result', 100);
        $result1->shouldReceive('save');

        /** @var Mock|Result $result2 = Mock */
        $result2 = Mockery::mock(Result::class)->makePartial();
        $result2->id = 3;
        $result2->shouldReceive('setAttribute')->with('calculated_result', 80);
        $result2->shouldReceive('save');

        $this->submission->results = collect([$result1, $result2]);
        $this->submission->id = 5;
        $this->submission->charon_id = 7;
        $this->submission->user_id = 11;

        $this->charonGradingService->shouldReceive('updateGrade')->with($this->submission);
        $this->charonGradingService->shouldReceive('confirmSubmission')->with($this->submission);
        $this->charonGradingService->shouldReceive('updateProgressByStudentId')->with(7, 5, 11, 'Done');

        $result = [
            [ 'id' => 1, 'calculated_result' => 100 ],
            [ 'id' => 3, 'calculated_result' => 80 ],
        ];

        $actual = $this->service->updateSubmissionCalculatedResults($this->submission, $result);

        $this->assertSame($this->submission, $actual);
    }

    public function testAddsNewSubmission()
    {
        $this->submission->id = 1;

        $now = Carbon::create(2020, 11, 16, 12);
        Carbon::setTestNow($now);

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

        /** @var Charon $charon */
        $charon = Mockery::mock(Charon::class, ['submissions' => $submissions])->makePartial();
        $charon->grademaps = [new Grademap(['grade_type_code' => 1]), new Grademap(['grade_type_code' => 101])];

        $this->submissionsRepository->shouldReceive('saveNewEmptyResult')->with(1, 1, '');
        $this->submissionsRepository->shouldReceive('saveNewEmptyResult')->with(1, 101, '');
        $this->charonGradingService->shouldReceive('gradesShouldBeUpdated')->with($this->submission)->andReturn(true);
        $this->charonGradingService->shouldReceive('updateGrade')->with($this->submission);

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
