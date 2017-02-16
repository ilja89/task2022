<?php

namespace TTU\Charon\Http\Controllers;

use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Repositories\ClassificationsRepository;
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

    /** @var ClassificationsRepository */
    private $classificationsRepository;

    /**
     * CourseSettingsFormController constructor.
     *
     * @param  Output $output
     * @param Page $page
     * @param CourseSettingsRepository $courseSettingsRepository
     *
     * @param ClassificationsRepository $classificationsRepository
     *
     * @internal param Page $page
     */
    public function __construct(
        Output $output,
        Page $page,
        CourseSettingsRepository $courseSettingsRepository,
        ClassificationsRepository $classificationsRepository
    ) {
        $this->output                   = $output;
        $this->page                     = $page;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->classificationsRepository = $classificationsRepository;
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
        $this->setUrl($course->id);

        $this->addBreadcrumbs($course);

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);
        $testerTypes = $this->classificationsRepository->getAllTesterTypes();

        return view('course_settings_form.form', [
            'header'    => $this->output->header(),
            'footer'    => $this->output->footer(),
            'settings'  => $courseSettings,
            'course_id' => $course->id,
            'tester_types' => $testerTypes,
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

    /**
     * Sets the URL. Needed by Moodle.
     *
     * @param  integer  $courseId
     */
    private function setUrl($courseId)
    {
        global $PAGE;
        $PAGE->set_url('/mod/charon/courses/' . $courseId . '/settings', []);
    }
}
