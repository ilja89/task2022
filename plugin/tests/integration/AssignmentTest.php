<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AssignmentTest extends TestCase
{
    // We must use DatabaseTransactions and not DatabaseMigrations because most of the database isn't made with Laravel
    // migrations.
    use DatabaseTransactions, WithoutMiddleware;

    public function testAssignmentViewUsesCorrectInstance()
    {
        $course = factory(\Zeizig\Moodle\Models\Course::class)->create();
        /** @var \TTU\Charon\Models\Charon $charon */
        $charon = factory(TTU\Charon\Models\Charon::class)->create([
            'course' => $course->id
        ]);
        /** @var \Zeizig\Moodle\Models\CourseModule $courseModule */
        $courseModule = factory(Zeizig\Moodle\Models\CourseModule::class)->create([
            'instance' => $charon->id,
            'course' => $course->id
        ]);

        $expected = \TTU\Charon\Models\Charon::with('testerType', 'gradingMethod', 'grademaps', 'deadlines')
            ->find($charon->id);

        $this->visit('/view.php?id=' . $courseModule->id)
             ->assertResponseOk()
             ->assertViewHas('charon', $expected);
    }
}
