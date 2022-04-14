<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Zeizig\Moodle\Models\Course;

/**
 * Class CourseRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class CourseRepository
{
    /**
     * @return Builder|Course
     */
    public function query()
    {
        return Course::query();
    }

    /**
     * Get the shortname of the course by the id
     * @param $courseId
     * @return null
     */
    public function getShortnameById($courseId)
    {
        $courseShortname = Course::where('id', $courseId)->value('shortname');
        return $courseShortname ?: null;
    }
}
