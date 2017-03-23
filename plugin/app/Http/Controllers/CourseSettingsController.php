<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Repositories\CourseSettingsRepository;
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

    /**
     * CourseSettingsController constructor.
     *
     * @param  Request  $request
     * @param  CourseSettingsRepository  $courseSettingsRepository
     */
    public function __construct(Request $request, CourseSettingsRepository $courseSettingsRepository)
    {
        parent::__construct($request);
        $this->courseSettingsRepository = $courseSettingsRepository;
    }

    /**
     * Stores the course settings from the request.
     *
     * @param  Course  $course
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Course $course)
    {
        $courseSettings = $this->getCourseSettings($course);

        $courseSettings->unittests_git = $this->request['unittests_git'];
        $courseSettings->tester_type_code = $this->request['tester_type'];
        $courseSettings->save();

        return app('redirect')->action('CourseSettingsFormController@index', ['course' => $course->id]);
    }

    /**
     * Get the course settings if exists or create new settings if doesn't.
     *
     * @param  Course $course
     *
     * @return CourseSettings
     */
    private function getCourseSettings(Course $course)
    {
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);

        if ($courseSettings === null) {
            $courseSettings            = new CourseSettings();
            $courseSettings->course_id = $course->id;
        }

        return $courseSettings;
    }
}
