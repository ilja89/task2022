<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use Zeizig\Moodle\Models\Course;

class InstanceTest extends TestCase
{
    use DatabaseTransactions;

    private $courseId;

    /** @test */
    public function it_saves_new_charon()
    {
        /** @var CourseSettings $courseSettings */
        $courseSettings = factory(CourseSettings::class)->create();

        $requestParams = $this->getRandomRequest($courseSettings->course_id);

        Event::fake();

        /** @var Charon $charon */
        $charon = $this->makeStoreRequestAndGetCharon($requestParams);

        $this->assertEquals($requestParams['name'], $charon->name);
        $this->assertEquals($requestParams['course'], $courseSettings->course_id);
        $this->assertEquals($requestParams['grademaps'][1]['grademap_name'], $charon->grademaps[0]->name);
        $this->assertEquals($requestParams['name'], $charon->category->fullname);

        // TODO: Figure out how to clear database completely after test (grade items, grade items history)
    }

    public function it_notifies_tester_of_new_charon()
    {
        /** @var CourseSettings $courseSettings */
        /** @var Charon $charon */
        Event::fake();

        $courseSettings = factory(CourseSettings::class)->create();
        $requestParams = $this->getRandomRequest($courseSettings->course_id);

        $charon = $this->makeStoreRequestAndGetCharon($requestParams);

        Event::assertDispatched(CharonCreated::class, function ($e) use ($charon) {
            return $e->charon->id === $charon->id;
        });
    }

    private function makeStoreRequestAndGetCharon($params)
    {
        $response = $this->post('/charons', $params);
        return Charon::where('id', $response->baseResponse->content())->first();
    }

    /**
     * @param  int  $courseId
     *
     * @return array
     */
    private function getRandomRequest($courseId)
    {
        return [
            'name'           => $this->faker->name,
            'description'    => [
                'text' => $this->faker->text
            ],
            'extra'          => 'stylecheck',
            'project_folder' => $this->faker->word,
            'tester_type'    => $this->faker->randomElement([1, 2, 3, 4]),
            'grading_method' => $this->faker->randomElement([1, 2]),
            'course'         => $courseId,
            'grademaps'      => [
                1 => [
                    'grademap_name' => $this->faker->word,
                    'max_points'    => 100,
                    'id_number'     => $this->faker->word
                ]
            ],
            'deadlines'      => [],
        ];
    }
}
