<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Services\PermissionsService;

class PopupController extends Controller
{
    /** @var Request */
    private $request;

    /** @var PermissionsService */
    private $permissionsService;

    /**
     * PopupController constructor.
     *
     * @param Request $request
     * @param PermissionsService $permissionsService
     */
    public function __construct(Request $request, PermissionsService $permissionsService)
    {
        $this->request = $request;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Display the Charon popup.
     */
    public function index()
    {
        $course = $this->getCourse();
        $this->requirePermissions($course->id);

        return view('popup.index', compact('course'));
    }

    /**
     * Require the permission to manage the given course.
     *
     * @param  integer  $courseId
     *
     * @return void
     */
    private function requirePermissions($courseId)
    {
        $this->permissionsService->requireCourseManagementCapability($courseId);
    }

    /**
     * Gets the current course.
     *
     * @return Course
     */
    private function getCourse()
    {
        return Course::find($this->request['course_id']);
    }
}
