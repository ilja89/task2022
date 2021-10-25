<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\LabService;
use Zeizig\Moodle\Models\User;

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
            $this->labRepository = Mockery::mock(LabRepository::class),
            Mockery::mock(CharonRepository::class)
        );
    }

    public function testFindUpcomingOrActiveLabsByCharon()
    {
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;

        $lab1 = Mockery::mock(Lab::class)->makePartial();
        $lab1->id = 1;
        $lab2 = Mockery::mock(Lab::class)->makePartial();
        $lab2->id = 2;
        $lab4 = Mockery::mock(Lab::class)->makePartial();
        $lab4->id = 3;

        $labs = array($lab1, $lab2, $lab4);

        $this->labRepository->shouldReceive('getLabsByCharonId')
            ->once()
            ->with(222)
            ->andReturn($labs);

        foreach ($labs as $lab){
            $this->defenseRegistrationRepository->shouldReceive('countDefendersByLab')
                ->with($lab->id)
                ->once()
                ->andReturn($lab->id);
        }

        $result = $this->service->findUpcomingOrActiveLabsByCharon($charon->id);

        $this->assertEquals(3, count($result));

        foreach ($result as $key => $lab){
            $this->assertEquals($key + 1, $lab->defenders_num); // as defense number is lab id + 1
        }
    }

    public function testLabQueueStatus()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->firstname = 'Tom';
        $user->lastname = 'Jackson';
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 2;

        $this->lab->id = 401;
        $this->lab->start = new \DateTime('2033-07-14 21:30:00');

        $reg1 = new \stdClass();
        $reg1->charon_name = 'EX01';
        $reg1->charon_length = 5;
        $reg1->student_id = 100;
        $reg2 = new \stdClass();
        $reg2->charon_name = 'EX02';
        $reg2->charon_length = 5;
        $reg2->student_id = 1;
        $reg3 = new \stdClass();
        $reg3->charon_name = 'EX01';
        $reg3->charon_length = 5;
        $reg3->student_id = 102;
        $reg4 = new \stdClass();
        $reg4->charon_name = 'EX06';
        $reg4->charon_length = 5;
        $reg4->student_id = 102;
        $reg5 = new \stdClass();
        $reg5->charon_name = 'EX06';
        $reg5->charon_length = 5;
        $reg5->student_id = 102;
        $reg6 = new \stdClass();
        $reg6->charon_name = 'EX08';
        $reg6->charon_length = 5;
        $reg6->student_id = 102;
        $reg7 = new \stdClass();
        $reg7->charon_name = 'EX01';
        $reg7->charon_length = 5;
        $reg7->student_id = 1;

        $registrations = array($reg1, $reg2, $reg3, $reg4, $reg5, $reg6, $reg7);

        $this->defenseRegistrationRepository->shouldReceive('getListOfLabRegistrationsByLabId')
            ->once()
            ->with(401)
            ->andReturn($registrations);

        $this->labTeacherRepository->shouldReceive('countLabTeachers')
            ->once()
            ->with(401)
            ->andReturn(3); // this is a number of teachers for lab

        $result = $this->service->labQueueStatus($user, $this->lab);

        $expectedReg1 = new \stdClass();
        $expectedReg1->charon_name = 'EX01';
        $expectedReg1->student_name = '';
        $expectedReg1->queue_pos = 1;
        $expectedReg1->approx_start_time = '14.07.2033 21:30';
        $expectedReg2 = new \stdClass();
        $expectedReg2->charon_name = 'EX02';
        $expectedReg2->student_name = 'Tom Jackson';
        $expectedReg2->queue_pos = 2;
        $expectedReg2->approx_start_time = '14.07.2033 21:30';
        $expectedReg3 = new \stdClass();
        $expectedReg3->charon_name = 'EX01';
        $expectedReg3->student_name = '';
        $expectedReg3->queue_pos = 3;
        $expectedReg3->approx_start_time = '14.07.2033 21:30';
        $expectedReg4 = new \stdClass();
        $expectedReg4->charon_name = 'EX06';
        $expectedReg4->student_name = '';
        $expectedReg4->queue_pos = 4;
        $expectedReg4->approx_start_time = '14.07.2033 21:35';
        $expectedReg5 = new \stdClass();
        $expectedReg5->charon_name = 'EX06';
        $expectedReg5->student_name = '';
        $expectedReg5->queue_pos = 5;
        $expectedReg5->approx_start_time = '14.07.2033 21:35';
        $expectedReg6 = new \stdClass();
        $expectedReg6->charon_name = 'EX08';
        $expectedReg6->student_name = '';
        $expectedReg6->queue_pos = 6;
        $expectedReg6->approx_start_time = '14.07.2033 21:35';
        $expectedReg7 = new \stdClass();
        $expectedReg7->charon_name = 'EX01';
        $expectedReg7->student_name = 'Tom Jackson';
        $expectedReg7->queue_pos = 7;
        $expectedReg7->approx_start_time = '14.07.2033 21:40';

        $expectedResult = array($expectedReg1, $expectedReg2, $expectedReg3, $expectedReg4, $expectedReg5, $expectedReg6, $expectedReg7);

        $this->assertEquals($expectedResult, $result);
    }
}
