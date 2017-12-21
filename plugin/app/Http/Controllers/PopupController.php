<?php

namespace TTU\Charon\Http\Controllers;

use Zeizig\Moodle\Models\Course;

/**
 * Class PopupController.
 *
 * @package TTU\Charon\Http\Controllers
 */
class PopupController extends Controller
{
    /**
     * Display the Charon popup.
     *
     * @param Course $course
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index(Course $course)
    {
        $this->setUrl($course->id);

        return view('popup.index', compact('course'));
    }

    /**
     * Sets the URL. Needed by Moodle.
     *
     * @param  integer  $courseId
     */
    private function setUrl($courseId)
    {
        global $PAGE;
        $PAGE->set_url('/mod/charon/courses/' . $courseId . '/popup', []);
    }
}
