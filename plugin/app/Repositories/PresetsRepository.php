<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\Preset;

/**
 * Class PresetsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class PresetsRepository
{
    /**
     * Get presets by the course. Also gets presets with no course ID meaning
     * they are for all courses.
     *
     * @param  int  $courseId
     *
     * @return Preset[]
     */
    public function getPresetsByCourse($courseId)
    {
        return Preset::with('presetGrades')
            ->where('course_id', $courseId)
            ->orWhere('course_id', null)
            ->get();
    }

    public function getPresetsOnlyForCourse($courseId)
    {
        $presets = Preset::with('presetGrades')
                     ->where('course_id', $courseId)
                     ->get();

        return $presets;
    }
}
