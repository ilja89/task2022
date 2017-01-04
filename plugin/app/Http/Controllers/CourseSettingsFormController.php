<?php

namespace TTU\Charon\Http\Controllers;

use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Services\PermissionsService;

/**
 * Class CourseSettingsFormController.
 * This controller renders the course settings form.
 *
 * @package TTU\Charon\Http\Controllers
 */
class CourseSettingsFormController extends Controller
{
    /** @var PermissionsService */
    protected $permissionsService;

    /**
     * CourseSettingsFormController constructor.
     *
     * @param  PermissionsService  $permissionsService
     */
    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Renders the course settings form.
     *
     * @param  Course  $course
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Course $course)
    {
        $this->requirePermissions($course);

        return view('welcome');
    }

    /**
     * Requires that the currently logged in user can administer the course.
     *
     * @param  Course  $course
     *
     * @return boolean
     */
    private function requirePermissions(Course $course)
    {
        $this->permissionsService->requireCourseManagementCapability($course->id);
    }
}
