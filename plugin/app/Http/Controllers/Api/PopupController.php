<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\Course;

/**
 * Class PopupController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PopupController extends Controller
{
    /**
     * @param  Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|[]
     */
    public function getCharonsByCourse(Course $course)
    {
        return Charon::all();
    }
}
