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
}
