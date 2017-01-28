<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Services\PermissionsService;

class PopupController extends Controller
{
    /**
     * Display the Charon popup.
     *
     * @param Course $course
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Course $course)
    {
        return view('popup.index', compact('course'));
    }
}
