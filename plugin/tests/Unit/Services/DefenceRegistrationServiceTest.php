<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
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
            Mockery::mock(LabRepository::class),
            $this->defenseRegistrationRepository = Mockery::mock(DefenseRegistrationRepository::class),
            Mockery::mock(MoodleUser::class),
            $this->userRepository = Mockery::mock(UserRepository::class)
        );
    }
}
