<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\ClassificationsRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\PresetsRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\SettingsService;

/**
 * Class InstanceFormController.
 * Renders the instance form when updating or editing the plugin.
 *
 * @package TTU\Charon\Http\Controllers
 */
class InstanceFormController extends Controller
{
    /** @var CharonRepository */
    protected $charonRepository;

    /** @var ClassificationsRepository */
    protected $classificationsRepository;

    /** @var GradebookService */
    private $gradebookService;

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /** @var PresetsRepository */
    private $presetsRepository;

    /** @var SettingsService */
    private $settingsService;

    /**
     * InstanceFormController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  ClassificationsRepository $classificationsRepository
     * @param GradebookService $gradebookService
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param PresetsRepository $presetsRepository
     * @param SettingsService $settingsService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        ClassificationsRepository $classificationsRepository,
        GradebookService $gradebookService,
        CourseSettingsRepository $courseSettingsRepository,
        PresetsRepository $presetsRepository,
        SettingsService $settingsService
    ) {
        parent::__construct($request);
        $this->charonRepository          = $charonRepository;
        $this->classificationsRepository = $classificationsRepository;
        $this->gradebookService = $gradebookService;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->presetsRepository = $presetsRepository;
        $this->settingsService = $settingsService;
    }

    /**
     * Renders the instance form when creating a new instance.
     *
     * @return Factory|View
     */
    public function index()
    {
        if ($this->isUpdate()) {
            return $this->edit($this->getCharon());
        }

        $course = Course::with('groups')
                        ->where('id', $this->request['course'])
                        ->first();
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);
        $courseSettingsUrl = $courseSettings && $courseSettings->unittests_git
            ? '' : "/mod/charon/courses/{$course->id}/settings";

        return view('instanceForm.form', [
            'gradingMethods' => $this->classificationsRepository->getAllGradingMethods(),
            'testerTypes' => $this->classificationsRepository->getAllTesterTypes(),
            'courseSettings' => $courseSettings,
            'presets' => $this->presetsRepository->getPresetsByCourse($course->id),
            'courseSettingsUrl' => $courseSettingsUrl,
            'moduleSettingsUrl' => $this->getModuleSettingsUrl(),
            'groups' => $course->groups,
            'plagiarismServices' => $this->classificationsRepository->getAllPlagiarismServices(),
        ]);
    }

    /**
     * Triggered by a post request to the form. This takes old values from the request and
     * sends them to the form.
     */
    public function postIndex()
    {
        $charon = new Charon([
            'name'                => $this->request['name'],
            'description'         => $this->request['description']['text'],
            'project_folder'      => $this->request['project_folder'],
            'extra'               => $this->request['extra'],
            'tester_type_code'    => $this->request['tester_type'],
            'grading_method_code' => $this->request['grading_method'],
        ]);

        $charon->grademaps = $this->request['grademaps'];
        $charon->deadlines = $this->request['deadlines'];
        $charon->max_score = $this->request['max_score'];
        $charon->calculation_formula = $this->request['calculation_formula'];

        $gradingMethods = $this->classificationsRepository->getAllGradingMethods();
        $testerTypes    = $this->classificationsRepository->getAllTesterTypes();
        $presets = $this->presetsRepository->getPresetsByCourse($this->request['course']);

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($this->request['course']);
        $courseSettingsUrl = $courseSettings && $courseSettings->unittests_git
            ? '' : "/mod/charon/courses/{$this->request['course']}/settings";
        $moduleSettingsUrl = $this->getModuleSettingsUrl();

        $update = true;
        $course = Course::with('groups')
                        ->where('id', $this->request['course'])
                        ->first();
        $groups = $course->groups;

        return view('instanceForm.form', compact(
            'charon', 'gradingMethods', 'testerTypes', 'update', 'courseSettings', 'presets', 'courseSettingsUrl',
            'moduleSettingsUrl', 'groups'
        ));
    }

    /**
     * Show the edit form for a given Charon.
     *
     * @param Charon $charon
     *
     * @return Factory|View
     */
    public function edit(Charon $charon)
    {
        $gradingMethods = $this->classificationsRepository->getAllGradingMethods();
        $testerTypes    = $this->classificationsRepository->getAllTesterTypes();

        $moduleSettingsUrl = $this->getModuleSettingsUrl();

        $presets = $this->presetsRepository->getPresetsByCourse($charon->course);
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($charon->course);
        $courseSettingsUrl = $courseSettings && $courseSettings->unittests_git
            ? '' : "/mod/charon/courses/{$charon->course}/settings";
        $groups = $charon->moodleCourse->groups;

        return view('instanceForm.form', compact(
            'charon', 'gradingMethods', 'testerTypes', 'courseSettings', 'presets', 'courseSettingsUrl',
            'moduleSettingsUrl', 'groups'
        ));
    }

    /**
     * Check if the current request is an update request.
     *
     * @return bool
     */
    private function isUpdate()
    {
        return isset($this->request->update);
    }

    /**
     * Gets the charon instance with the course module id from the request.
     *
     * @return Charon
     */
    private function getCharon()
    {
        $charon = $this->charonRepository->getCharonByCourseModuleIdEager($this->request['update']);

        if ($charon === null) {
            return null;
        }

        if ($charon->category_id !== null) {
            $gradeItem = $this->gradebookService->getGradeItemByCategoryId($charon->category_id);
            $charon->calculation_formula = $this->gradebookService->denormalizeCalculationFormula($gradeItem->calculation, $charon->course);
            $charon->max_score = $gradeItem->grademax;
        }

        return $charon;
    }

    /**
     * Get the module settings' URL. If tester URL does not exist, will return the
     * settings URL. If tester URL is set, will just return an empty string.
     *
     * @return string
     */
    private function getModuleSettingsUrl()
    {
        return $this->settingsService->getSetting('mod_charon', 'tester_url', null)
            ? ''
            : "/admin/settings.php?section=modsettingcharon";
    }
}
