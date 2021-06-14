<?php

namespace Tests\Unit\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Http\Controllers\Api\DefenseRegistrationController;
use Tests\TestCase;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use TTU\Charon\Services\Flows\BookStudentRegistration;
use TTU\Charon\Services\Flows\FindAvailableRegistrationTimes;

class DefenseRegistrationControllerTest extends TestCase
{
    /** @var DefenceRegistrationService|Mock */
    private $registrationService;

    /** @var CharonDefenseLabRepository|Mock */
    private $defenseLabRepository;

    /** @var DefenseRegistrationController  */
    private $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->controller = new DefenseRegistrationController(
            Mockery::mock(Request::class),
            Mockery::mock(CharonRepository::class),
            Mockery::mock(StudentsRepository::class),
            Mockery::mock(DefenseRegistrationRepository::class),
            $this->registrationService = Mockery::mock(DefenceRegistrationService::class),
            $this->defenseLabRepository = Mockery::mock(CharonDefenseLabRepository::class),
            Mockery::mock(FindAvailableRegistrationTimes::class),
            Mockery::mock(BookStudentRegistration::class)
        );
    }

    /**
     * @throws RegistrationException
     */
    public function testStudentRegisterDefenceDelegates()
    {
        $request = new Request([
            'user_id' => 3,
            'submission_id' => 5,
            'selected' => 0,
            'charon_id' => 7,
            'student_chosen_time' => '2020-12-15 22:20:00',
            'defense_lab_id' => 13
        ]);

        $lab = new Lab();
        $lab->id = 19;

        $this->defenseLabRepository
            ->shouldReceive('getLabByDefenseLabId')
            ->once()
            ->with(13)
            ->andReturn($lab);

        $this->registrationService
            ->shouldReceive('validateDefence')
            ->once()
            ->with(3, 7, '2020-12-15 22:20:00', $lab);

        $this->registrationService
            ->shouldReceive('getTeacherId')
            ->once()
            ->with(3, false, 19, 7, equalTo(Carbon::parse('2020-12-15 22:20:00')))
            ->andReturn(17);

        $this->registrationService
            ->shouldReceive('registerDefenceTime')
            ->once()
            ->with(3, 5, false, 7, '2020-12-15 22:20:00', 17, 19, 13);

        $response = $this->controller->studentRegisterDefence($request);

        $this->assertEquals('inserted', $response);
    }

    public function testGetUsedDefenceTimesDelegates()
    {
        $request = new Request([
            'time' => 3,
            'charon_id' => 5,
            'lab_id' => 7,
            'user_id' => 11,
            'my_teacher' => 'false'
        ]);

        $lab = new Lab();
        $lab->id = 13;

        $this->defenseLabRepository
            ->shouldReceive('getLabByDefenseLabId')
            ->with(7)
            ->once()
            ->andReturn($lab);

        $this->registrationService
            ->shouldReceive('getUsedDefenceTimes')
            ->once()
            ->with(3, 5, $lab, 11, false)
            ->andReturn(['12:00']);

        $response = $this->controller->getUsedDefenceTimes($request);

        $this->assertEquals(['12:00'], $response);
    }
}
