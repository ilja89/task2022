<?php

namespace TTU\Charon\Http\Controllers;

use Zeizig\Moodle\Globals\Output;
use Zeizig\Moodle\Globals\Page;
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

    /** @var Output */
    protected $output;

    /** @var Page */
    protected $page;

    /**
     * CourseSettingsFormController constructor.
     *
     * @param  PermissionsService $permissionsService
     * @param  Output $output
     *
     * @param Page $page
     *
     * @internal param Page $page
     */
    public function __construct(PermissionsService $permissionsService, Output $output, Page $page)
    {
        $this->permissionsService = $permissionsService;
        $this->output = $output;
        $this->page = $page;
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

        return view('vuetest', [
            'header' => $this->output->header(),
            'footer' => $this->output->footer()
        ]);
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

        return true;
    }
}
