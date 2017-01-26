<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\CourseSettings;

/**
 * Class CourseSettingsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class CourseSettingsRepository
{
    /**
     * Get course settings by course id.
     *
     * @param  integer  $courseId
     *
     * @return CourseSettings
     */
    public function getCourseSettingsByCourseId($courseId)
    {
        $courseSettings = CourseSettings::where('course_id', $courseId)
            ->get();

        if ($courseSettings->isEmpty()) {
            return null;
        }

        return $courseSettings->first();
    }
}
