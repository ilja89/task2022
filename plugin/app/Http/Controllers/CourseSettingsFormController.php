<?php

namespace TTU\Charon\Http\Controllers;

use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Repositories\CourseSettingsRepository;
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
    /** @var Output */
    protected $output;

    /** @var Page */
    protected $page;

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /**
     * CourseSettingsFormController constructor.
     *
     * @param  Output $output
     * @param Page $page
     * @param CourseSettingsRepository $courseSettingsRepository
     *
     * @internal param Page $page
     */
    public function __construct(
        Output $output,
        Page $page,
        CourseSettingsRepository $courseSettingsRepository
    ) {
        $this->output                   = $output;
        $this->page                     = $page;
        $this->courseSettingsRepository = $courseSettingsRepository;
    }

    /**
     * Renders the course settings form.
     *
     * @param  Course $course
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Course $course)
    {
        $this->addBreadcrumbs($course);

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);

        return view('course_settings_form.form', [
            'header'    => $this->output->header(),
            'footer'    => $this->output->footer(),
            'settings'  => $courseSettings,
            'course_id' => $course->id,
        ]);
    }

    /**
     * Add breadcrumbs to the page.
     * Uses Moodle built in breadcrumbs.
     *
     * @param  Course $course
     *
     * @return void
     */
    public function addBreadcrumbs(Course $course)
    {
        $this->page->addBreadcrumb(
            $course->shortname,
            '/course/view.php?id=' . $course->id
        );
    }
}
