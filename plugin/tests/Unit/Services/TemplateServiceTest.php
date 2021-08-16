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

        $this->service->addTemplates($charon->id, $templates);
    }
}
