<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Http\Controllers\Api\DefenseRegistrationController;
use Tests\TestCase;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use TTU\Charon\Services\LabService;

class DefenseRegistrationControllerTest extends TestCase
{
    /** @var DefenceRegistrationService|Mock */
    private $registrationService;

    /** @var CharonDefenseLabRepository|Mock */
    private $defenseLabRepository;

    /** @var DefenseRegistrationController  */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new DefenseRegistrationController(
            Mockery::mock(Request::class),
            Mockery::mock(StudentsRepository::class),
            Mockery::mock(DefenseRegistrationRepository::class),
            $this->registrationService = Mockery::mock(DefenceRegistrationService::class),
            Mockery::mock(LabService::class),
            $this->defenseLabRepository = Mockery::mock(CharonDefenseLabRepository::class),
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
            'charon_id' => 7,
            'defense_lab_id' => 13,
        ]);

        $lab = new Lab();
        $lab->id = 19;

        $this->registrationService
            ->shouldReceive('registerDefence')
            ->once()
            ->with(3, 7, 13, 5)
            ->andReturn('inserted');

        $response = $this->controller->registerDefenceByStudent($request);

        $this->assertEquals('inserted', $response);
    }
}
