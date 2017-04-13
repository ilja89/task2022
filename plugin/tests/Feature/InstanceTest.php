<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;

class InstanceTest extends TestCase
{
    use DatabaseTransactions;

    private $courseId;

    /** @test */
    public function it_saves_new_charon()
    {
        $course = factory(\Zeizig\Moodle\Models\Course::class)->create();
        $this->courseId = $course->id;
        factory(CourseSettings::class)->create([
            'course_id' => $course->id,
        ]);

        $requestParams = [
            'name' => $this->faker->name,
            'description' => [
                'text' => $this->faker->text
            ],
            'extra' => 'stylecheck',
            'project_folder' => $this->faker->word,
            'tester_type' => $this->faker->randomElement([1, 2, 3, 4]),
            'grading_method' => $this->faker->randomElement([1, 2]),
            'course' => $course->id,
            'grademaps' => [
                1 => [
                    'grademap_name' => $this->faker->word,
                    'max_points' => 100,
                    'id_number' => $this->faker->word
                ]
            ],
            'deadlines' => [],
        ];

        Event::fake();

        $response = $this->post('/charons', $requestParams);

        /** @var Charon $charon */
        $charon = Charon::where('id', $response->baseResponse->content())->first();

        $this->assertEquals($requestParams['name'], $charon->name);
        $this->assertEquals($requestParams['course'], $course->id);
        $this->assertEquals($requestParams['grademaps'][1]['grademap_name'], $charon->grademaps[0]->name);
        $this->assertEquals($requestParams['name'], $charon->category->fullname);

        Event::assertDispatched(CharonCreated::class, function ($e) use ($charon) {
            return $e->charon->id === $charon->id;
        });

        // TODO: Figure out how to clear database completely after test (grade items, grade items history)
    }
}
