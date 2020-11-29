<?php

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\GradingMethod;
use TTU\Charon\Models\Registration;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use Tests\TestCase;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionCalculatorService;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Services\GradingService;

class CharonGradingServiceTest extends TestCase
{
    /** @var Mock|GradingService */
    private $gradingService;

    /** @var Mock|GrademapService */
    private $grademapService;

    /** @var Mock|CharonRepository */
    private $charonRepository;

    /** @var Mock|SubmissionsRepository */
    private $submissionsRepository;

    /** @var Mock|SubmissionCalculatorService */
    private $calculatorService;

    /** @var Mock|DefenseRegistrationRepository */
    private $registrationRepository;

    /** @var Mock|User */
    private $user;

    /** @var CharonGradingService */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $this->service = new CharonGradingService(
            $this->gradingService = Mockery::mock(GradingService::class),
            $this->grademapService = Mockery::mock(GrademapService::class),
            $this->charonRepository = Mockery::mock(CharonRepository::class),
            $this->submissionsRepository = Mockery::mock(SubmissionsRepository::class),
            $this->calculatorService = Mockery::mock(SubmissionCalculatorService::class),
            $this->registrationRepository = Mockery::mock(DefenseRegistrationRepository::class),
            $this->user = Mockery::mock(User::class)
        );
    }

    public function testCalculateCalculatedResultsForNewSubmissionSavesResults()
    {
        $deadlines = collect(new Deadline());

        /** @var Mock|Result $result1 */
        $result1 = Mockery::mock(Result::class);

        /** @var Mock|Result $result2 */
        $result2 = Mockery::mock(Result::class);

        $submission = new Submission();
        $submission->charon = Mockery::mock(Charon::class)->makePartial();
        $submission->charon->deadlines = $deadlines;
        $submission->results = collect([$result1, $result2]);

        $this->calculatorService
            ->shouldReceive('calculateResultFromDeadlines')
            ->with($result1, $deadlines)
            ->once()
            ->andReturn(3);

        $this->calculatorService
            ->shouldReceive('calculateResultFromDeadlines')
            ->with($result2, $deadlines)
            ->once()
            ->andReturn(5);

        $result1->shouldReceive('setAttribute')->with('calculated_result', 3)->once();
        $result1->shouldReceive('save')->once();

        $result2->shouldReceive('setAttribute')->with('calculated_result', 5)->once();
        $result2->shouldReceive('save')->once();

        $this->service->calculateCalculatedResultsForNewSubmission($submission);
    }

    public function testUpdateGradeUpdatesResultsMatchingCharonGradeTypes()
    {
        CourseModule::unguard();
        Charon::unguard();

        /** @var Mock|Charon $charon */
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 3;

        $charon->shouldReceive('courseModule')->once()->andReturn(new CourseModule(['course' => 5]));
        $charon->shouldReceive('getGradeTypeCodes')->once()->andReturn(collect([7, 11]));

        $submission = new Submission(['user_id' => 17]);
        $submission->charon = $charon;
        $submission->results = collect([
            new Result(['grade_type_code' => 7, 'calculated_result' => 107]),
            new Result(['grade_type_code' => 11, 'calculated_result' => 111]),
            new Result(['grade_type_code' => 13, 'calculated_result' => 113]),
        ]);

        $this->gradingService->shouldReceive('updateGrade')->with(5, 3, 7, 17, 107)->once();
        $this->gradingService->shouldReceive('updateGrade')->with(5, 3, 11, 17, 111)->once();
        $this->gradingService->shouldReceive('updateGrade')->with(5, 3, 13, 17, 113)->never();

        $this->service->updateGrade($submission);
    }

    public function testConfirmSubmissionUnconfirmsOldSubmissions()
    {
        Submission::unguard();

        $newSubmission = new Submission(['id' => 3, 'user_id' => 5, 'charon_id' => 7]);
        $oldSubmission1 = new Submission(['id' => 11]);
        $oldSubmission2 = new Submission(['id' => 13]);

        $this->submissionsRepository
            ->shouldReceive('findConfirmedSubmissionsForUserAndCharon')
            ->with(5, 7)
            ->once()
            ->andReturn(collect([$newSubmission, $oldSubmission1, $oldSubmission2]));

        $this->submissionsRepository
            ->shouldReceive('unconfirmSubmission')
            ->with($oldSubmission1)
            ->once();

        $this->submissionsRepository
            ->shouldReceive('unconfirmSubmission')
            ->with($oldSubmission2)
            ->once();

        $this->submissionsRepository
            ->shouldReceive('unconfirmSubmission')
            ->with($newSubmission)
            ->never();

        $this->user
            ->shouldReceive('currentUserId')
            ->once()
            ->andReturn(17);

        $this->submissionsRepository
            ->shouldReceive('confirmSubmission')
            ->with($newSubmission, 17)
            ->once();

        $this->service->confirmSubmission($newSubmission);
    }

    public function testGradesShouldBeUpdatedReturnsFalseIfAlreadyConfirmed()
    {
        $submission = new Submission(['user_id' => 3, 'charon_id' => 5]);

        $this->submissionsRepository
            ->shouldReceive('charonHasConfirmedSubmissions')
            ->with(5, 3)
            ->once()
            ->andReturn(true);

        $actual = $this->service->gradesShouldBeUpdated($submission);

        $this->assertFalse($actual);
    }

    public function testGradesShouldBeUpdatedReturnsTrueIfNotConfirmedAndNoPreference()
    {
        /** @var Mock|GradingMethod $gradingMethod */
        $gradingMethod = Mockery::mock(GradingMethod::class);

        $charon = new Charon();
        $charon->gradingMethod = $gradingMethod;

        $submission = new Submission(['user_id' => 3, 'charon_id' => 5]);
        $submission->charon = $charon;

        $this->submissionsRepository
            ->shouldReceive('charonHasConfirmedSubmissions')
            ->andReturn(false);

        $gradingMethod
            ->shouldReceive('isPreferBest')
            ->once()
            ->andReturn(false);

        $this->calculatorService->shouldNotReceive('submissionIsBetterThanLast');

        $actual = $this->service->gradesShouldBeUpdated($submission);

        $this->assertTrue($actual);
    }

    public function testGradesShouldBeUpdatedReturnsTrueIfGradingMethodPrefersBestAndSubmissionIsBetter()
    {
        /** @var Mock|GradingMethod $gradingMethod */
        $gradingMethod = Mockery::mock(GradingMethod::class);

        $charon = new Charon();
        $charon->gradingMethod = $gradingMethod;

        $submission = new Submission(['user_id' => 3, 'charon_id' => 5]);
        $submission->charon = $charon;

        $this->submissionsRepository
            ->shouldReceive('charonHasConfirmedSubmissions')
            ->andReturn(false);

        $gradingMethod
            ->shouldReceive('isPreferBest')
            ->once()
            ->andReturn(true);

        $this->calculatorService
            ->shouldReceive('submissionIsBetterThanLast')
            ->with($submission)
            ->andReturn(true);

        $actual = $this->service->gradesShouldBeUpdated($submission);

        $this->assertTrue($actual);
    }

    public function testGradesShouldBeUpdatedReturnsFalseIfGradingMethodPrefersBestAndSubmissionIsWorse()
    {
        /** @var Mock|GradingMethod $gradingMethod */
        $gradingMethod = Mockery::mock(GradingMethod::class);

        $charon = new Charon();
        $charon->gradingMethod = $gradingMethod;

        $submission = new Submission(['user_id' => 3, 'charon_id' => 5]);
        $submission->charon = $charon;

        $this->submissionsRepository
            ->shouldReceive('charonHasConfirmedSubmissions')
            ->andReturn(false);

        $gradingMethod
            ->shouldReceive('isPreferBest')
            ->once()
            ->andReturn(true);

        $this->calculatorService
            ->shouldReceive('submissionIsBetterThanLast')
            ->with($submission)
            ->andReturn(false);

        $actual = $this->service->gradesShouldBeUpdated($submission);

        $this->assertFalse($actual);
    }

    public function testRecalculateGradesUpdatesGradeWithDeadlineResult()
    {
        $deadlines = collect([new Deadline()]);

        $charon = new Charon(['course' => 7]);
        $charon->deadlines = $deadlines;

        $grademap = new Grademap(['charon_id' => 3, 'grade_type_code' => 5]);
        $grademap->charon = $charon;

        /** @var Mock|Result $result1 */
        $result1 = Mockery::mock(Result::class)->makePartial();
        $result1->grade_type_code = 19;
        $result1->submission = new Submission(['user_id' => 11]);
        $result1->shouldReceive('save')->once();

        /** @var Mock|Result $result2 */
        $result2 = Mockery::mock(Result::class)->makePartial();
        $result2->grade_type_code = 23;
        $result2->submission = new Submission(['user_id' => 11]);
        $result2->shouldReceive('save')->once();

        $this->submissionsRepository
            ->shouldReceive('findResultsByCharonAndGradeType')
            ->with(3, 5)
            ->once()
            ->andReturn(collect([$result1, $result2]));

        $this->submissionsRepository
            ->shouldReceive('charonHasConfirmedSubmissions')
            ->with(3, 11)
            ->twice()
            ->andReturn(false);

        $this->calculatorService
            ->shouldReceive('calculateResultFromDeadlines')
            ->with($result1, $deadlines)
            ->once()
            ->andReturn(13);

        $this->calculatorService
            ->shouldReceive('calculateResultFromDeadlines')
            ->with($result2, $deadlines)
            ->once()
            ->andReturn(17);

        $this->gradingService
            ->shouldReceive('updateGrade')
            ->with(7, 3, 19, 11, 13)
            ->once();

        $this->gradingService
            ->shouldReceive('updateGrade')
            ->with(7, 3, 23, 11, 17)
            ->once();

        $this->service->recalculateGrades($grademap);
    }

    public function testRecalculateGradesUpdatesGradesWithConfirmedSubmissions()
    {
        $grademap = new Grademap(['charon_id' => 3, 'grade_type_code' => 5]);
        $grademap->charon = new Charon(['course' => 7]);

        /** @var Mock|Result $result1 */
        $result1 = Mockery::mock(Result::class)->makePartial();
        $result1->grade_type_code = 19;
        $result1->submission = new Submission(['user_id' => 11]);
        $result1->calculated_result = 13;

        /** @var Mock|Result $result2 */
        $result2 = Mockery::mock(Result::class)->makePartial();
        $result2->grade_type_code = 23;
        $result2->submission = new Submission(['user_id' => 11]);
        $result2->calculated_result = 17;

        $this->submissionsRepository
            ->shouldReceive('findResultsByCharonAndGradeType')
            ->with(3, 5)
            ->once()
            ->andReturn(collect([$result1, $result2]));

        $this->submissionsRepository
            ->shouldReceive('charonHasConfirmedSubmissions')
            ->with(3, 11)
            ->twice()
            ->andReturn(true);

        /** @var Mock|Submission $existingSubmission */
        $existingSubmission = Mockery::mock(Submission::class);
        $existingSubmission->shouldReceive('results')->twice()->andReturn(collect([$result1, $result2]));

        $submissions = collect([$existingSubmission, new Submission()]);

        $this->submissionsRepository
            ->shouldReceive('findConfirmedSubmissionsForUserAndCharon')
            ->with(11, 3)
            ->twice()
            ->andReturn($submissions);

        $this->gradingService
            ->shouldReceive('updateGrade')
            ->with(7, 3, 19, 11, 13)
            ->once();

        $this->gradingService
            ->shouldReceive('updateGrade')
            ->with(7, 3, 23, 11, 17)
            ->once();

        $this->service->recalculateGrades($grademap);
    }

    public function testUpdateProgressByStudentIdIgnoresUpdateIfNoRegistration()
    {
        /** @var Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);

        $this->registrationRepository->shouldReceive('query')->once()->andReturn($builder);

        $builder->shouldReceive('where')->with('student_id', 7)->once()->andReturn($builder);
        $builder->shouldReceive('where')->with('submission_id', 5)->once()->andReturn($builder);
        $builder->shouldReceive('where')->with('charon_id', 3)->once()->andReturn($builder);
        $builder->shouldReceive('select')->with('id')->once()->andReturn($builder);
        $builder->shouldReceive('orderBy')->with('choosen_time', 'desc')->once()->andReturn($builder);
        $builder->shouldReceive('first')->once()->andReturn(null);

        $this->service->updateProgressByStudentId(3, 5, 7, 'Done');

        $this->user->shouldNotHaveReceived('currentUserId');
        $this->registrationRepository->shouldNotHaveReceived('updateRegistration');
    }

    public function testUpdateProgressByStudentIdUpdatesFoundRegistration()
    {
        Registration::unguard();

        /** @var Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);

        $this->registrationRepository->shouldReceive('query')->once()->andReturn($builder);

        $builder->shouldReceive('where')->with('student_id', 7)->once()->andReturn($builder);
        $builder->shouldReceive('where')->with('submission_id', 5)->once()->andReturn($builder);
        $builder->shouldReceive('where')->with('charon_id', 3)->once()->andReturn($builder);
        $builder->shouldReceive('select')->with('id')->once()->andReturn($builder);
        $builder->shouldReceive('orderBy')->with('choosen_time', 'desc')->once()->andReturn($builder);
        $builder->shouldReceive('first')->once()->andReturn(new Registration(['id' => 11]));

        $this->user->shouldReceive('currentUserId')->once()->andReturn(13);
        $this->registrationRepository->shouldReceive('updateRegistration')->with(11, 'Done', 13)->once();

        $this->service->updateProgressByStudentId(3, 5, 7, 'Done');
    }
}
