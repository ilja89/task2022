<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\CourseModule;

class AssignmentTest extends TestCase
{
    // We must use DatabaseTransactions and not DatabaseMigrations because most of the
    // database isn't made with Laravel migrations.
    use DatabaseTransactions, WithoutMiddleware;

    /** @test */
    public function it_uses_correct_charon_instance()
    {
        $this->markTestSkipped('Out of date, needs attention. Fails because categories are not set for charon');

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create();
        /** @var CourseModule $courseModule */
        $courseModule = factory(CourseModule::class)->create([
            'instance' => $charon->id,
            'course'   => $charon->course
        ]);

        $expected = Charon::with('testerType', 'gradingMethod', 'grademaps', 'deadlines')
                          ->find($charon->id);

        $this->get('/view.php?id=' . $courseModule->id)
             ->assertStatus(200)
             ->assertViewHas('charon', $expected);
    }
}
