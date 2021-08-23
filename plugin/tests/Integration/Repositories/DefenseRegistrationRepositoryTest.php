<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Registration;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use Tests\TestCase;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\ModuleService;

class DefenseRegistrationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var DefenseRegistrationRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DefenseRegistrationRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(LabTeacherRepository::class)
        );
    }

    /**
     * Cases:
     * - 1st matches time but not progress and still queues
     * + 2nd & 3rd match time and progress
     * - (4th matches progress but queue does not have enough capacity) - commented out
     */
    public function testGetUserPendingRegistrationsCount()
    {
        $start = Carbon::now();
        $end = Carbon::now()->addMinutes(50);

        /** @var User $student */
        $student = factory(User::class)->create();

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create(['start' => $start, 'end' => $end]);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0, 'defense_duration' => 15]);

        /** @var CharonDefenseLab $defenseLab */
        $defenseLab = CharonDefenseLab::create(['lab_id' => $lab->id, 'charon_id' => $charon->id]);

        $common = [
            'student_id' => $student->id,
            'charon_id' => $charon->id,
            'defense_lab_id' => $defenseLab->id,
        ];

        factory(Registration::class)->create($common + [
            'progress' => 'Done'
        ]);

        factory(Registration::class)->create($common + [
            'progress' => 'Waiting'
        ]);

        factory(Registration::class)->create($common + [
            'progress' => 'Defending'
        ]);

        /*
        factory(Registration::class)->create($common + [
            'progress' => 'Defending'
        ]);
        */

        $actual = $this->repository->getUserPendingRegistrationsCount($student->id, $charon->id, $start, $end);

        $this->assertEquals(2, $actual);
    }
}
