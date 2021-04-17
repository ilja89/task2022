<?php

namespace Tests\Integration\Services\Flows;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Registration;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\Flows\TeacherModifiesSubmission;
use Tests\TestCase;
use Zeizig\Moodle\Globals\User as MoodleUser;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradingService;

class TeacherModifiesSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GradingService */
    private $gradingService;

    /** @var Mock|MoodleUser */
    private $user;

    /** @var TeacherModifiesSubmission */
    private $flow;

    protected function setUp()
    {
        parent::setUp();

        $this->user = Mockery::mock(MoodleUser::class)->makePartial();
        $this->gradingService = Mockery::mock(GradingService::class)->makePartial();

        $this->app->bind(MoodleUser::class, function() { return $this->user; });
        $this->app->bind(GradingService::class, function() { return $this->gradingService; });

        $this->flow = $this->app->make(TeacherModifiesSubmission::class);
    }

    public function testRun()
    {
        $this->markTestSkipped('Out of date, needs attention');

        /** @var User $teacher */
        $teacher = factory(User::class)->create();

        /** @var User $mainStudent */
        $mainStudent = factory(User::class)->create();

        /** @var User $coStudent */
        $coStudent = factory(User::class)->create();

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create();

        /** @var Submission $submission */
        $submission = factory(Submission::class)->create(['charon_id' => $charon->id, 'user_id' => $mainStudent->id]);
        $submission->users()->saveMany([$mainStudent, $coStudent]);

        /** @var Submission $oldSubmission */
        $oldSubmission = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'confirmed' => 1,
            'user_id' => $mainStudent->id
        ]);
        $oldSubmission->users()->saveMany([$mainStudent, $coStudent]);

        $testResult = Result::create([
            'submission_id' => $submission->id,
            'grade_type_code' => 1,
            'percentage' => 0.10,
            'calculated_result' => 0.10,
        ]);

        $styleResult = Result::create([
            'submission_id' => $submission->id,
            'grade_type_code' => 101,
            'percentage' => 0,
            'calculated_result' => 0,
        ]);

        /** @var Registration $registration */
        $registration = factory(Registration::class)->create([
            'charon_id' => $charon->id,
            'submission_id' => $submission->id,
            'student_id' => $mainStudent->id,
            'progress' => 'Waiting'
        ]);

        factory(Grademap::class)->create(['charon_id' => $charon->id, 'grade_type_code' => 1]);
        factory(Grademap::class)->create(['charon_id' => $charon->id, 'grade_type_code' => 101]);

        $this->user->shouldReceive('currentUserId')->andReturn($teacher->id);
        $this->gradingService->shouldReceive('updateGrade')->once()->with(0, $charon->id, 1, $mainStudent->id, 0.9);
        $this->gradingService->shouldReceive('updateGrade')->once()->with(0, $charon->id, 101, $mainStudent->id, 1.0);
        $this->gradingService->shouldReceive('updateGrade')->once()->with(0, $charon->id, 1, $coStudent->id, 0.9);
        $this->gradingService->shouldReceive('updateGrade')->once()->with(0, $charon->id, 101, $coStudent->id, 1.0);

        $this->flow->run($submission, [
            ['id' => $testResult->id, 'calculated_result' => 0.9],
            ['id' => $styleResult->id, 'calculated_result' => 1.0],
        ]);

        $testResult->refresh();
        $this->assertEquals(0.9, $testResult->calculated_result);

        $styleResult->refresh();
        $this->assertEquals(1.0, $styleResult->calculated_result);

        $oldSubmission->refresh();
        $this->assertEquals(0, $oldSubmission->confirmed);

        $registration->refresh();
        $this->assertEquals(1, $submission->confirmed);
        $this->assertEquals($teacher->id, $submission->grader_id);

        $registration->refresh();
        $this->assertEquals('Done', $registration->progress);
        $this->assertEquals($teacher->id, $registration->teacher_id);
    }
}
