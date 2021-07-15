<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Exceptions\TemplatePathException;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\TemplatesRepository;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\TemplateService;

class TemplateServiceTest extends TestCase
{
    /** @var TemplateService */
    private $service;

    /** @var Mock|TemplatesRepository */
    private $repository;

    /** @var Mock|Template */
    private $template;

    protected function setUp()
    {
        parent::setUp();

        $this->template = Mockery::mock(Template::class)->makePartial();

        $this->service = new TemplateService(
            $this->repository = Mockery::mock(TemplatesRepository::class)
        );
    }


    /**
     * @throws TemplatePathException
     */
    public function testAddTemplatesWorksFine()
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

        $dbTemplates = array();
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;

        $this->repository->shouldReceive('saveTemplate')
            ->twice()
            ->with(222, 'EX01/Dog.php', 'auh')
            ->andReturn($this->template);
        $this->repository->shouldReceive('saveTemplate')
            ->twice()
            ->with(222, 'EX01/Cat.php', 'meow')
            ->andReturn($this->template);
        $this->repository->shouldReceive('saveTemplate')
            ->twice()
            ->with(222, 'EX01/Zebra.php', '')
            ->andReturn($this->template);

        $this->service->addTemplates($charon->id, $templates, $dbTemplates);

        $testTemplate1 = Mockery::mock(Template::class)->makePartial();
        $testTemplate1->path = 'EX01/Horse.php';
        $testTemplate1->contents = 'code here';

        $testTemplate2 = Mockery::mock(Template::class)->makePartial();
        $testTemplate2->path = 'EX01/Cow.php';
        $testTemplate2->contents = 'code here';

        $dbTemplates = array(
            $testTemplate1, $testTemplate2
        );

        $this->service->addTemplates($charon->id, $templates, $dbTemplates);
    }

    public function testAddTemplatesErrorWhenSamePathInDatabase()
    {
        $this->expectException(TemplatePathException::class);
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

        $testTemplate = Mockery::mock(Template::class)->makePartial();
        $testTemplate->path = 'EX01/Cat.php';
        $testTemplate->contents = 'code here';

        $dbTemplates = array(
            $testTemplate
        );

        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 666;

        $this->repository->shouldReceive('saveTemplate')
            ->never();

        $this->service->addTemplates($charon->id, $templates, $dbTemplates);
    }

    public function testAddTemplatesErrorWhenSamePathAddTwice()
    {
        $this->expectException(TemplatePathException::class);
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
                'path' => 'EX01/Dog.php',
                'contents' => '',
            ),
        );

        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 666;

        $this->repository->shouldReceive('saveTemplate')
            ->never();

        $this->service->addTemplates($charon->id, $templates, array());
    }

    /**
     * @throws TemplatePathException
     */
    public function testUpdateTemplatesWorksFine()
    {
        $templates = array(
            array(
                'path' => 'EX01/Dog.php',
                'contents' => 'auh',
            ),
            array(
                'path' => 'EX01/Zebra.php',
                'contents' => '',
            ),
        );

        $testTemplate1 = Mockery::mock(Template::class)->makePartial();
        $testTemplate1->path = 'EX01/Zebra.php';
        $testTemplate1->contents = '';

        $testTemplate2 = Mockery::mock(Template::class)->makePartial();
        $testTemplate2->path = 'EX01/Dog.php';
        $testTemplate2->contents = 'auh';

        $dbTemplates = array(
            $testTemplate1, $testTemplate2
        );

        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 999;

        $this->repository->shouldReceive('updateTemplateContents')
            ->once()
            ->with($testTemplate1)
            ->andReturn($this->template);
        $this->repository->shouldReceive('updateTemplateContents')
            ->once()
            ->with($testTemplate2)
            ->andReturn($this->template);

        $this->service->updateTemplates($templates, $dbTemplates);

        $testTemplate3 = Mockery::mock(Template::class)->makePartial();
        $testTemplate3->path = 'EX01/Dog.php';
        $testTemplate3->contents = ' public function auh(){\n\n}';

        $testTemplate4 = Mockery::mock(Template::class)->makePartial();
        $testTemplate4->path = 'EX01/Car.php';
        $testTemplate4->contents = 'code';

        $testTemplate5 = Mockery::mock(Template::class)->makePartial();
        $testTemplate5->path = 'EX01/Zebra.php';
        $testTemplate5->contents = 'Good zebra';

        $dbTemplates = array(
            $testTemplate3, $testTemplate4, $testTemplate5
        );

        $this->repository->shouldReceive('updateTemplateContents')
            ->once()
            ->with($testTemplate3)
            ->andReturn($this->template);
        $this->repository->shouldReceive('updateTemplateContents')
            ->never()
            ->with($testTemplate4);
        $this->repository->shouldReceive('updateTemplateContents')
            ->once()
            ->with($testTemplate5)
            ->andReturn($this->template);

        $this->service->updateTemplates($templates, $dbTemplates);

        $testTemplate6 = Mockery::mock(Template::class)->makePartial();
        $testTemplate6->path = 'EX01/Car.php';
        $testTemplate6->contents = 'code';

        $testTemplate7 = Mockery::mock(Template::class)->makePartial();
        $testTemplate7->path = 'EX01/Zebra.php';
        $testTemplate7->contents = 'Good zebra';

        $dbTemplates = array(
            $testTemplate6, $testTemplate7
        );

        $templates = array();

        $this->repository->shouldReceive('updateTemplateContents')
            ->never()
            ->with($testTemplate6);
        $this->repository->shouldReceive('updateTemplateContents')
            ->never()
            ->with($testTemplate7);

        $this->service->updateTemplates($templates, $dbTemplates);
    }

    public function testUpdateTemplatesNoPath()
    {
        $this->expectException(TemplatePathException::class);

        $templates = array(
            array(
                'path' => 'EX01/Zebra.php',
                'contents' => 'auh',
            ),
            array(
                'path' => 'EX01/Cat.php',
                'contents' => 'meow',
            ),
            array(
                'path' => 'EX01/Dog.php',
                'contents' => '',
            ),
        );

        $testTemplate1 = Mockery::mock(Template::class)->makePartial();
        $testTemplate1->path = 'EX01/Zebra.php';
        $testTemplate1->contents = '';

        $testTemplate2 = Mockery::mock(Template::class)->makePartial();
        $testTemplate2->path = 'EX01/Cat.php';
        $testTemplate2->contents = 'auh';

        $dbTemplates = array(
            $testTemplate1, $testTemplate2
        );

        $this->repository->shouldReceive('updateTemplateContents')
            ->never();

        $this->service->updateTemplates($templates, $dbTemplates);
    }
}
