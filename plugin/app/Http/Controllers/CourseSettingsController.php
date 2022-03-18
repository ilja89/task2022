<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\GitlabLocationType;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Services\PlagiarismCommunicationService;
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

    /**
     * CourseSettingsController constructor.
     *
     * @param Request $request
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param PlagiarismCommunicationService $plagiarismCommunicationService
     */
    public function __construct(Request $request, CourseSettingsRepository $courseSettingsRepository, PlagiarismCommunicationService $plagiarismCommunicationService)
    {
        parent::__construct($request);
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
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

        $courseSettings = $this->addPlagiarismSettingsThatExist($courseSettings);

        if ($this->allPlagiarismSettingsExist()) {
            $this->createOrUpdateInPlagiarism($course, $courseSettings);
        }

        $courseSettings->save();

        return app('redirect')->action('CourseSettingsFormController@index', ['course' => $course->id]);
    }

    /**
     * Get the course settings if exists or create new settings if doesn't.
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

    /**
     * Check if plagiarism settings were set on the form
     * @return bool
     */
    private function allPlagiarismSettingsExist(): bool
    {
        return (
            $this->request['plagiarism_language_type_code'] &&
            $this->request['plagiarism_gitlab_group'] &&
            $this->request['gitlab_location_type_code'] &&
            $this->request['plagiarism_file_extensions'] &&
            $this->request['plagiarism_moss_passes'] &&
            $this->request['plagiarism_moss_matches_shown']
        );
    }

    /**
     * Check for settings that were set (are not null) and add them to course settings to be saved
     * @param $courseSettings
     * @return mixed
     */
    private function addPlagiarismSettingsThatExist($courseSettings)
    {
        if ($this->request['plagiarism_language_type_code']) {
            $courseSettings->plagiarism_language_type_code = $this->request['plagiarism_language_type_code'];
        }
        if ($this->request['plagiarism_gitlab_group']) {
            $courseSettings->plagiarism_gitlab_group = $this->request['plagiarism_gitlab_group'];
        }
        if ($this->request['gitlab_location_type_code']) {
            $courseSettings->gitlab_location_type_code = $this->request['gitlab_location_type_code'];
        }
        if ($this->request['plagiarism_file_extensions']) {
            $courseSettings->plagiarism_file_extensions = $this->request['plagiarism_file_extensions'];
        }
        if ($this->request['plagiarism_moss_passes']) {
            $courseSettings->plagiarism_moss_passes = $this->request['plagiarism_moss_passes'];
        }
        if ($this->request['plagiarism_moss_matches_shown']) {
            $courseSettings->plagiarism_moss_matches_shown = $this->request['plagiarism_moss_matches_shown'];
        }
        return $courseSettings;
    }

    /**
     * Format data to fit Django model field names and structure
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function createOrUpdateInPlagiarism($course, $courseSettings)
    {
        $projectsLocation = GitlabLocationType::where('code', $courseSettings->gitlab_location_type_code)->value('name');
        $projectsLocation = str_replace(' ', '_', strtolower($projectsLocation));

        $this->plagiarismCommunicationService->createOrUpdateCourse([
            'name' => $course->shortname,
            'charon_identifier' => $course->id,
            'language' => $this->request['plagiarism_language_type_code'],
            'group_name' => $this->request['plagiarism_gitlab_group'],
            'projects_location' => $projectsLocation,
            'file_extensions' => '{' . $this->request['plagiarism_file_extensions'] . '}',
            'max_passes' => $this->request['plagiarism_moss_passes'],
            'number_shown' => $this->request['plagiarism_moss_matches_shown']
        ]);
    }
}
