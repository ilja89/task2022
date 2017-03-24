<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\CourseSettings;

class CourseSettingsFormTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindsCorrectSettings()
    {
        $course = factory(\Zeizig\Moodle\Models\Course::class)->create();

        CourseSettings::create([
            'course_id' => $course->id,
            'unittests_git' => 'old unittests git',
            'tester_type_code' => 1,
        ]);
        $courseSettings = CourseSettings::where('course_id', $course->id)->first();

        $response = $this->get('/courses/' . $course->id . '/settings');

        $response->assertViewHas('settings', $courseSettings);
    }
}
