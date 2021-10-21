<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Tests\TestCase;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Services\ModuleService;

class LabRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var LabRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LabRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(LabTeacherRepository::class),
            Mockery::mock(CharonDefenseLabRepository::class)
        );
    }

    public function testGettingLabsWithStartAndEndTimes()
    {
        // Creating 4 charons
        $charon1 = factory(Charon::class)->create();
        $charon2 = factory(Charon::class)->create();
        $charon3 = factory(Charon::class)->create();
        $charon4 = factory(Charon::class)->create();

        /** @var Course $course */
        $course = factory(Course::class)->create();

        // Creating 4 labs
        $lab1 = factory(Lab::class)->create(['course_id' => $course->id, 'name' => 'lab1']);
        $lab2 = factory(Lab::class)->create(['course_id' => $course->id, 'name' => 'lab2']);
        $lab3 = factory(Lab::class)->create(['course_id' => $course->id, 'name' => 'lab3']);
        $lab4 = factory(Lab::class)->create(['course_id' => $course->id, 'name' => 'lab4']);

        // Defences for lab1
        $defLab1Charon1 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab1->id, 'charon_id' => $charon1->id]);
        $defLab1Charon2 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab1->id, 'charon_id' => $charon2->id]);

        // Defences for lab2
        $defLab2Charon2 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab2->id, 'charon_id' => $charon2->id]);
        $defLab2Charon3 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab2->id, 'charon_id' => $charon3->id]);

        // Defences for lab3
        $defLab3Charon1 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab3->id, 'charon_id' => $charon1->id]);
        $defLab3Charon2 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab3->id, 'charon_id' => $charon2->id]);
        $defLab3Charon3 = factory(CharonDefenseLab::class)->create(['lab_id' => $lab3->id, 'charon_id' => $charon3->id]);

        // Test lab getting by charon 1
        $actual = $this->repository->getLabsWithStartAndEndTimes($charon1->id);
        $this->assertEquals(2, count($actual));
        $wrongLabs = ['lab2', 'lab4'];
        foreach ($actual as $lab){
            $this->assertNotContains($lab->name, $wrongLabs);
        }

        // Test lab getting by charon 2
        $actual = $this->repository->getLabsWithStartAndEndTimes($charon2->id);
        $this->assertEquals(3, count($actual));
        foreach ($actual as $lab){
            $this->assertNotEquals('lab4', $lab->name);
        }

        // Test lab getting by charon 3
        $actual = $this->repository->getLabsWithStartAndEndTimes($charon3->id);
        $this->assertEquals(2, count($actual));
        $wrongLabs = ['lab1', 'lab4'];
        foreach ($actual as $lab){
            $this->assertNotContains($lab->name, $wrongLabs);
        }

        // Test lab getting by charon 4
        $actual = $this->repository->getLabsWithStartAndEndTimes($charon4->id);
        $this->assertEmpty($actual);

    }
}
