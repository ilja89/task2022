<?php

namespace Tests\Unit\Services;

use Tests\MockingTest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\GradingMethod;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\GradingService;
use Zeizig\Moodle\Services\UserService;

class CharonGradingServiceTest extends MockingTest
{
    public function testDetectsThatGradesShouldBeUpdatedForce()
    {
        $gradingService = $this->getGradingService(
            [null, null, null, null],
            ['hasConfirmedSubmission' => true, 'shouldUpdateBasedOnGradingMethod' => false]
        );
        $submission     = $this->getMockBuilder(Submission::class)->getMock();
        $result         = $gradingService->gradesShouldBeUpdated($submission, true);

        $this->assertTrue($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasConfirmed()
    {
        $submissionService = $this->getSubmissionService(
            [null, null],
            ['charonHasConfirmedSubmission' => true]
        );
        $gradingService    = $this->getGradingService(
            [null, $submissionService, null, null],
            ['hasConfirmedSubmission' => true, 'shouldUpdateBasedOnGradingMethod' => false]
        );
        $submission        = $this->getMockBuilder(Submission::class)->getMock();

        $result = $gradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasBetterPrevious()
    {
        $charonGradingService = $this->getGradingService(
            [null, null, null, null],
            ['hasConfirmedSubmission' => false]
        );

        $charon = $this->getNewPreferBestCharonMock();
        $submission = new Submission;
        $submission->charon = $charon;
        $submission->results = $this->getMockWorseResults();

        $result = $charonGradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    private function getGradingService($constructorArgs, $methodReturns)
    {
        $originalArgs = [
            GradingService::class,
            SubmissionService::class,
            GrademapService::class,
            CharonRepository::class,
        ];

        return $this->getNewMock(CharonGradingService::class, $originalArgs, $constructorArgs, $methodReturns);
    }

    private function getSubmissionService($constructorArgs, $methodReturns)
    {
        $originalArgs = [UserService::class, GradebookService::class];

        return $this->getNewMock(SubmissionService::class, $originalArgs, $constructorArgs, $methodReturns);
    }

    private function getMockWorseResults()
    {
        $results = [];
        $results[] = $this->getMockResult(0.5, 1);
        $results[] = $this->getMockResult(0, 1);
        $results[] = $this->getMockResult(0, 1);

        return $results;
    }

    private function getMockResult($calculatedResult, $previousResult = 0)
    {
        $gradeGrade = new GradeGrade;
        $gradeGrade->finalgrade = $previousResult;
        $gradeItem = $this->getNewMock(GradeItem::class, [], [], ['gradesForUser' => $gradeGrade]);
        $grademap = new Grademap;
        $grademap->gradeItem = $gradeItem;
        $result = $this->getNewMock(Result::class, [], [], ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;

        return $result;
    }

    private function getNewPreferBestCharonMock()
    {
        $gradingMethod = $this->getNewMock(GradingMethod::class, [], [], ['isPreferBest' => true]);
        $charon = new Charon;
        $charon->gradingMethod = $gradingMethod;
        return $charon;
    }
}
