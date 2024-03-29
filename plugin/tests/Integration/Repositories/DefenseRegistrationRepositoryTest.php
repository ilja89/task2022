<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Registration;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use Tests\TestCase;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\ModuleService;

class DefenseRegistrationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var DefenseRegistrationRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DefenseRegistrationRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(LabTeacherRepository::class)
        );
    }

    /**
     * Cases:
     * - 1st matches time but not progress and still queues
     * + 2nd & 3rd match time and progress
     * - (4th matches progress but queue does not have enough capacity) - commented out
     */
    public function testGetUserPendingRegistrationsCount()
    {
        $start = Carbon::now();
        $end = Carbon::now()->addMinutes(50);

        /** @var User $student */
        $student = factory(User::class)->create();

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create(['start' => $start, 'end' => $end]);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0, 'defense_duration' => 15]);

        /** @var CharonDefenseLab $defenseLab */
        $defenseLab = CharonDefenseLab::create(['lab_id' => $lab->id, 'charon_id' => $charon->id]);

        $common = [
            'student_id' => $student->id,
            'charon_id' => $charon->id,
            'defense_lab_id' => $defenseLab->id,
        ];

        factory(Registration::class)->create($common + [
            'progress' => 'Done'
        ]);

        factory(Registration::class)->create($common + [
            'progress' => 'Waiting'
        ]);

        factory(Registration::class)->create($common + [
            'progress' => 'Defending'
        ]);

        /*
        factory(Registration::class)->create($common + [
            'progress' => 'Defending'
        ]);
        */

        $actual = $this->repository->getUserPendingRegistrationsCount($student->id, $charon->id, $start, $end);

        $this->assertEquals(2, $actual);
    }

    /**
     * Cases:
     * + 1st & 2nd match time and teacher
     * - 3rd matches teacher but not time
     * - 4th matches time but not teacher
     */
    public function testGetChosenTimesForTeacherAt()
    {
        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0, 'defense_duration' => 20]);

        /** @var User $teacher */
        $teacher = factory(User::class)->create();

        /** @var Carbon $time */
        $time = Carbon::parse('2020-12-15 22:10:00');

        factory(Registration::class)->create([
            'teacher_id' => $teacher->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => $teacher->id,
            'choosen_time' => $time->clone()->addMinutes(20),
            'student_id' => factory(User::class)->create()->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => $teacher->id,
            'choosen_time' => $time->clone()->addDays(1),
            'student_id' => factory(User::class)->create()->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(20),
            'student_id' => factory(User::class)->create()->id,
            'charon_id' => $charon->id
        ]);

        $actual = $this->repository->getChosenTimesForTeacherAt($teacher->id, $time->format('Y-m-d H'));

        $this->assertEquals(2, sizeof($actual));
        $this->assertEquals('2020-12-15 22:20:00', $actual[0]->choosen_time);
        $this->assertEquals('2020-12-15 22:30:00', $actual[1]->choosen_time);
    }

    /**
     * Cases:
     * + 1st & 2nd match time and teacher count
     * - 3rd does not match defense lab id
     * - 4th & 5th match count but not time
     */
    public function testGetChosenTimesForAllTeachers()
    {
        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0, 'defense_duration' => 20]);

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create();

        $defenseLab = CharonDefenseLab::create(['charon_id' => $charon->id, 'lab_id' => $lab->id]);

        /** @var Carbon $time */
        $time = Carbon::parse('2020-12-15 22:10:00');

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id,
            'defense_lab_id' => $defenseLab->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id,
            'defense_lab_id' => $defenseLab->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addDays(2),
            'student_id' => factory(User::class)->create()->id,
            'defense_lab_id' => $defenseLab->id,
            'charon_id' => $charon->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addDays(2),
            'student_id' => factory(User::class)->create()->id,
            'defense_lab_id' => $defenseLab->id,
            'charon_id' => $charon->id
        ]);

        $actual = $this->repository->getChosenTimesForLabTeachers($time->format('Y-m-d H'), $lab->id);

        $this->assertEquals(2, sizeof($actual));
        $this->assertEquals('2020-12-15 22:20:00', $actual[0]->choosen_time);
        $this->assertEquals('2020-12-15 22:20:00', $actual[1]->choosen_time);
    }
}
