<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TTU\Charon\Models\CourseSettings;

class CourseSettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function testCourseSettingsSavesNewSettings()
    {
        $course = factory(\Zeizig\Moodle\Models\Course::class)->create();

        $response = $this->post('/courses/' . $course->id . '/settings', [
            'unittests_git' => 'unittests git here',
            'tester_type' => 2
        ]);
        $response->assertRedirect('/courses/' . $course->id . '/settings');

        /** @var CourseSettings $courseSettings */
        $courseSettings = CourseSettings::where('course_id', $course->id)->first();

        $this->assertEquals(2, $courseSettings->tester_type_code);
        $this->assertEquals('unittests git here', $courseSettings->unittests_git);
    }

    public function testCourseSettingsUpdatesExistingSetting()
    {
        /** @var CourseSettings $courseSettings */
        $courseSettings = factory('TTU\Charon\Models\CourseSettings')->create();

        $this->post('/courses/' . $courseSettings->course_id . '/settings', [
            'unittests_git' => 'unittests git here',
            'tester_type' => 2
        ]);

        $courseSettings = CourseSettings::where('course_id', $courseSettings->course_id)->first();
        $this->assertEquals(2, $courseSettings->tester_type_code);
        $this->assertEquals('unittests git here', $courseSettings->unittests_git);
    }
}
