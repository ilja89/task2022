<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\Lab;
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
            $this->labRepository = Mockery::mock(LabRepository::class)
        );
    }

    public function testFindUpcomingOrActiveLabsByCharon()
    {
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;

        $lab1 = Mockery::mock(Lab::class)->makePartial();
        $lab1->defense_lab_id = 1;
        $lab2 = Mockery::mock(Lab::class)->makePartial();
        $lab2->defense_lab_id = 2;
        $lab4 = Mockery::mock(Lab::class)->makePartial();
        $lab4->defense_lab_id = 3;

        $labs = array($lab1, $lab2, $lab4);

        $this->labRepository->shouldReceive('getLabsWithStartAndEndTimes')
            ->once()
            ->with(222)
            ->andReturn($labs);

        foreach ($labs as $lab){
            $this->defenseRegistrationRepository->shouldReceive('countDefendersByLab')
                ->with($lab->defense_lab_id)
                ->once()
                ->andReturn($lab->defense_lab_id);
        }

        $result = $this->service->findUpcomingOrActiveLabsByCharon($charon->id);

        $this->assertEquals(3, count($result));

        foreach ($result as $key => $lab){
            $this->assertEquals($key + 1, $lab->defenders_num); // as defense number is lab id + 1
        }
    }

    public function testStudentsQueueLabNotStarted()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->firstname = 'Tom';
        $user->lastname = 'Jackson';
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 2;

        $this->lab->id = 401;
        $this->lab->start = new \DateTime('+1 day');

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

        $teacher1 = new \stdClass();
        $teacher1->firstname = 'Mari';
        $teacher1->lastname = 'Mägi';
        $teacher1->id = 100;
        $teacher2 = new \stdClass();
        $teacher2->firstname = 'Karl';
        $teacher2->lastname = 'Kivi';
        $teacher2->id = 101;
        $teacher3 = new \stdClass();
        $teacher3->firstname = 'Anna';
        $teacher3->lastname = 'Aluoja';
        $teacher3->id = 102;

        $teachers = array($teacher1, $teacher2, $teacher3);

        $this->defenseRegistrationRepository->shouldReceive('getLabRegistrationsByLabId')
            ->once()
            ->with(401)
            ->andReturn($registrations);
        $this->labTeacherRepository->shouldReceive('getAllLabTeachersByLab')
            ->once()
            ->with(401)
            ->andReturn($teachers);
        $this->defenseRegistrationRepository->shouldReceive('getTeacherAndDefendingCharonByLab')
            ->once()
            ->with(401)
            ->andReturn([]);

        $result = $this->service->labQueueStatus($user, $this->lab);

        $expectedReg1 = new \stdClass();
        $expectedReg1->charon_name = 'EX01';
        $expectedReg1->student_name = '';
        $expectedReg1->queue_pos = 1;
        $expectedReg1->approx_start_time = $this->lab->start->format('d.m.Y H:i');
        $expectedReg2 = new \stdClass();
        $expectedReg2->charon_name = 'EX02';
        $expectedReg2->student_name = 'Tom Jackson';
        $expectedReg2->queue_pos = 2;
        $expectedReg2->approx_start_time = $this->lab->start->format('d.m.Y H:i');
        $expectedReg3 = new \stdClass();
        $expectedReg3->charon_name = 'EX01';
        $expectedReg3->student_name = '';
        $expectedReg3->queue_pos = 3;
        $expectedReg3->approx_start_time = $this->lab->start->format('d.m.Y H:i');

        // add 5 minutes to time, as next registrations are 5 minutes later
        $labStartPlusFive = $this->lab->start->modify('+5 minutes');

        $expectedReg4 = new \stdClass();
        $expectedReg4->charon_name = 'EX06';
        $expectedReg4->student_name = '';
        $expectedReg4->queue_pos = 4;
        $expectedReg4->approx_start_time = $labStartPlusFive->format('d.m.Y H:i');
        $expectedReg5 = new \stdClass();
        $expectedReg5->charon_name = 'EX06';
        $expectedReg5->student_name = '';
        $expectedReg5->queue_pos = 5;
        $expectedReg5->approx_start_time = $labStartPlusFive->format('d.m.Y H:i');
        $expectedReg6 = new \stdClass();
        $expectedReg6->charon_name = 'EX08';
        $expectedReg6->student_name = '';
        $expectedReg6->queue_pos = 6;
        $expectedReg6->approx_start_time = $labStartPlusFive->format('d.m.Y H:i');

        // add 5 minutes to time, as next registration is 5 minutes later
        $labStartPlusFive = $labStartPlusFive->modify('+5 minutes');

        $expectedReg7 = new \stdClass();
        $expectedReg7->charon_name = 'EX01';
        $expectedReg7->student_name = 'Tom Jackson';
        $expectedReg7->queue_pos = 7;
        $expectedReg7->approx_start_time = $labStartPlusFive->format('d.m.Y H:i');

        $expectedResult = array($expectedReg1, $expectedReg2, $expectedReg3, $expectedReg4, $expectedReg5, $expectedReg6, $expectedReg7);

        $this->assertEquals($expectedResult, $result['registrations']);
    }

    /**
     * Here lab started, so students queue estimated time is shown from now.
     */
    public function testStudentsQueueLabStarted()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->firstname = 'Tom';
        $user->lastname = 'Jackson';
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 2;

        $this->lab->id = 401;
        $this->lab->start = new \DateTime('-30 minutes');

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

        $registrations = array($reg1, $reg2, $reg3, $reg4, $reg5);

        $teacher1 = new \stdClass();
        $teacher1->firstname = 'Mari';
        $teacher1->lastname = 'Mägi';
        $teacher1->id = 100;
        $teacher2 = new \stdClass();
        $teacher2->firstname = 'Karl';
        $teacher2->lastname = 'Kivi';
        $teacher2->id = 101;

        $teachers = array($teacher1, $teacher2);

        $this->defenseRegistrationRepository->shouldReceive('getLabRegistrationsByLabId')
            ->once()
            ->with(401, true)
            ->andReturn($registrations);
        $this->labTeacherRepository->shouldReceive('getAllLabTeachersByLab')
            ->once()
            ->with(401)
            ->andReturn($teachers);
        $this->defenseRegistrationRepository->shouldReceive('getTeacherAndDefendingCharonByLab')
            ->once()
            ->with(401)
            ->andReturn([]);

        $result = $this->service->labQueueStatus($user, $this->lab);
        $queueTime = new \DateTime();

        $expectedReg1 = new \stdClass();
        $expectedReg1->charon_name = 'EX01';
        $expectedReg1->student_name = '';
        $expectedReg1->queue_pos = 1;
        $expectedReg1->approx_start_time = $queueTime->format('d.m.Y H:i');
        $expectedReg2 = new \stdClass();
        $expectedReg2->charon_name = 'EX02';
        $expectedReg2->student_name = 'Tom Jackson';
        $expectedReg2->queue_pos = 2;
        $expectedReg2->approx_start_time = $queueTime->format('d.m.Y H:i');

        // add 5 minutes to time, as next registrations are 5 minutes later
        $queueTime = $queueTime->modify('+5 minutes');

        $expectedReg3 = new \stdClass();
        $expectedReg3->charon_name = 'EX01';
        $expectedReg3->student_name = '';
        $expectedReg3->queue_pos = 3;
        $expectedReg3->approx_start_time = $queueTime->format('d.m.Y H:i');
        $expectedReg4 = new \stdClass();
        $expectedReg4->charon_name = 'EX06';
        $expectedReg4->student_name = '';
        $expectedReg4->queue_pos = 4;
        $expectedReg4->approx_start_time = $queueTime->format('d.m.Y H:i');

        // add 5 minutes to time, as next registration is 5 minutes later
        $queueTime = $queueTime->modify('+5 minutes');

        $expectedReg5 = new \stdClass();
        $expectedReg5->charon_name = 'EX06';
        $expectedReg5->student_name = '';
        $expectedReg5->queue_pos = 5;
        $expectedReg5->approx_start_time = $queueTime->format('d.m.Y H:i');

        $expectedResult = array($expectedReg1, $expectedReg2, $expectedReg3, $expectedReg4, $expectedReg5);

        $this->assertEquals($expectedResult, $result['registrations']);
    }

    public function testTeachersList()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->firstname = 'Tom';
        $user->lastname = 'Jackson';
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 2;

        $this->lab->id = 401;
        $this->lab->start = new \DateTime();

        $teacher1 = new \stdClass();
        $teacher1->firstname = 'Mari';
        $teacher1->lastname = 'Mägi';
        $teacher1->id = 100;
        $teacher2 = new \stdClass();
        $teacher2->firstname = 'Karl';
        $teacher2->lastname = 'Kivi';
        $teacher2->id = 101;
        $teacher3 = new \stdClass();
        $teacher3->firstname = 'Anna';
        $teacher3->lastname = 'Aluoja';
        $teacher3->id = 102;

        $teachers = array($teacher1, $teacher2, $teacher3);

        $defence1 = new \stdClass();
        $defence1->teacher_id = 101;
        $defence1->charon = 'EX01';
        $defence2 = new \stdClass();
        $defence2->teacher_id = 102;
        $defence2->charon = 'EX02';
        $defence3 = new \stdClass();
        $defence3->teacher_id = 101;
        $defence3->charon = 'EX02';

        $teachersDefences = array($defence1, $defence2, $defence3);

        $this->defenseRegistrationRepository->shouldReceive('getLabRegistrationsByLabId')
            ->once()
            ->with(401, true)
            ->andReturn([]);
        $this->labTeacherRepository->shouldReceive('getAllLabTeachersByLab')
            ->once()
            ->with(401)
            ->andReturn($teachers);
        $this->defenseRegistrationRepository->shouldReceive('getTeacherAndDefendingCharonByLab')
            ->once()
            ->with(401)
            ->andReturn($teachersDefences);

        $result = $this->service->labQueueStatus($user, $this->lab);

        $expectedTeacher1 = new \stdClass();
        $expectedTeacher1->teacher_name = 'Mari Mägi';
        $expectedTeacher1->charon = '';
        $expectedTeacher1->availability = 'Free';
        $expectedTeacher2 = new \stdClass();
        $expectedTeacher2->teacher_name = 'Karl Kivi';
        $expectedTeacher2->charon = 'EX01';
        $expectedTeacher2->availability = 'Defending';
        $expectedTeacher3 = new \stdClass();
        $expectedTeacher3->teacher_name = 'Anna Aluoja';
        $expectedTeacher3->charon = 'EX02';
        $expectedTeacher3->availability = 'Defending';

        $expectedResult = array($expectedTeacher1, $expectedTeacher2, $expectedTeacher3);

        $this->assertEquals($expectedResult, $result['teachers']);
    }
}
