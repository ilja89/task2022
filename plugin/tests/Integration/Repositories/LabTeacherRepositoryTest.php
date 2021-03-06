<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Registration;
use TTU\Charon\Repositories\LabTeacherRepository;
use Tests\TestCase;
use Zeizig\Moodle\Models\User;

class LabTeacherRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var LabTeacherRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = new LabTeacherRepository(new MoodleConfig());
    }

    public function testCheckWhichTeachersBusyAt()
    {
        /** @var User $teacher1 */
        $teacher1 = factory(User::class)->create();

        /** @var User $teacher2 */
        $teacher2 = factory(User::class)->create();

        /** @var User $teacher3 */
        $teacher3 = factory(User::class)->create();

        /** @var User $student */
        $student = factory(User::class)->create();

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create();

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var CharonDefenseLab $defenseLab */
        $defenseLab = CharonDefenseLab::create(['lab_id' => $lab->id, 'charon_id' => $charon->id]);

        $common = [
            'student_id' => $student->id,
            'charon_id' => $charon->id,
            'defense_lab_id' => $defenseLab->id,
            'progress' => 'Waiting'
        ];

        $chosenTime = Carbon::now()->addMinutes(20);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(10),
            'teacher_id' => $teacher1->id
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => $chosenTime,
            'teacher_id' => $teacher2->id
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(30),
            'teacher_id' => $teacher3->id
        ]);

        $actual = $this->repository->checkWhichTeachersBusyAt(
            [$teacher1->id, $teacher2->id, $teacher3->id],
            $chosenTime->format('Y-m-d H:i:s')
        );

        $this->assertEquals([$teacher2->id], $actual);
    }
}
