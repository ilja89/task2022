<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\CourseSettings;

class CourseSettingsFormTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function shows_correct_course_settings()
    {
        $this->markTestSkipped('Out of date, needs attention');

        /** @var CourseSettings $courseSettings */
        $courseSettings = factory(CourseSettings::class)->create();
        $courseSettings->wasRecentlyCreated = false;

        $this->get('/courses/' . $courseSettings->course_id . '/settings')
             ->assertViewHas('settings', $courseSettings);
    }
}
