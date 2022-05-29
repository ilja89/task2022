<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Exceptions\TemplatePathException;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\TemplatesRepository;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\TemplateService;
use Zeizig\Moodle\Models\Course;

class TemplateServiceTest extends TestCase
{
    /** @var TemplateService */
    private $service;

    /** @var Mock|TemplatesRepository */
    private $repository;

    /** @var Mock|Template */
    private $template;

    protected function setUp(): void
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
                'templateContents' => 'auh',
            ),
            array(
                'path' => 'EX01/Cat.php',
                'templateContents' => 'meow',
            ),
            array(
                'path' => 'EX01/Zebra.php',
                'templateContents' => '',
            ),
        );

        $dbTemplates = array();
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;

        $this->repository->shouldReceive('saveTemplate')
            ->once()
            ->with(222, 'EX01/Dog.php', 'auh')
            ->andReturn($this->template);
        $this->repository->shouldReceive('saveTemplate')
            ->once()
            ->with(222, 'EX01/Cat.php', 'meow')
            ->andReturn($this->template);
        $this->repository->shouldReceive('saveTemplate')
            ->once()
            ->with(222, 'EX01/Zebra.php', '')
            ->andReturn($this->template);

        $this->service->addTemplates($charon->id, $templates);
    }

    public function testAddTemplatesErrorWhenSamePathAddTwice()
    {
        $this->expectException(TemplatePathException::class);
        $templates = array(
            array(
                'path' => 'EX01/Dog.php',
                'templateContents' => 'auh',
            ),
            array(
                'path' => 'EX01/Cat.php',
                'templateContents' => 'meow',
            ),
            array(
                'path' => 'EX01/Dog.php',
                'templateContents' => '',
            ),
        );

        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 666;

        $this->repository->shouldReceive('saveTemplate')
            ->never();

        $this->service->addTemplates($charon->id, $templates);
    }

    public function testUpdateTemplatesSuccessful()
    {
        $template1 = array(
            'path' => 'EX01/Dog.php',
            'templateContents' => 'auh'
        );
        $template2 = array(
            'path' => 'EX01/Cat.php',
            'templateContents' => 'meow'
        );
        $templates = array(
            $template1,
            $template2
        );

        $this->repository->shouldReceive('deleteAllTemplates')->with(1)->once();
        $this->repository->shouldReceive('saveTemplate')->with(1, $template1['path'], $template1['templateContents'])->once();
        $this->repository->shouldReceive('saveTemplate')->with(1, $template2['path'], $template2['templateContents'])->once();

        $this->service->updateTemplates(1, $templates);
    }

    public function testGetTemplates()
    {
        $course = new Course();
        $course->id = 999;
        $course->shortname = 'iti-000000';

        $charon = new Charon();
        $charon->id = 999;
        $charon->course = $course->id;
        $charon->name = 'ex01';
        $charon->project_folder = 'folder';
        $charon->plagiarism_assignment_id = 1;

        $template = new Template();
        $template->charon_id = $charon->id;
        $template->path = 'file.py';
        $template->contents = 'template';
        $template->created_at = Carbon::now();

        $this->repository
            ->shouldReceive('getTemplates')
            ->with($charon->id)
            ->once()
            ->andReturn([$template]);

        $this->service->getTemplates($charon->id);
    }
}
