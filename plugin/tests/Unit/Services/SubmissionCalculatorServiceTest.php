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
use TTU\Charon\Repositories\ResultRepository;
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

    /** @var Mock|ResultRepository */
    protected $resultRepository;

    /** @var SubmissionCalculatorService */
    protected $service;

    public function setUp(): void
    {
        $this->service = new SubmissionCalculatorService(
            $this->gradebookService = Mockery::mock(GradebookService::class),
            Mockery::mock(ResultRepository::class)
        );
    }

    public function testSubmissionIsBetterThanLastDetectsWorse()
    {
        $results = collect([
            $this->makeResult(0.5, 1, 1),
            $this->makeResult(0, 1, 101),
            $this->makeResult(0, 1, 1001)
        ]);

        /** @var Mock|Submission $submission */
        $submission = Mockery::mock(Submission::class);

        $submission->shouldReceive('results->where->get')
            ->once()
            ->andReturn($results);

        $actual = $this->service->submissionIsBetterThanLast($submission, 3);

        $this->assertFalse($actual);
    }

    public function testSubmissionIsBetterThanLastDetectsBetter()
    {
        $results = collect([
            $this->makeResult(1, 0.5, 1),
            $this->makeResult(1, 1, 101)
        ]);

        /** @var Mock|Submission $submission */
        $submission = Mockery::mock(Submission::class);

        $submission->shouldReceive('results->where->get')
            ->once()
            ->andReturn($results);

        $actual = $this->service->submissionIsBetterThanLast($submission, 3);

        $this->assertTrue($actual);
    }

    public function testSubmissionIsBetterThanLastIgnoresResultsWithoutGrademap()
    {
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

        $actual = $this->service->submissionIsBetterThanLast($submission, 3);

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
     */
    public function testCalculateResultFromDeadlinesReturnsSmallestScoreFromPassedDeadlines()
    {
        $this->markTestSkipped('Out of date, needs attention');

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
}
