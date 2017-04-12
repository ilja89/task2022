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
        /** @var CourseSettings $courseSettings */
        $courseSettings = factory('TTU\Charon\Models\CourseSettings')->create();
        $courseSettings = CourseSettings::where('id', $courseSettings->id)->first();

        $this->get('/courses/' . $courseSettings->course_id . '/settings')
             ->assertViewHas('settings', $courseSettings);
    }
}
