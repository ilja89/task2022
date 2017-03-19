<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Collection;
use Mockery as m;
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
        $submissionService    = $this->getSubmissionService([null, null], ['charonHasConfirmedSubmission' => false]);
        $charonGradingService = $this->getGradingService([null, $submissionService, null, null], []);

        $submission = $this->getMockWorseSubmission(['charon' => $this->getNewPreferBestCharonMock()]);

        $result = $charonGradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testUpdatesGradesWhenForced()
    {
        $gradingService = m::mock(GradingService::class)->shouldReceive('updateGrade')->times(3)->getMock();

        $charonGradingService = new CharonGradingService(
            $gradingService,
            m::mock(SubmissionService::class),
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class)
        );

        $charon = $this->getCharon(['id' => 1], [
                'courseModule'      => factory(\Zeizig\Moodle\Models\CourseModule::class)->make(['course' => 1]),
                'getGradeTypeCodes' => Collection::make([1, 101, 1001]),
            ]
        );

        $submission = $this->getMockWorseSubmission(['charon' => $charon, 'user_id' => 1]);

        $charonGradingService->updateGradeIfApplicable($submission, true);
    }

    public function testDoesNotUpgradeGradesWhenIsWorse()
    {
        $gradingService = m::mock(GradingService::class)->shouldReceive('updateGrade')->getMock();
        $submissionService = m::mock(SubmissionService::class, ['charonHasConfirmedSubmission' => false]);
        $charonGradingService = new CharonGradingService(
            $gradingService,
            $submissionService,
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class)
        );

        $submission = $this->getMockWorseSubmission(['charon' => $this->getNewPreferBestCharonMock()]);

        $charonGradingService->updateGradeIfApplicable($submission, false);
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
