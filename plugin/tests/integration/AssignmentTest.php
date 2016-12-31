<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class AssignmentTest extends TestCase
{
    // We must use DatabaseTransactions and not DatabaseMigrations because most of the database isn't made with Laravel
    // migrations.
    use DatabaseTransactions;

    public function testAssignmentViewUsesCorrectInstance()
    {
        /** @var \TTU\Charon\Models\Charon $charon */
        $charon = factory(TTU\Charon\Models\Charon::class)->create();
        /** @var \Zeizig\Moodle\Models\CourseModule $courseModule */
        $courseModule = factory(Zeizig\Moodle\Models\CourseModule::class)->create([
            'instance' => $charon->id
        ]);

        $expected = \TTU\Charon\Models\Charon::find($charon->id);

        $this->visit('/view.php?id=' . $courseModule->id)
             ->assertResponseOk()
             ->assertViewHas('instance', $expected);
    }
}
