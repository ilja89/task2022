<?php

namespace Tests\Unit\Services;

use Tests\BaseTests\GradeMockingTest;
use Tests\Traits\MocksCharon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\GradingService;
use Zeizig\Moodle\Services\UserService;

class CharonGradingServiceTest extends GradeMockingTest
{
    use MocksCharon;

    public function testDetectsThatGradesShouldBeUpdatedForce()
    {
        $submissionService = $this->getSubmissionService([null, null], ['charonHasConfirmedSubmission' => true]);
        $gradingService    = $this->getGradingService([null, $submissionService, null, null], []);

        $submission         = new Submission;
        $submission->charon = $this->getNewPreferLastCharonMock();
        $result             = $gradingService->gradesShouldBeUpdated($submission, true);

        $this->assertTrue($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasConfirmed()
    {
        $submissionService = $this->getSubmissionService(
            [null, null],
            ['charonHasConfirmedSubmission' => true]
        );
        $gradingService    = $this->getGradingService([null, $submissionService, null, null], []);

        $submission         = new Submission;
        $submission->charon = $this->getNewPreferLastCharonMock();

        $result = $gradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasBetterPrevious()
    {
        $submissionService    = $this->getSubmissionService(
            [null, null],
            ['charonHasConfirmedSubmission' => false]
        );
        $charonGradingService = $this->getGradingService(
            [null, $submissionService, null, null], []
        );

        $charon              = $this->getNewPreferBestCharonMock();
        $submission          = new Submission;
        $submission->charon  = $charon;
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

        return $this->getNewMock(CharonGradingService::class, $originalArgs, $constructorArgs,
            $methodReturns)->makePartial();
    }

    private function getSubmissionService($constructorArgs, $methodReturns)
    {
        $originalArgs = [UserService::class, GradebookService::class];

        return $this->getNewMock(SubmissionService::class, $originalArgs, $constructorArgs,
            $methodReturns)->makePartial();
    }
}
