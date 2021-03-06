<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\CourseSettings;
use Zeizig\Moodle\Models\Course;

class CourseSettingsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_saves_new_course_settings()
    {
        $this->markTestSkipped('Out of date, needs attention');

        /** @var Course $course */
        $course = factory(Course::class)->create();
        $request = $this->getRandomRequest();

        $this->post('/courses/' . $course->id . '/settings', $request);

        /** @var CourseSettings $courseSettings */
        $courseSettings = CourseSettings::where('course_id', $course->id)->first();
        $this->assertEquals($request['unittests_git'], $courseSettings->unittests_git);
        $this->assertEquals($request['tester_type'], $courseSettings->tester_type_code);
    }

    /** @test */
    public function it_updates_existing_course_settings()
    {
        $this->markTestSkipped('Out of date, needs attention');

        /** @var CourseSettings $courseSettings */
        $courseSettings = factory(CourseSettings::class)->create();
        $request = $this->getRandomRequest();

        $this->post('/courses/' . $courseSettings->course_id . '/settings', $request);

        $courseSettings = CourseSettings::where('course_id', $courseSettings->course_id)->first();
        $this->assertEquals($request['unittests_git'], $courseSettings->unittests_git);
        $this->assertEquals($request['tester_type'], $courseSettings->tester_type_code);
    }

    private function getRandomRequest()
    {
        return [
            'unittests_git' => $this->faker->word,
            'tester_type'   => $this->faker->randomElement([1, 2, 3, 4]),
        ];
    }
}
