<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * @param int $id
     *
     * @return Course
     * @throws ModelNotFoundException
     */
    public function find(int $id): Course
    {
        return Course::findOrFail($id);
    }
}
