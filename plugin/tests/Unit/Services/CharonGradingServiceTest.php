<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Collection;
use Mockery as m;
use Tests\BaseTests\GradeMockingTest;
use Tests\Traits\MocksCharon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
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
        $gradingService    = $this->getGradingService([null, $submissionService, null, null, null], []);

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
        $gradingService    = $this->getGradingService([null, $submissionService, null, null, null], []);

        $submission         = new Submission;
        $submission->charon = $this->getNewPreferLastCharonMock();

        $result = $gradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasBetterPrevious()
    {
        $submissionService    = $this->getSubmissionService([null, null], ['charonHasConfirmedSubmission' => false]);
        $charonGradingService = $this->getGradingService([null, $submissionService, null, null, null], []);

        $submission = $this->getMockWorseSubmission(['charon' => $this->getNewPreferBestCharonMock()]);

        $result = $charonGradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testUpdatesGradesWhenForced()
    {
        $gradingService = m::mock(GradingService::class)->shouldReceive('updateGrade')->times(2)->getMock();

        $charonGradingService = new CharonGradingService(
            $gradingService,
            m::mock(SubmissionService::class),
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            m::mock(SubmissionsRepository::class)
        );

        $charon = $this->getCharon(['id' => 1], [
                'courseModule'      => factory(\Zeizig\Moodle\Models\CourseModule::class)->make(['course' => 1]),
                'getGradeTypeCodes' => Collection::make([1, 101]),
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
            m::mock(CharonRepository::class),
            m::mock(SubmissionsRepository::class)
        );

        $submission = $this->getMockWorseSubmission(['charon' => $this->getNewPreferBestCharonMock()]);

        $charonGradingService->updateGradeIfApplicable($submission, false);
    }

    public function testConfirmUnconfirmsPreviousSubmissions()
    {
        $submission1 = $this->getMockWorseSubmission(['id' => 1, 'confirmed' => 1]);
        $submission2 = $this->getMockWorseSubmission(['id' => 2, 'confirmed' => 1]);
        $submission = $this->getMockWorseSubmission(['user_id' => 1, 'charon_id' => 1, 'id' => 3, 'confirmed' => 0]);

        $submissionsRepository = m::mock(SubmissionsRepository::class,
            ['findConfirmedSubmissionsForUserAndCharon' => [$submission1, $submission2]]
        )
            ->shouldReceive('confirmSubmission')->once()->with($submission)
            ->shouldReceive('unconfirmSubmission')->once()->with($submission1)
            ->shouldReceive('unconfirmSubmission')->once()->with($submission2)
            ->shouldReceive('confirmSubmission')->never()
            ->shouldReceive('unconfirmSubmission')->never()
            ->getMock()->makePartial();

        $charonGradingService = new CharonGradingService(
            m::mock(GradingService::class),
            m::mock(SubmissionService::class),
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            $submissionsRepository
        );

        $charonGradingService->confirmSubmission($submission);
    }

    public function testConfirmKeepsSubmissionConfirmed()
    {
        
    }

    private function getGradingService($constructorArgs, $methodReturns)
    {
        $originalArgs = [
            GradingService::class,
            SubmissionService::class,
            GrademapService::class,
            CharonRepository::class,
            SubmissionsRepository::class,
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
