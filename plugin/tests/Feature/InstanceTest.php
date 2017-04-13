<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use Zeizig\Moodle\Models\CourseModule;

class InstanceTest extends TestCase
{
    use DatabaseTransactions;

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

    /** @test */
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

    /** @test */
    public function it_updates_existing_charon()
    {
        /** @var CourseModule $courseModule */
        /** @var Charon $charon */
        $courseModule = factory(CourseModule::class)->create();
        $charon = Charon::find($courseModule->instance);
        factory(CourseSettings::class)->create([
            'course_id' => $charon->course,
        ]);

        $params = $this->getRandomRequest($charon->course);
        $params['update'] = $courseModule->id;

        $response = $this->post('/charons/update', $params);
        $charon = Charon::find($courseModule->instance);

        $this->assertEquals($params['name'], $charon->name);
        $this->assertEquals($params['course'], $charon->course);
        $this->assertEquals($params['grademaps'][1]['grademap_name'], $charon->grademaps[0]->name);
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
