<?php

namespace TTU\Charon\Http\Controllers\Api;

use TTU\Charon\Http\Controllers\Controller;
use Zeizig\Moodle\Models\Course;

class CourseController extends Controller
{
    public function index(Course $course): Course
    {
        return $course;
    }
}
