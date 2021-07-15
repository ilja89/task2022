<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Exceptions\TemplatePathException;
use TTU\Charon\Http\Controllers\Api\TemplatesController;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\TemplatesRepository;
use TTU\Charon\Services\TemplateService;

class TemplateControllerTest extends TestCase
{
    /** @var Mock|Request */
    private $request;

    /** @var TemplatesController */
    private $controller;

    /** @var Mock|TemplateService */
    private $service;

    /** @var Mock|TemplatesRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->controller = new TemplatesController(
            $this->request = Mockery::mock(Request::class),
            $this->service = Mockery::mock(TemplateService::class),
            $this->repository = Mockery::mock(TemplatesRepository::class)
        );
    }

    public function testStoreTemplatesControllerWorksCorrect()
    {
        $templates = array(
            array(
                'path' => 'EX01/Dog.php',
                'contents' => 'auh',
            ),
            array(
                'path' => 'EX01/Cat.php',
                'contents' => 'meow',
            ),
            array(
                'path' => 'EX01/Zebra.php',
                'contents' => '',
            ),
        );
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;
        $this->repository->shouldReceive('getTemplates')
            ->once()
            ->with(222)
            ->andReturn(array());
        $this->service->shouldReceive('addTemplates')
            ->with(222, $templates, array())
            ->once()
            ->andReturn(true);

        $request = new Request($templates);

        $response = $this->controller->store($request, $charon);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Templates saved!', $response->getData()->data->message);
    }

    public function testStoreTemplatesControllerMissingPath()
    {
        $this->expectException(TemplatePathException::class);

        $templates = array(
            array(
                'path' => 'EX01/Dog.php',
                'contents' => 'auh',
            ),
            array(
                'path' => 'rvv r',
                'contents' => 'meow',
            ),
        );
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;
        $this->repository->shouldReceive('getTemplates')
            ->once()
            ->with(222)
            ->andReturn(array());
        $this->service->shouldReceive('addTemplates')
            ->never();
        $request = new Request($templates);
        $this->controller->store($request, $charon);
    }

    public function testStoreTemplatesControllerSpaceInPath()
    {
        $this->expectException(TemplatePathException::class);

        $templates = array(
            array(
                'path' => 'EX01/Dog.php',
                'contents' => 'auh',
            ),
            array(
                'path' => 'EX01/ Cat.php',
                'contents' => 'meow',
            ),
        );
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;
        $this->repository->shouldReceive('getTemplates')
            ->once()
            ->with(222)
            ->andReturn(array());
        $this->service->shouldReceive('addTemplates')
            ->never();
        $request = new Request($templates);
        $this->controller->store($request, $charon);
    }

    public function testUpdateTemplatesControllerWorksCorrect()
    {
        $templates = array(
            array(
                'path' => 'EX01/Dog.php',
                'contents' => 'auh, auh',
            ),
            array(
                'path' => 'EX01/Cat.php',
                'contents' => 'meow, meow',
            ),
            array(
                'path' => 'EX01/Zebra.php',
                'contents' => 'neigh, whinny',
            ),
        );
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 333;
        $this->service->shouldReceive('updateTemplates')
            ->with(333, $templates)
            ->once()
            ->andReturn(true);
        $request = new Request($templates);

        $response = $this->controller->update($request, $charon);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Templates updated!', $response->getData()->data->message);
    }

    public function testDeleteTemplateControllerWorksCorrect()
    {
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 444;
        $template = Mockery::mock(Template::class)->makePartial();
        $template->path = 'EX01/Cat.php';
        $this->repository->shouldReceive('deleteTemplate')
            ->with(444, 'EX01/Cat.php')
            ->once()
            ->andReturn(true);

        $response = $this->controller->delete($charon, $template);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Template deleted!', $response->getData()->data->message);
    }

    public function testGetTemplateControllerWorksCorrect()
    {
        $charon = Mockery::mock(Charon::class)->makePartial();

        $charon->id = 555;

        $this->repository->shouldReceive('getTemplates')
            ->with(555)
            ->once()
            ->andReturn(array());

        $this->controller->get($charon);
    }
}
