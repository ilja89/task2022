<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Exceptions\TemplatePathException;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\LabService;

class LabServiceTest extends TestCase
{
    /** @var LabService */
    private $service;

    /** @var Mock|LabRepository */
    private $labRepository;

    /** @var Mock|DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var Mock|LabTeacherRepository */
    private $labTeacherRepository;

    /** @var Mock|Lab */
    private $lab;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lab = Mockery::mock(Lab::class)->makePartial();

        $this->service = new LabService(
            $this->defenseRegistrationRepository = Mockery::mock(DefenseRegistrationRepository::class),
            $this->labTeacherRepository = Mockery::mock(LabTeacherRepository::class),
            $this->labRepository = Mockery::mock(LabRepository::class)
        );
    }


    /**
     * @test
     */
    public function findUpcomingOrActiveLabsByCharonTest()
    {
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;

        $lab1 = Mockery::mock(Lab::class)->makePartial();
        $lab1->id = 1;
        $lab2 = Mockery::mock(Lab::class)->makePartial();
        $lab2->id = 2;
        $lab4 = Mockery::mock(Lab::class)->makePartial();
        $lab4->id = 4;

        $labs = array($lab1, $lab2, $lab4);

        $this->labRepository->shouldReceive('getLabsWithStartAndEndTimes')
            ->once()
            ->with(222)
            ->andReturn($labs);

        foreach ($labs as $lab){
            $this->defenseRegistrationRepository->shouldReceive('countDefendersByLab')
                ->with($lab->id)
                ->once();
        }

        $this->service->findUpcomingOrActiveLabsByCharon($charon->id);
    }

//    public function testAddTemplatesErrorWhenSamePathAddTwice()
//    {
//        $this->expectException(TemplatePathException::class);
//        $templates = array(
//            array(
//                'path' => 'EX01/Dog.php',
//                'templateContents' => 'auh',
//            ),
//            array(
//                'path' => 'EX01/Cat.php',
//                'templateContents' => 'meow',
//            ),
//            array(
//                'path' => 'EX01/Dog.php',
//                'templateContents' => '',
//            ),
//        );
//
//        $charon = Mockery::mock(Charon::class)->makePartial();
//        $charon->id = 666;
//
//        $this->repository->shouldReceive('saveTemplate')
//            ->never();
//
//        $this->service->addTemplates($charon->id, $templates);
//    }
}
