<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\GitlabLocationType;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Services\PlagiarismCommunicationService;
use TTU\Charon\Services\PlagiarismService;
use Zeizig\Moodle\Models\Course;

/**
 * Class CourseSettingsController.
 * Handles updating the course settings.
 *
 * @package TTU\Charon\Http\Controllers
 */
class CourseSettingsController extends Controller
{
    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /** @var PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /** @var PlagiarismService  */
    private $plagiarismService;

    /**
     * CourseSettingsController constructor.
     *
     * @param Request $request
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param PlagiarismCommunicationService $plagiarismCommunicationService
     * @param PlagiarismService $plagiarismService
     */
    public function __construct(Request $request, CourseSettingsRepository $courseSettingsRepository, PlagiarismCommunicationService $plagiarismCommunicationService, PlagiarismService $plagiarismService)
    {
        parent::__construct($request);
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
        $this->plagiarismService = $plagiarismService;
    }

    /**
     * Stores the course settings from the request.
     *
     * @param Course $course
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(Course $course)
    {
        $courseSettings = $this->getCourseSettings($course);

        $courseSettings->unittests_git = $this->request['unittests_git'];
        $courseSettings->tester_type_code = $this->request['tester_type_code'];
        $courseSettings->tester_url = $this->request['tester_url'];
        $courseSettings->tester_sync_url = $this->request['tester_sync_url'];
        $courseSettings->tester_token = $this->request['tester_token'];

        $this->plagiarismService->createOrUpdateCourse($course, $this->request);

        $courseSettings->save();

        return app('redirect')->action('CourseSettingsFormController@index', ['course' => $course->id]);
    }

    /**
     * Get the Moodle course settings if exists or create new settings if not.
     *
     * @param Course $course
     *
     * @return CourseSettings
     */
    public function getCourseSettings(Course $course)
    {
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);

        if ($courseSettings === null) {
            $courseSettings = new CourseSettings();
            $courseSettings->course_id = $course->id;
        }

        return $courseSettings;
    }
}
