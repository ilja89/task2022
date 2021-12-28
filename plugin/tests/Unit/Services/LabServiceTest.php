<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\DefenceRegistrationService;
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

    /** @var Mock|CharonRepository */
    private $charonRepository;

    /** @var Mock|DefenceRegistrationService */
    private $defenceRegistrationService;

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
            $this->charonRepository = Mockery::mock(CharonRepository::class),
            $this->defenceRegistrationService = Mockery::mock(DefenceRegistrationService::class)
        );
    }

    public function testFindAvailableLabsByCharon()
    {
        $charon = Mockery::mock(Charon::class)->makePartial();
        $charon->id = 222;

        $lab1 = Mockery::mock(Lab::class)->makePartial();
        $lab1->id = 1;
        $lab1->start = Carbon::now()->addDays(30);
        $lab1->end = Carbon::now()->addDays(30)->addHours(3);

        $lab2 = Mockery::mock(Lab::class)->makePartial();
        $lab2->id = 2;
        $lab2->start = Carbon::now()->addDays(60);
        $lab2->end = Carbon::now()->addDays(60)->addHour();

        $lab4 = Mockery::mock(Lab::class)->makePartial();
        $lab4->id = 3;
        $lab4->start = Carbon::now()->addDays(90);
        $lab4->end = Carbon::now()->addDays(90)->addHours(2);

        $labs = array($lab1, $lab2, $lab4);

        $this->labRepository->shouldReceive('getAvailableLabsByCharonId')
            ->once()
            ->with($charon->id)
            ->andReturn($labs);

        $this->charonRepository->shouldReceive('getCharonById')
            ->once()
            ->with($charon->id)
            ->andReturn($charon);

        foreach ($labs as $lab) {
            $this->defenceRegistrationService->shouldReceive('getEstimateTimeForNewRegistration')
                ->once()
                ->with($lab, $charon)
                ->andReturn($lab->start);

            $this->defenseRegistrationRepository->shouldReceive('countUndoneDefendersByLab')
                ->once()
                ->with($lab->id)
                ->andReturn(0);
        }

        $result = $this->service->findAvailableLabsByCharon($charon->id);

        $this->assertEquals(count($labs), count($result));
    }

    public function testStudentsQueueLabNotStarted()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->firstname = 'Tom';
        $user->lastname = 'Jackson';

        $this->lab->id = 401;
        $this->lab->start = new \DateTime('+1 day');
        $this->lab->end = date_modify(new \DateTime('+1 day'), '+1 hour');

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

        $regWithTime1 = new \stdClass();
        $regWithTime1->charon_name = 'EX01';
        $regWithTime1->charon_length = 5;
        $regWithTime1->student_id = 100;
        $regWithTime2 = new \stdClass();
        $regWithTime2->charon_name = 'EX02';
        $regWithTime2->charon_length = 5;
        $regWithTime2->student_id = 1;
        $regWithTime3 = new \stdClass();
        $regWithTime3->charon_name = 'EX01';
        $regWithTime3->charon_length = 5;
        $regWithTime3->student_id = 102;
        $regWithTime4 = new \stdClass();
        $regWithTime4->charon_name = 'EX06';
        $regWithTime4->charon_length = 5;
        $regWithTime4->student_id = 102;
        $regWithTime5 = new \stdClass();
        $regWithTime5->charon_name = 'EX06';
        $regWithTime5->charon_length = 5;
        $regWithTime5->student_id = 102;
        $regWithTime6 = new \stdClass();
        $regWithTime6->charon_name = 'EX08';
        $regWithTime6->charon_length = 5;
        $regWithTime6->student_id = 102;
        $regWithTime7 = new \stdClass();
        $regWithTime7->charon_name = 'EX01';
        $regWithTime7->charon_length = 5;
        $regWithTime7->student_id = 1;
        $regWithTime1->estimated_start = $this->lab->start;
        $regWithTime2->estimated_start = $this->lab->start;
        $regWithTime3->estimated_start = $this->lab->start;
        $addFiveMinutes = $this->lab->start->modify('+5 minutes');
        $regWithTime4->estimated_start = $addFiveMinutes;
        $regWithTime5->estimated_start = $addFiveMinutes;
        $regWithTime6->estimated_start = $addFiveMinutes;
        $addTenMinutes = $this->lab->start->modify('+10 minutes');
        $regWithTime7->estimated_start = $addTenMinutes;

        $registrationsWithTimes = array($regWithTime1, $regWithTime2, $regWithTime3, $regWithTime4, $regWithTime5, $regWithTime6, $regWithTime7);

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
            ->with(401, ['Waiting', 'Defending'])
            ->andReturn($registrations);
        $this->labTeacherRepository->shouldReceive('getAllLabTeachersByLab')
            ->once()
            ->with(401)
            ->andReturn($teachers);
        $this->defenseRegistrationRepository->shouldReceive('getTeacherAndDefendingCharonByLab')
            ->once()
            ->with($this->lab->id);
        $this->defenceRegistrationService->shouldReceive('attachEstimatedTimesToDefenceRegistrations')
            ->once()
            ->andReturn($registrationsWithTimes);

        $result = $this->service->labQueueStatus($user, $this->lab);

        $expectedReg1 = new \stdClass();
        $expectedReg1->charon_name = 'EX01';
        $expectedReg1->student_name = '';
        $expectedReg1->queue_pos = 1;
        $expectedReg1->charon_length = 5;
        $expectedReg1->estimated_start = $this->lab->start->format('d.m.Y H:i');
        $expectedReg2 = new \stdClass();
        $expectedReg2->charon_name = 'EX02';
        $expectedReg2->student_name = 'Tom Jackson';
        $expectedReg2->queue_pos = 2;
        $expectedReg2->charon_length = 5;
        $expectedReg2->estimated_start = $this->lab->start->format('d.m.Y H:i');
        $expectedReg3 = new \stdClass();
        $expectedReg3->charon_name = 'EX01';
        $expectedReg3->student_name = '';
        $expectedReg3->queue_pos = 3;
        $expectedReg3->charon_length = 5;
        $expectedReg3->estimated_start = $this->lab->start->format('d.m.Y H:i');

        // add 5 minutes to time, as next registrations are 5 minutes later
        $labStartPlusFive = $this->lab->start->modify('+5 minutes');

        $expectedReg4 = new \stdClass();
        $expectedReg4->charon_name = 'EX06';
        $expectedReg4->student_name = '';
        $expectedReg4->queue_pos = 4;
        $expectedReg4->charon_length = 5;
        $expectedReg4->estimated_start = $labStartPlusFive->format('d.m.Y H:i');
        $expectedReg5 = new \stdClass();
        $expectedReg5->charon_name = 'EX06';
        $expectedReg5->student_name = '';
        $expectedReg5->queue_pos = 5;
        $expectedReg5->charon_length = 5;
        $expectedReg5->estimated_start = $labStartPlusFive->format('d.m.Y H:i');
        $expectedReg6 = new \stdClass();
        $expectedReg6->charon_name = 'EX08';
        $expectedReg6->student_name = '';
        $expectedReg6->queue_pos = 6;
        $expectedReg6->charon_length = 5;
        $expectedReg6->estimated_start = $labStartPlusFive->format('d.m.Y H:i');

        // add 5 minutes to time, as next registration is 5 minutes later
        $labStartPlusTen = $this->lab->start->modify('+10 minutes');

        $expectedReg7 = new \stdClass();
        $expectedReg7->charon_name = 'EX01';
        $expectedReg7->student_name = 'Tom Jackson';
        $expectedReg7->queue_pos = 7;
        $expectedReg7->charon_length = 5;
        $expectedReg7->estimated_start = $labStartPlusTen->format('d.m.Y H:i');

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

        $this->lab->id = 401;
        $this->lab->start = new \DateTime('-30 minutes');
        $this->lab->end = date_modify($this->lab->start, '+1 hour');

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

        $regWithTime1 = new \stdClass();
        $regWithTime1->charon_name = 'EX01';
        $regWithTime1->charon_length = 5;
        $regWithTime1->student_id = 100;
        $regWithTime2 = new \stdClass();
        $regWithTime2->charon_name = 'EX02';
        $regWithTime2->charon_length = 5;
        $regWithTime2->student_id = 1;
        $regWithTime3 = new \stdClass();
        $regWithTime3->charon_name = 'EX01';
        $regWithTime3->charon_length = 5;
        $regWithTime3->student_id = 102;
        $regWithTime4 = new \stdClass();
        $regWithTime4->charon_name = 'EX06';
        $regWithTime4->charon_length = 5;
        $regWithTime4->student_id = 102;
        $regWithTime5 = new \stdClass();
        $regWithTime5->charon_name = 'EX06';
        $regWithTime5->charon_length = 5;
        $regWithTime5->student_id = 102;
        $regWithTime1->estimated_start = $this->lab->start;
        $regWithTime2->estimated_start = $this->lab->start;
        $addFiveMinutes = $this->lab->start->modify('+5 minutes');
        $regWithTime3->estimated_start = $addFiveMinutes;
        $regWithTime4->estimated_start = $addFiveMinutes;
        $addTenMinutes = $this->lab->start->modify('+10 minutes');
        $regWithTime5->estimated_start = $addTenMinutes;

        $registrationsWithTimes = array($regWithTime1, $regWithTime2, $regWithTime3, $regWithTime4, $regWithTime5);

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
            ->with($this->lab->id, true)
            ->andReturn($registrations);
        $this->labTeacherRepository->shouldReceive('getAllLabTeachersByLab')
            ->once()
            ->with($this->lab->id)
            ->andReturn($teachers);
        $this->defenseRegistrationRepository->shouldReceive('getTeacherAndDefendingCharonByLab')
            ->once()
            ->with($this->lab->id)
            ->andReturn(new Collection());
        $this->defenceRegistrationService->shouldReceive('attachEstimatedTimesToDefenceRegistrations')
            ->once()
            ->andReturn($registrationsWithTimes);

        $result = $this->service->labQueueStatus($user, $this->lab);
        $queueTime = $this->lab->start;

        $expectedReg1 = new \stdClass();
        $expectedReg1->charon_name = 'EX01';
        $expectedReg1->student_name = '';
        $expectedReg1->queue_pos = 1;
        $expectedReg1->charon_length = 5;
        $expectedReg1->estimated_start = $queueTime->format('d.m.Y H:i');
        $expectedReg2 = new \stdClass();
        $expectedReg2->charon_name = 'EX02';
        $expectedReg2->student_name = 'Tom Jackson';
        $expectedReg2->queue_pos = 2;
        $expectedReg2->charon_length = 5;
        $expectedReg2->estimated_start = $queueTime->format('d.m.Y H:i');

        // add 5 minutes to time, as next registrations are 5 minutes later
        $queueTime = $queueTime->modify('+5 minutes');

        $expectedReg3 = new \stdClass();
        $expectedReg3->charon_name = 'EX01';
        $expectedReg3->student_name = '';
        $expectedReg3->queue_pos = 3;
        $expectedReg3->charon_length = 5;
        $expectedReg3->estimated_start = $queueTime->format('d.m.Y H:i');
        $expectedReg4 = new \stdClass();
        $expectedReg4->charon_name = 'EX06';
        $expectedReg4->student_name = '';
        $expectedReg4->queue_pos = 4;
        $expectedReg4->charon_length = 5;
        $expectedReg4->estimated_start = $queueTime->format('d.m.Y H:i');

        // add 5 minutes to time, as next registration is 5 minutes later
        $queueTime = $queueTime->modify('+5 minutes');

        $expectedReg5 = new \stdClass();
        $expectedReg5->charon_name = 'EX06';
        $expectedReg5->student_name = '';
        $expectedReg5->queue_pos = 5;
        $expectedReg5->charon_length = 5;
        $expectedReg5->estimated_start = $queueTime->format('d.m.Y H:i');

        $expectedResult = array($expectedReg1, $expectedReg2, $expectedReg3, $expectedReg4, $expectedReg5);

        $this->assertEquals($expectedResult, $result['registrations']);
    }

    public function testLabQueueTeacherList()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->firstname = 'Tom';
        $user->lastname = 'Jackson';

        $this->lab->id = 401;
        $this->lab->start = new \DateTime();
        $this->lab->end = date_modify(new \DateTime(), '+1 hour');

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

        $teachersDefences = new Collection();
        $teachersDefences->add($defence1);
        $teachersDefences->add($defence2);

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
        $this->defenceRegistrationService->shouldReceive('attachEstimatedTimesToDefenceRegistrations')
            ->once()
            ->andReturn([]);

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
