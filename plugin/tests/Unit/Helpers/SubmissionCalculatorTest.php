<?php

namespace Tests\Unit\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use \Mockery as m;
use Tests\TestCase;
use Tests\Traits\MocksSubmission;
use TTU\Charon\Services\SubmissionCalculatorService;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeItem;

class SubmissionCalculatorTest extends TestCase
{
    use MocksSubmission;

    /** @var SubmissionCalculatorService */
    protected $submissionCalculator;

    public function setUp()
    {
        $this->submissionCalculator = new SubmissionCalculatorService;
    }

    public function testDetectsThatSubmissionIsWorseThanLast()
    {
        $submission = $this->getMockWorseSubmission();

        $result = $this->submissionCalculator->submissionIsBetterThanLast($submission);

        $this->assertFalse($result);
    }

    public function testDetectsThatSubmissionIsBetterThanLast()
    {
        $submission = $this->getMockBetterSubmission();

        $result = $this->submissionCalculator->submissionIsBetterThanLast($submission);

        $this->assertTrue($result);
    }

    public function testSubmissionIsBetterThanLastDoesNotCalculateWithoutGrademap()
    {
        $submission = new Submission;
        $submission->results = Collection::make([
            $this->getMockResult(1, 2),
            $this->getMockResult(1, 1),
            $this->getMockResultWithoutGrademap(5)  // Should not make new submission better
        ]);

        $result = $this->submissionCalculator->submissionIsBetterThanLast($submission);

        $this->assertFalse($result);
    }

    public function testCalculateResultTakesDeadlinesIntoAccount()
    {
        $this->setupDeadlineTest();

        $now = Carbon::now('Europe/Tallinn');
        $deadlines = $this->getDeadlinesFromNow($now);
        $result = $this->getLatePerfectResult($now->copy()->addMinute());

        $calculationResult = $this->submissionCalculator->calculateResultFromDeadlines($result, $deadlines);

        $this->assertEquals(50, $calculationResult);
    }

    public function testCalculateResultWhenNotTestsGradeType()
    {
        $now = Carbon::now('Europe/Tallinn');
        $deadlines = $this->getDeadlinesFromNow($now);
        $result = $this->getLatePerfectResult($now->copy()->addMinute(), 101);

        $calculationResult = $this->submissionCalculator->calculateResultFromDeadlines($result, $deadlines);

        $this->assertEquals(100, $calculationResult);
    }

    public function testCalculateResultNoGrademap()
    {
        $result = m::mock(Result::class, ['getGrademap' => null])->makePartial();
        $result->percentage = 1;

        $calculationResult = $this->submissionCalculator->calculateResultFromDeadlines($result, []);

        $this->assertEquals(0, $calculationResult);
    }

    private function setupDeadlineTest()
    {
        // Needed for using facades in code
        $this->createApplication();

        Config::set('get', 'Europe/Tallinn');
    }

    private function getDeadlinesFromNow(Carbon $now)
    {
        $afterOneHour = $now->copy()->addHour();

        $deadline1 = new \StdClass;
        $deadline1->deadline_time = $now;
        $deadline1->percentage = 50;
        $deadline2 = new \StdClass;
        $deadline2->deadline_time = $afterOneHour;
        $deadline2->percentage = 20;
        $deadlines = [$deadline1, $deadline2];
        return $deadlines;
    }

    private function getLatePerfectResult($time, $gradeTypeCode = 1)
    {
        $submission = new \StdClass;
        $submission->git_timestamp = $time;

        $gradeItem = new GradeItem;
        $gradeItem->grademax = 100;
        $grademap = new Grademap;
        $grademap->gradeItem = $gradeItem;
        $result = m::mock(Result::class, ['getGrademap' => $grademap])->makePartial();
        $result->submission = $submission;
        $result->percentage = 1;
        $result->grade_type_code = $gradeTypeCode;

        return $result;
    }
}
