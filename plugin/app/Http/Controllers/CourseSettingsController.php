<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Models\CourseSettings;
use Zeizig\Moodle\Models\Course;

class CourseSettingsController extends Controller
{
    /** @var Request */
    private $request;

    /**
     * CourseSettingsController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store(Course $course)
    {
        $courseSettings = CourseSettings::where('course_id', $course->id)
            ->get();

        if ($courseSettings->isEmpty()) {
            $courseSettings            = new CourseSettings();
            $courseSettings->course_id = $course->id;
        } else {
            $courseSettings = $courseSettings->first();
        }

        $courseSettings->unittests_git = $this->request['unittests_git'];
        $courseSettings->save();

        redirect('/mod/charon/courses/' . $course->id . '/settings');
    }
}
