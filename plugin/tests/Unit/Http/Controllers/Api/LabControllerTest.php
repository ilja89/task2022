<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Http\Controllers\Api\LabController;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\LabService;
use Zeizig\Moodle\Models\User;

class LabControllerTest extends TestCase
{
    /** @var Mock|Request */
    private $request;

    /** @var LabController */
    private $controller;

    /** @var Mock|LabRepository */
    private $repository;

    /** @var Mock|UserRepository */
    private $userRepository;

    /** @var Mock|LabService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new LabController(
            $this->request = Mockery::mock(Request::class),
            $this->repository = Mockery::mock(LabRepository::class),
            $this->service = Mockery::mock(LabService::class)
        );
    }

    public function testTemplatesGettingAsksTemplatesFromDatabase()
    {
        $this->markTestSkipped('Not working. $user assert needs fix. DB access needed.');

        global $USER;
        $user = factory(User::class)->create();
        $USER = $user;

        $defenseLab = Mockery::mock(CharonDefenseLab::class)->makePartial();
        $defenseLab->lab = Mockery::mock(Lab::class)->makePartial();

        $this->service->shouldReceive('labQueueStatus')
            ->once()
            ->andReturn(array());

        $charon = Mockery::mock(Charon::class)->makePartial();

        $this->controller->getLabQueueStatus($charon, $defenseLab);
    }
}
