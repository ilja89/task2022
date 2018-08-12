<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\CourseModule;

class InstanceFormTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function update_form_uses_correct_charon()
    {
        /** @var CourseModule $courseModule */
        /** @var Charon $charon */
        $courseModule = factory(CourseModule::class)->create([
            'instance' => factory(Charon::class)->create([
                'category_id' => null,
            ])->id,
        ]);
        $charon = Charon::find($courseModule->instance);
        $charon->load(['testerType', 'gradingMethod', 'grademaps', 'deadlines']);

        $this->get('/instance_form?update=' . $courseModule->id . '&course=' . $charon->course)
            ->assertViewHas('charon', $charon);
    }

    /** @test */
    public function create_form_shows_the_form()
    {
        $course = factory(Course::class)->create();

        $this->get('/instance_form?course=' . $course->id)
            ->assertStatus(200);
    }

    /** @test */
    public function create_form_shows_previous_values_after_error()
    {
        $course = factory(Course::class)->create();

        $params = $this->getRequestParams();
        $params['course'] = $course->id;

        $this->post('/instance_form', $params)
             ->assertStatus(200)
            ->assertSee($params['name'])
            ->assertSee($params['tester_extra'])
            ->assertSee('' . $params['max_score'])
            ->assertSee($params['project_folder']);
    }

    private function getRequestParams()
    {
        return [
            'name'                => $this->faker->sentence,
            'description'         => [
                'text' => $this->faker->paragraph,
            ],
            'project_folder'      => $this->faker->word,
            'tester_extra'        => $this->faker->word,
            'tester_type_code'    => $this->faker->randomElement([1, 2, 3, 4]),
            'grading_method_code' => $this->faker->randomElement([1, 2]),
            'grademaps' => [],
            'deadlines' => [],
            'max_score' => $this->faker->randomFloat(),
            'calculation_formula' => '',
        ];
    }
}
