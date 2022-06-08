<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use Tests\Traits\MocksSubmission;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\GradingMethod;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionCalculatorService;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\Group;
use Zeizig\Moodle\Services\GradebookService;

class SubmissionCalculatorServiceTest extends TestCase
{
    use MocksSubmission;

    /** @var Mock|GradebookService */
    protected $gradebookService;

    /** @var SubmissionCalculatorService */
    protected $service;

    /** @var GrademapService */
    protected $grademapService;

    public function setUp(): void
    {
        $this->service = new SubmissionCalculatorService(
            $this->gradebookService = Mockery::mock(GradebookService::class),
            $this->grademapService = Mockery::mock(GrademapService::class)
        );
    }

    public function testSubmissionIsBetterThanActiveDetectsWorse()
    {
        $results = collect([
            $this->makeResult(1, 1, 5),
            $this->makeResult(101, 1, 6),
            $this->makeResult(1001, 1, 7)
        ]);

        $studentId = 3;
        $gradeItem = new GradeItem();
        $calculation = "=##gi1## * ##gi2## * ##gi3##";
        $gradeItem->calculation = $calculation;

        $newSubmissionParams = ["gi1" => 0.5, "gi2" => 0, "gi3" => 0];
        $activeSubmissionParams = ["gi1" => 1, "gi2" => 1, "gi3" => 1];

        $newSubmissionPotentialTotal = 0.5;
        $activeSubmissionPotentialTotal = 1;

        /** @var Mock|GradeCategory $category */
        $category = Mockery::mock(GradeCategory::class);
        $category->shouldReceive("getGradeItem")
            ->twice()
            ->andReturn($gradeItem);

        $charon = new Charon();
        $charon->category = $category;

        $submission = new Submission();
        $submission->charon = $charon;
        $submission->results = $results;

        $this->grademapService->shouldReceive("findFormulaParams")
            ->with($calculation, $results, $studentId, true, false)
            ->once()
            ->andReturn($newSubmissionParams);

        $this->gradebookService->shouldReceive("calculateResultWithFormulaParams")
            ->with($calculation, $newSubmissionParams)
            ->once()
            ->andReturn($newSubmissionPotentialTotal);

        $this->grademapService->shouldReceive("findFormulaParamsFromGradebook")
            ->with($calculation, [], $studentId, true, false)
            ->once()
            ->andReturn($activeSubmissionParams);

        $this->gradebookService->shouldReceive("calculateResultWithFormulaParams")
            ->with($calculation, $activeSubmissionParams)
            ->once()
            ->andReturn($activeSubmissionPotentialTotal);

        $actual = $this->service->submissionIsBetterThanActive($submission, $studentId);

        $this->assertFalse($actual);
    }

    public function testSubmissionIsBetterThanActiveDetectsBetter()
    {
        $results = collect([
            $this->makeResult(1, 0.5, 5),
            $this->makeResult(101, 1, 6)
        ]);

        $studentId = 3;
        $gradeItem = new GradeItem();
        $calculation = "=##gi1## * ##gi2## * ##gi3##";
        $gradeItem->calculation = $calculation;

        $newSubmissionParams = ["gi1" => 1, "gi2" => 1];
        $activeSubmissionParams = ["gi1" => 0.5, "gi2" => 1];

        $newSubmissionPotentialTotal = 1;
        $activeSubmissionPotentialTotal = 0.5;

        /** @var Mock|GradeCategory $category */
        $category = Mockery::mock(GradeCategory::class);
        $category->shouldReceive("getGradeItem")
            ->twice()
            ->andReturn($gradeItem);

        $charon = new Charon();
        $charon->category = $category;

        $submission = new Submission();
        $submission->charon = $charon;
        $submission->results = $results;

        $this->grademapService->shouldReceive("findFormulaParams")
            ->with($calculation, $results, $studentId, true, false)
            ->once()
            ->andReturn($newSubmissionParams);

        $this->gradebookService->shouldReceive("calculateResultWithFormulaParams")
            ->with($calculation, $newSubmissionParams)
            ->once()
            ->andReturn($newSubmissionPotentialTotal);

        $this->grademapService->shouldReceive("findFormulaParamsFromGradebook")
            ->with($calculation, [], $studentId, true, false)
            ->once()
            ->andReturn($activeSubmissionParams);

        $this->gradebookService->shouldReceive("calculateResultWithFormulaParams")
            ->with($calculation, $activeSubmissionParams)
            ->once()
            ->andReturn($activeSubmissionPotentialTotal);

        $actual = $this->service->submissionIsBetterThanActive($submission, $studentId);

        $this->assertTrue($actual);
    }

    public function testSubmissionIsBetterThanActiveIgnoresResultsWithoutGrademap()
    {
        $this->markTestSkipped("Out of date, needs attention");

        /** @var Mock|Result $withoutGrademap */
        $withoutGrademap = $this->makeResult(2, 1, 5);
        $withoutGrademap->shouldReceive('getGrademap')->andReturnNull();

        $results = collect([
            $this->makeResult(1, 2),
            $this->makeResult(1, 1),
            $withoutGrademap
        ]);

        /** @var Mock|Submission $submission */
        $submission = Mockery::mock(Submission::class);

        $submission->shouldReceive('results->where->get')
            ->once()
            ->andReturn($results);

        $actual = $this->service->submissionIsBetterThanActive($submission, 3);

        $this->assertFalse($actual);
    }

    public function testCalculateResultFromDeadlinesReturnsZeroWhenNoGrademap()
    {
        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => null])->makePartial();
        $result->percentage = 1;

        $actual = $this->service->calculateResultFromDeadlines($result, collect([]));

        $this->assertEquals(0, $actual);
    }

    public function testCalculateResultFromDeadlinesReturnsUnchangedWhenPersistent()
    {
        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => new Grademap(['persistent' => true])])->makePartial();
        $result->percentage = 0;
        $result->calculated_result = 0.31;
        $result->grade_type_code = 1002;

        $actual = $this->service->calculateResultFromDeadlines($result, collect([]));

        $this->assertEquals(0.31, $actual);
    }

    /**
     * Test grade types are <= 100
     */
    public function testCalculateResultFromDeadlinesIgnoresDeadlineWhenNoTestGradeType()
    {
        GradeItem::unguard();

        $yesterday = new Deadline([
            'deadline_time' => Carbon::now()->subDay(),
            'percentage' => 50
        ]);

        $grademap = new Grademap();
        $grademap->gradeItem = new GradeItem(['grademax' => 100]);

        $submission = $this->makeSubmissionAt(Carbon::now());

        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => $grademap])->makePartial();
        $result->submission = $submission;
        $result->percentage = 1;
        $result->grade_type_code = 101;

        $actual = $this->service->calculateResultFromDeadlines($result, collect([$yesterday]));

        $this->assertEquals(100, $actual);
    }

    public function testCalculateResultFromDeadlinesReturnsZeroWhenNoDeadlinesFoundByUserGroup()
    {
        Group::unguard();

        $yesterday = new Deadline([
            'deadline_time' => Carbon::now()->subDay(),
            'percentage' => 50,
            'group_id' => 3
        ]);

        $userGroups = collect([new Group(['id' => 7]), new Group(['id' => 11])]);

        /** @var Mock|Builder $userGroupBuilder */
        $userGroupBuilder = Mockery::mock(Builder::class);
        $userGroupBuilder->shouldReceive('groups->where')->with('courseid', 5)->andReturn($userGroupBuilder);
        $userGroupBuilder->shouldReceive('get')->andReturn($userGroups);

        /** @var Mock|Submission $submission */
        $submission = Mockery::spy($this->makeSubmissionAt(Carbon::now()));
        $submission->originalSubmission = null;
        $submission->user = $userGroupBuilder;
        $submission->charon = new Charon(['course' => 5]);

        $grademap = new Grademap();
        $grademap->gradeItem = new GradeItem(['grademax' => 100]);

        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => $grademap])->makePartial();
        $result->submission = $submission;
        $result->percentage = 1;
        $result->grade_type_code = 1;

        $actual = $this->service->calculateResultFromDeadlines($result, collect([$yesterday]));

        $this->assertEquals(0, $actual);
    }

    /**
     * Deadline with percentage 45 should match as lowest past due date with user group overlap
     * Do not take into consideration submissions for a charon with grading method as 'prefer_best_each_grade'
     */
    public function testCalculateResultFromDeadlinesReturnsSmallestScoreFromPassedDeadlines()
    {
        Group::unguard();

        $deadlines = collect([
            new Deadline(['percentage' => 20, 'group_id' => null, 'deadline_time' => Carbon::now()->addDays(3)]),
            new Deadline(['percentage' => 30, 'group_id' => 7, 'deadline_time' => Carbon::now()->addDays(2)]),
            new Deadline(['percentage' => 40, 'group_id' => 11, 'deadline_time' => Carbon::now()->addDays(1)]),
            new Deadline(['percentage' => 50, 'group_id' => null, 'deadline_time' => Carbon::now()->subDays(1)]),
            new Deadline(['percentage' => 45, 'group_id' => 7, 'deadline_time' => Carbon::now()->subDays(1)]),
            new Deadline(['percentage' => 5, 'group_id' => 3, 'deadline_time' => Carbon::now()->subDays(1)]),
            new Deadline(['percentage' => 60, 'group_id' => 11, 'deadline_time' => Carbon::now()->subDays(2)]),
        ]);

        $userGroups = collect([new Group(['id' => 7]), new Group(['id' => 11])]);

        /** @var Mock|Builder $userGroupBuilder */
        $userGroupBuilder = Mockery::mock(Builder::class);
        $userGroupBuilder->shouldReceive('groups->where')->with('courseid', 5)->andReturn($userGroupBuilder);
        $userGroupBuilder->shouldReceive('get')->andReturn($userGroups);

        $charon = new Charon(['course' => 5]);
        $charon->gradingMethod = new GradingMethod(['code' => rand(1, 2)]);

        /** @var Mock|Submission $submission */
        $submission = Mockery::spy($this->makeSubmissionAt(Carbon::now()));
        $submission->originalSubmission = null;
        $submission->user = $userGroupBuilder;
        $submission->charon = $charon;

        $grademap = new Grademap();
        $grademap->gradeItem = new GradeItem(['grademax' => 100]);

        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => $grademap])->makePartial();
        $result->submission = $submission;
        $result->percentage = 1;
        $result->grade_type_code = 1;

        $actual = $this->service->calculateResultFromDeadlines($result, $deadlines);

        $this->assertEquals(45, $actual);
    }

    public function testGetUserActiveGradeForCharonDelegatesToGradebook()
    {
        GradeItem::unguard();

        /** @var Mock|GradeCategory $category */
        $category = Mockery::mock(GradeCategory::class);
        $category->shouldReceive('getGradeItem')->andReturn(new GradeItem(['id' => 3]));

        $charon = new Charon();
        $charon->category = $category;

        $expected = new GradeGrade();

        $this->gradebookService
            ->shouldReceive('getGradeForGradeItemAndUser')
            ->with(3, 5)
            ->once()
            ->andReturn($expected);

        $actual = $this->service->getUserActiveGradeForCharon($charon, 5);

        $this->assertSame($expected, $actual);
    }

    /**
     * @param $createdAt
     * @return Submission
     */
    private function makeSubmissionAt($createdAt) {
        $submission = new Submission();
        $submission->setDateFormat('Y-m-d H:i:s');
        $submission->created_at = $createdAt;
        return $submission;
    }

    public function testCalculateSubmissionTotalGradeIfFormulaPresent()
    {
        GradeItem::unguard();

        $gradeItem = new GradeItem(['calculation' => '=##gi3## * ##gi5##']);

        /** @var Charon $charon */
        $charon = Mockery::mock('Charon');
        $charon->category = Mockery::mock('Category', ['getGradeItem' => $gradeItem]);

        $submission = new Submission();
        $submission->charon = $charon;
        $submission->user_id = 7;
        $submission->results = [
            $this->makeResult('Tests', 0.5, 3),
            $this->makeResult('Style', 1, 5),
        ];

        $this->grademapService
            ->shouldReceive('findFormulaParams')
            ->with('=##gi3## * ##gi5##', $submission->results, 7, false, false)
            ->once()
            ->andReturn(['gi3' => 0.5, 'gi5' => 1]);

        $this->gradebookService
            ->shouldReceive('calculateResultWithFormulaParams')
            ->with('=##gi3## * ##gi5##', ['gi3' => 0.5, 'gi5' => 1])
            ->andReturn(0.5009);

        $result = $this->service->calculateSubmissionTotalGrade($submission, 7);

        $this->assertEquals(0.501, $result);
    }

    public function testCalculateSubmissionTotalGradeIfFormulaMissing()
    {
        GradeItem::unguard();

        $gradeItem = new GradeItem();

        /** @var Charon $charon */
        $charon = Mockery::mock('Charon');
        $charon->category = Mockery::mock('Category', ['getGradeItem' => $gradeItem]);

        $submission = new Submission();
        $submission->charon = $charon;
        $submission->results = [
            $this->makeResult('Tests', 0.5009, 0, 7),
            $this->makeResult('Tests', 0.9, 0, 0),
            $this->makeResult('Style', 1.004, 0, 7),
        ];

        $result = $this->service->calculateSubmissionTotalGrade($submission, 7);

        $this->assertEquals(1.505, $result);
    }

    private function makeResult($identifier, $calculatedResult, $gradeItemId = 1, $userId = 0)
    {
        $gradeItem = new GradeItem();
        $gradeItem->idnumber = $identifier;
        $gradeItem->id = $gradeItemId;
        $grademap = new Grademap(['gradeItem' => $gradeItem]);

        /** @var Mock|Result $result */
        $result = Mockery::mock('Result', ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;
        $result->user_id = $userId;

        return $result;
    }
}
