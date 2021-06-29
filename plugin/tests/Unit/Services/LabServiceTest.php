<?php

namespace Services;

use Mockery;
use Mockery\Mock;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use TTU\Charon\Services\LabService;
use PHPUnit\Framework\TestCase;
use Zeizig\Moodle\Globals\User as MoodleUser;

class LabServiceTest extends TestCase
{
    /** @var LabTeacherRepository|Mock */
    private $teacherRepository;

    /** @var DefenseRegistrationRepository|Mock */
    private $defenseRegistrationRepository;

    /** @var UserRepository|Mock */
    private $userRepository;

    /** @var DefenceRegistrationService */
    private $service;

    protected function setUp()
    {
        echo "setup started";
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

    /**
     */
    public function testRescheduleRegistrationsIfEmptyGapsAppear()
    {

    }
}
