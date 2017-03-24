<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Collection;
use Mockery as m;
use Tests\MockingTest;
use Tests\Traits\MocksCharon;
use Tests\Traits\MocksSubmission;
use TTU\Charon\Helpers\SubmissionCalculator;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\GradingService;
use Zeizig\Moodle\Services\UserService;

class CharonGradingServiceTest extends MockingTest
{
    use MocksCharon, MocksSubmission;

    public function testDetectsThatGradesShouldBeUpdatedForce()
    {
        $submissionsRepository = m::mock(SubmissionsRepository::class, ['charonHasConfirmedSubmissions' => true]);
        $gradingService    = $this->getGradingService([null, null, null, $submissionsRepository, null], []);

        $submission         = new Submission;
        $submission->charon = $this->getNewPreferLastCharonMock();
        $result             = $gradingService->gradesShouldBeUpdated($submission, true);

        $this->assertTrue($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasConfirmed()
    {
        $submissionsRepository = m::mock(SubmissionsRepository::class, ['charonHasConfirmedSubmissions' => true]);
        $gradingService    = $this->getGradingService([null, null, null, $submissionsRepository, null], []);

        $submission         = new Submission;
        $submission->charon = $this->getNewPreferLastCharonMock();

        $result = $gradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testDetectsThatGradeShouldNotBeUpdatedWhenHasBetterPrevious()
    {
        $submissionsRepository = m::mock(SubmissionsRepository::class, ['charonHasConfirmedSubmissions' => false]);
        $submissionCalculator = m::mock(SubmissionCalculator::class, ['submissionIsBetterThanLast' => false]);
        $charonGradingService = $this->getGradingService([null, null, null, $submissionsRepository, $submissionCalculator], []);

        $submission = $this->getMockWorseSubmission(['charon' => $this->getNewPreferBestCharonMock()]);

        $result = $charonGradingService->gradesShouldBeUpdated($submission, false);

        $this->assertFalse($result);
    }

    public function testDetectsThatGradesShouldBeUpdatedWhenPreferLast()
    {
        $charon = $this->getNewPreferLastCharonMock();
        $submission = new Submission;
        $submission->charon = $charon;

        $charonGradingService = new CharonGradingService(
            m::mock(GradingService::class),
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            m::mock(SubmissionsRepository::class, ['charonHasConfirmedSubmissions' => false]),
            m::mock(SubmissionCalculator::class)
        );

        $result = $charonGradingService->gradesShouldBeUpdated($submission, false);

        $this->assertTrue($result);
    }

    public function testUpdatesGradesWhenForced()
    {
        $gradingService = m::mock(GradingService::class)->shouldReceive('updateGrade')->times(2)->getMock();

        $charonGradingService = new CharonGradingService(
            $gradingService,
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            m::mock(SubmissionsRepository::class),
            m::mock(SubmissionCalculator::class)
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
        $submissionCalculator = m::mock(SubmissionCalculator::class, ['submissionIsBetterThanLast' => false]);
        $charonGradingService = new CharonGradingService(
            $gradingService,
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            m::mock(SubmissionsRepository::class, ['charonHasConfirmedSubmissions' => false]),
            $submissionCalculator
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
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            $submissionsRepository,
            m::mock(SubmissionCalculator::class)
        );

        $charonGradingService->confirmSubmission($submission);
    }

    public function testConfirmDoesNotUnconfirmAlreadyConfirmed()
    {
        $submission = $this->getMockWorseSubmission(['user_id' => 1, 'charon_id' => 1, 'id' => 3, 'confirmed' => 1]);

        $submissionsRepository = m::mock(SubmissionsRepository::class,
            ['findConfirmedSubmissionsForUserAndCharon' => [$submission]]
        )
                                  ->shouldReceive('confirmSubmission')->once()->with($submission)
                                  ->shouldReceive('unconfirmSubmission')->never()
                                  ->getMock()->makePartial();

        $charonGradingService = new CharonGradingService(
            m::mock(GradingService::class),
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            $submissionsRepository,
            m::mock(SubmissionCalculator::class)
        );

        $charonGradingService->confirmSubmission($submission);
    }

    public function testCalculatesCorrectCalculatedResultsForSubmission()
    {
        $submission = new Submission;
        $submission->charon = $this->getCharon(['deadlines' => []]);
        $result1 = m::mock(Result::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $result2 = m::mock(Result::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $submission->results = [$result1, $result2];

        $submissionCalculator = m::mock(SubmissionCalculator::class);
        $submissionCalculator->shouldReceive('calculateResultFromDeadlines')->withArgs([$result1, []])->once()->andReturn(1);
        $submissionCalculator->shouldReceive('calculateResultFromDeadlines')->withArgs([$result2, []])->once()->andReturn(2);
        $submissionCalculator->shouldReceive('calculateResultFromDeadlines')->never();

        $charonGradingService = new CharonGradingService(
            m::mock(GradingService::class),
            m::mock(GrademapService::class),
            m::mock(CharonRepository::class),
            m::mock(SubmissionsRepository::class),
            $submissionCalculator
        );

        $charonGradingService->calculateCalculatedResultsForNewSubmission($submission);

        $this->assertEquals(1, $result1->calculated_result);
        $this->assertEquals(2, $result2->calculated_result);
    }

    private function getGradingService($constructorArgs, $methodReturns)
    {
        $originalArgs = [
            GradingService::class,
            GrademapService::class,
            CharonRepository::class,
            SubmissionsRepository::class,
            SubmissionCalculator::class
        ];

        return $this->getNewMock(CharonGradingService::class, $originalArgs, $constructorArgs,
            $methodReturns)->makePartial();
    }
}
