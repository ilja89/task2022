<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Repositories\ClassificationsRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Services\PlagiarismCommunicationService;
use TTU\Charon\Repositories\PresetsRepository;
use Zeizig\Moodle\Globals\Output;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Models\Course;

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

    /** @var PresetsRepository */
    private $presetsRepository;

    /** @var PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /**
     * CourseSettingsFormController constructor.
     *
     * @param Request $request
     * @param Output $output
     * @param Page $page
     * @param CourseSettingsRepository $courseSettingsRepository
     *
     * @param ClassificationsRepository $classificationsRepository
     *
     * @param PresetsRepository $presetsRepository
     *
     * @param PlagiarismCommunicationService $plagiarismCommunicationService
     *
     * @internal param Page $page
     */
    public function __construct(
        Request $request,
        Output $output,
        Page $page,
        CourseSettingsRepository $courseSettingsRepository,
        ClassificationsRepository $classificationsRepository,
        PresetsRepository $presetsRepository,
        PlagiarismCommunicationService $plagiarismCommunicationService
    ) {
        parent::__construct($request);
        $this->output = $output;
        $this->page = $page;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->classificationsRepository = $classificationsRepository;
        $this->presetsRepository = $presetsRepository;
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
    }

    /**
     * Renders the course settings form.
     *
     * @param Course $course
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(Course $course)
    {
        $this->setUrl($course->id);

        return view('course_settings_form.form', [
            'header' => $this->output->header(),
            'footer' => $this->output->footer(),
            'settings' => $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id),
            'course' => $course,
            'tester_types' => $this->classificationsRepository->getAllTesterTypes(),
            'grading_methods' => $this->classificationsRepository->getAllGradingMethods(),
            'grade_name_prefixes' => $this->classificationsRepository->getAllGradeNamePrefixes(),
            'presets' => $this->presetsRepository->getPresetsOnlyForCourse($course->id),
            'plagiarism_settings' => $this->plagiarismCommunicationService->getCourseDetails($course)
        ]);
    }

    /**
     * Sets the URL. Needed by Moodle.
     *
     * @param integer $courseId
     */
    private function setUrl($courseId)
    {
        global $PAGE;
        $PAGE->set_url('/mod/charon/courses/' . $courseId . '/settings', []);
    }
}
