<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Http\Controllers\Api\TemplatesController;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\TemplatesRepository;

class TemplateControllerTest extends TestCase
{
    /** @var Mock|Request */
    private $request;

    /** @var TemplatesController */
    private $controller;

    /** @var Mock|TemplatesRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TemplatesController(
            $this->request = Mockery::mock(Request::class),
            $this->repository = Mockery::mock(TemplatesRepository::class)
        );
    }

    public function testTemplatesGettingAsksTemplatesFromDatabase()
    {

        $this->repository->shouldReceive('getTemplates')
            ->once()
            ->with(3020)
            ->andReturn(array());

        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 3020;

        $this->controller->get($charon);
    }
}
