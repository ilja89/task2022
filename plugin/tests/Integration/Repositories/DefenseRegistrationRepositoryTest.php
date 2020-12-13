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

    protected function setUp()
    {
        parent::setUp();
        $this->repository = new DefenseRegistrationRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(LabTeacherRepository::class)
        );
    }

    /**
     * Cases:
     * + 1st & 2nd match time and progress
     * - 3rd matches progress but too early
     * - 4th matches progress but too late
     * - 5th matches time but not progress
     */
    public function testGetUserPendingRegistrationsCount()
    {
        $start = Carbon::now();
        $end = Carbon::now()->addHours(3);

        /** @var User $student */
        $student = factory(User::class)->create();

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create();

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var CharonDefenseLab $defenseLab */
        $defenseLab = CharonDefenseLab::create(['lab_id' => $lab->id, 'charon_id' => $charon->id]);

        $common = [
            'student_id' => $student->id,
            'charon_id' => $charon->id,
            'defense_lab_id' => $defenseLab->id,
        ];

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(10),
            'progress' => 'Waiting'
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(15),
            'progress' => 'Defending'
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->subHours(5),
            'progress' => 'Defending'
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addHours(5),
            'progress' => 'Defending'
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(20),
            'progress' => 'Done'
        ]);

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
        /** @var User $teacher */
        $teacher = factory(User::class)->create();

        /** @var Carbon $time */
        $time = Carbon::parse('2020-12-15 22:10:00');

        factory(Registration::class)->create([
            'teacher_id' => $teacher->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => $teacher->id,
            'choosen_time' => $time->clone()->addMinutes(20),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => $teacher->id,
            'choosen_time' => $time->clone()->addDays(1),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(20),
            'student_id' => factory(User::class)->create()->id
        ]);

        $actual = $this->repository->getChosenTimesForTeacherAt($teacher->id, $time->format('Y-m-d H'));

        $this->assertEquals(['2020-12-15 22:20:00', '2020-12-15 22:30:00'], $actual);
    }

    /**
     * Cases:
     * + 1st & 2nd match time and teacher count
     * - 3rd matches time but not count
     * - 4th & 5th match count but not time
     */
    public function testGetChosenTimesForAllTeachers()
    {
        /** @var Carbon $time */
        $time = Carbon::parse('2020-12-15 22:10:00');

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(10),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addMinutes(20),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addDays(2),
            'student_id' => factory(User::class)->create()->id
        ]);

        factory(Registration::class)->create([
            'teacher_id' => factory(User::class)->create()->id,
            'choosen_time' => $time->clone()->addDays(2),
            'student_id' => factory(User::class)->create()->id
        ]);

        $actual = $this->repository->getChosenTimesForAllTeachers($time->format('Y-m-d H'), 2);

        $this->assertEquals(['2020-12-15 22:20:00'], $actual);
    }
}
