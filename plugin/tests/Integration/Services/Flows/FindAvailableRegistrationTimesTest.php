<?php

namespace Tests\Integration\Services\Flows;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\DefenseRegistration;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\LabGroup;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\Flows\FindAvailableRegistrationTimes;
use Tests\TestCase;
use TTU\Charon\Services\LabService;
use TTU\Charon\Validators\LabValidator;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\Group;
use Zeizig\Moodle\Models\User;

/**
 * TODO: cases which verify an empty array as the response should also include spy checks to verify at which point they
 * exited the flow to result in that empty array. Currently I just used breakpoints to verify their progress manually.
 */
class FindAvailableRegistrationTimesTest extends TestCase
{
    use DatabaseTransactions;

    /** @var LabService */
    private $labService;

    /** @var FindAvailableRegistrationTimes */
    private $flow;

    /** @var User */
    private $teacher;

    /** @var User */
    private $student;

    /** @var Course */
    private $course;

    /** @var Carbon */
    private $start;

    /** @var Carbon */
    private $end;

    protected function setUp()
    {
        parent::setUp();

        $config = new MoodleConfig();
        $config->prefix = 'mdl_';
        $this->app->bind(MoodleConfig::class, function() use ($config) { return $config; });

        $this->flow = $this->app->make(FindAvailableRegistrationTimes::class);

        $this->teacher = factory(User::class)->create(['username' => 'teacher@ttu.ee']);
        $this->student = factory(User::class)->create(['username' => 'student@ttu.ee']);
        $this->course = factory(Course::class)->create();
        $this->start = Carbon::create(2021, 11, 16, 12, 0, 0);
        $this->end = Carbon::create(2021, 11, 20, 12, 30, 0);

        GradeItem::unguard();
    }

    protected function tearDown()
    {
        parent::tearDown();
        GradeItem::reguard();
    }

    /**
     * @throws ValidationException
     */
    public function testFilterRemovesRegisteredSubmissions()
    {
        // Given
        $charon = $this->createCharon();
        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create(['course_id' => $this->course->id]);

        DefenseRegistration::create([
            'charon_id' => $charon->id,
            'student_id' => $this->student->id,
            'teacher_id' => $this->teacher->id,
            'progress' => 'New',
            'lab_id' => $lab->id,
            'time' => $this->start->format('Y-m-d H:i:s')
        ]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    /**
     * @throws ValidationException
     */
    public function testFilterRemovesConfirmedSubmissions()
    {
        // Given
        $charon = $this->createCharon();
        $submission = $this->createSubmission(['charon_id' => $charon->id, 'confirmed' => 1]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    /**
     * @throws ValidationException
     */
    public function testFilterRemovesEarlyCharons()
    {
        // Given
        $charon = $this->createCharon(['defense_start_time' => $this->start->copy()->addDays(1)]);
        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    /**
     * @throws ValidationException
     */
    public function testFilterRemovesLateCharons()
    {
        // Given
        $charon = $this->createCharon(['defense_deadline' => $this->end->copy()->addMinutes(-30)]);
        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    /**
     * @throws ValidationException
     */
    public function testFilterRemovesLowStyleResults()
    {
        // Given
        $charon = $this->createCharon();
        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        factory(Grademap::class)->create([
            'charon_id' => $charon->id,
            'grade_item_id' => GradeItem::create(['grademax' => 1])->id,
            'grade_type_code' => 101
        ]);

        Result::create([
            'submission_id' => $submission->id,
            'user_id' => $this->student->id,
            'grade_type_code' => 101,
            'percentage' => 0.9,
            'calculated_result' => 0.9
        ]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    /**
     * @throws ValidationException
     */
    public function testFilterRemovesLowTestResults()
    {
        // Given
        $charon = $this->createCharon(['defense_threshold' => 50]);
        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        factory(Grademap::class)->create([
            'charon_id' => $charon->id,
            'grade_item_id' => GradeItem::create(['grademax' => 1])->id,
            'grade_type_code' => 1
        ]);

        Result::create([
            'submission_id' => $submission->id,
            'user_id' => $this->student->id,
            'grade_type_code' => 1,
            'percentage' => 0.45,
            'calculated_result' => 0.45
        ]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    public function testValidateThrowsIfSubmissionDoesNotBelongToStudent()
    {
        $charon = $this->createCharon();
        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        try {
            $this->flow->run(
                $this->course->id,
                $this->student->id,
                [$charon->id => $submission->id],
                $this->start,
                $this->end
            );

            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertEquals(
                'Submission ' . $submission->id . ' does not belong to student ' . $this->student->id,
                $exception->validator->getMessageBag()->get('submissions')[0]
            );
        }
    }

    /**
     * @throws ValidationException
     */
    public function testFindLabsRemovesIfNotInGroup()
    {
        // Given
        $charon = $this->createCharon();

        $submission = $this->createSubmission(['charon_id' => $charon->id]);

        $submission->users()->attach($this->student->id);

        /** @var Group $group */
        $group = factory(Group::class)->create(['courseid' => $this->course->id]);

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create([
            'course_id' => $this->course->id,
            'start' => $this->start->copy()->addHours(1),
            'end' => $this->start->copy()->addHours(3),
        ]);

        CharonDefenseLab::create([
            'lab_id' => $lab->id,
            'charon_id' => $charon->id
        ]);

        LabGroup::create([
            'lab_id' => $lab->id,
            'group_id' => $group->id
        ]);

        // When
        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$charon->id => $submission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEmpty($actual);
    }

    /**
     * @throws ValidationException
     */
    public function testMakeChunksGroupsTimeslotsByLabAndTimeRange()
    {
        $labValidator = Mockery::mock(LabValidator::class)->makePartial();
        $labValidator->shouldReceive('duringCourse')->andReturnSelf();
        $labValidator->shouldReceive('withMaxDuration')->andReturnSelf();
        $labValidator->shouldReceive('withCourseCharons')->andReturnSelf();
        $labValidator->shouldReceive('withTeachers')->andReturnSelf();
        $labValidator->shouldReceive('validate');
        $this->app->bind(LabValidator::class, function() use ($labValidator) { return $labValidator; });

        $this->labService = $this->app->make(LabService::class);

        $this->bindTeacherToCourse();

        // Given
        $mathTask = $this->createCharon();
        $mathSubmission = $this->createSubmission(['charon_id' => $mathTask->id]);
        $mathSubmission->users()->attach($this->student->id);

        $bioTask = $this->createCharon();
        $bioSubmission = $this->createSubmission(['charon_id' => $bioTask->id]);
        $bioSubmission->users()->attach($this->student->id);

        // When
        $labs = [
            $this->createLab(
                $this->start->copy()->addHours(2),
                $this->start->copy()->addHours(3),
                30,
                [$mathTask->id, $bioTask->id]
            ),
            $this->createLab(
                $this->start->copy()->addHours(4),
                $this->start->copy()->addHours(6),
                20,
                [$bioTask->id]
            ),
        ];

        $actual = $this->flow->run(
            $this->course->id,
            $this->student->id,
            [$mathTask->id => $mathSubmission->id, $bioTask->id => $bioSubmission->id],
            $this->start,
            $this->end
        );

        // Then
        $this->assertEquals([
            [
                'lab' => $labs[0],
                'start' => $this->start->copy()->addMinutes(120),
                'end' => $this->start->copy()->addMinutes(150),
                'charons' => [$mathTask->id, $bioTask->id],
                'times' => 6
            ],
            [
                'lab' => $labs[0],
                'start' => $this->start->copy()->addMinutes(150),
                'end' => $this->start->copy()->addMinutes(180),
                'charons' => [$mathTask->id, $bioTask->id],
                'times' => 6
            ],
            [
                'lab' => $labs[1],
                'start' => $this->start->copy()->addMinutes(240),
                'end' => $this->start->copy()->addMinutes(260),
                'charons' => [$bioTask->id],
                'times' => 4
            ],
            [
                'lab' => $labs[1],
                'start' => $this->start->copy()->addMinutes(260),
                'end' => $this->start->copy()->addMinutes(280),
                'charons' => [$bioTask->id],
                'times' => 4
            ],
            [
                'lab' => $labs[1],
                'start' => $this->start->copy()->addMinutes(280),
                'end' => $this->start->copy()->addMinutes(300),
                'charons' => [$bioTask->id],
                'times' => 4
            ],
            [
                'lab' => $labs[1],
                'start' => $this->start->copy()->addMinutes(300),
                'end' => $this->start->copy()->addMinutes(320),
                'charons' => [$bioTask->id],
                'times' => 4
            ],
            [
                'lab' => $labs[1],
                'start' => $this->start->copy()->addMinutes(320),
                'end' => $this->start->copy()->addMinutes(340),
                'charons' => [$bioTask->id],
                'times' => 4
            ],
            [
                'lab' => $labs[1],
                'start' => $this->start->copy()->addMinutes(340),
                'end' => $this->start->copy()->addMinutes(360),
                'charons' => [$bioTask->id],
                'times' => 4
            ],
        ], $actual);
    }

    private function createCharon(array $params = []): Charon
    {
        return factory(Charon::class)->create(
            array_merge([
                'course' => $this->course->id,
                'defense_deadline' => null,
                'defense_start_time' => null
            ], $params)
        );
    }

    private function createSubmission(array $params = []): Submission
    {
        return factory(Submission::class)->create(array_merge(['user_id' => $this->student->id], $params));
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @param int $chunkSize
     * @param array $charons
     *
     * @return int
     * @throws ValidationException
     */
    private function createLab(Carbon $start, Carbon $end, int $chunkSize, array $charons): int
    {
        return $this->labService->create(
            factory(Lab::class)->make(
                [
                    'course_id' => $this->course->id,
                    'start' => $start,
                    'end' => $end,
                    'chunk_size' => $chunkSize
                ]
            ),
            $this->course,
            $charons,
            [$this->teacher->id],
            []
        )[0];
    }

    private function bindTeacherToCourse()
    {
        $contextId = DB::table('context')->insertGetId(['instanceid' => $this->course->id]);

        DB::table('role_assignments')->insertGetId([
            'contextid' => $contextId,
            'roleid' => 3,
            'userid' => $this->teacher->id
        ]);
    }
}
