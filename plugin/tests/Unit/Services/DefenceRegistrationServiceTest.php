<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use Tests\TestCase;
use Zeizig\Moodle\Globals\User as MoodleUser;
use Zeizig\Moodle\Models\User;

class DefenceRegistrationServiceTest extends TestCase
{
    /** @var LabTeacherRepository|Mock */
    private $teacherRepository;

    /** @var DefenseRegistrationRepository|Mock */
    private $defenseRegistrationRepository;

    /** @var UserRepository|Mock */
    private $userRepository;

    /** @var DefenceRegistrationService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DefenceRegistrationService(
            Mockery::mock(CharonRepository::class),
            $this->teacherRepository = Mockery::mock(LabTeacherRepository::class),
            Mockery::mock(DefenseRegistrationRepository::class),
            Mockery::mock(MoodleUser::class),
            $this->userRepository = Mockery::mock(UserRepository::class)
        );
    }

    /**
     * @throws RegistrationException
     */
    public function testRegisterDefenceTimeThrowsIfNotEnoughSlots()
    {
        $this->markTestSkipped('Out of date, needs attention');
        $this->expectException(RegistrationException::class);

        $this->teacherRepository
            ->shouldReceive('countLabTeachers')
            ->with(17)
            ->once()
            ->andReturn(2);

        $this->defenseRegistrationRepository
            ->shouldReceive('countLabRegistrationsAt')
            ->with(17, equalTo(Carbon::parse('2020-12-15 22:20:00')))
            ->once()
            ->andReturn(2);

        $this->service->registerDefenceTime(3, 5, false, 7, '2020-12-15 22:20:00', 11, 17, 13);
    }

    /**
     * @throws RegistrationException
     */
    public function testRegisterDefenceTimeThrowsIfDuplicate()
    {
        $this->markTestSkipped('Out of date, needs attention');
        User::unguard();
        $this->expectException(RegistrationException::class);

        $this->teacherRepository
            ->shouldReceive('countLabTeachers')
            ->with(17)
            ->once()
            ->andReturn(2);

        $this->defenseRegistrationRepository
            ->shouldReceive('countLabRegistrationsAt')
            ->with(17, equalTo(Carbon::parse('2020-12-15 22:20:00')))
            ->once()
            ->andReturn(1);

        $this->userRepository
            ->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn(new User(['firstname' => '1name', 'lastname' => '2name']));

        /** @var QueryException|Mock $exception */
        $exception = Mockery::mock(QueryException::class);
        $exception->errorInfo = [1 => 1062];

        $this->defenseRegistrationRepository
            ->shouldReceive('create')
            ->with([
                'student_name' => '1name 2name',
                'submission_id' => 5,
                'choosen_time' => '2020-12-15 22:20:00',
                'my_teacher' => false,
                'student_id' => 3,
                'defense_lab_id' => 13,
                'progress' => 'Waiting',
                'charon_id' => 7,
                'teacher_id' => 11
            ])
            ->once()
            ->andThrowExceptions([$exception]);

        $this->service->registerDefenceTime(3, 5, false, 7, '2020-12-15 22:20:00', 11, 17, 13);
    }
}
