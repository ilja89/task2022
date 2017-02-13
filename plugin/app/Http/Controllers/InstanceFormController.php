<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\ClassificationsRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use Zeizig\Moodle\Services\GradebookService;

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

    /** @var Request */
    protected $request;

    /** @var GradebookService */
    private $gradebookService;

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /**
     * InstanceFormController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  ClassificationsRepository $classificationsRepository
     * @param GradebookService $gradebookService
     * @param CourseSettingsRepository $courseSettingsRepository
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        ClassificationsRepository $classificationsRepository,
        GradebookService $gradebookService,
        CourseSettingsRepository $courseSettingsRepository
    ) {
        $this->request                   = $request;
        $this->charonRepository          = $charonRepository;
        $this->classificationsRepository = $classificationsRepository;
        $this->gradebookService = $gradebookService;
        $this->courseSettingsRepository = $courseSettingsRepository;
    }

    /**
     * Renders the instance form when creating a new instance.
     *
     * @return Factory|View
     */
    public function index()
    {
        $gradeTypes     = $this->classificationsRepository->getAllGradeTypes();
        $gradingMethods = $this->classificationsRepository->getAllGradingMethods();
        $testerTypes    = $this->classificationsRepository->getAllTesterTypes();
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($this->request['course']);

        if ($this->isUpdate()) {
            $charon = $this->getCharon();

            return view('instanceForm.form', compact(
                'charon', 'gradeTypes', 'gradingMethods', 'testerTypes', 'courseSettings'
            ));
        }

        return view('instanceForm.form', compact(
            'gradeTypes', 'gradingMethods', 'testerTypes', 'courseSettings'
        ));
    }

    /**
     *
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

        $gradeTypes     = $this->classificationsRepository->getAllGradeTypes();
        $gradingMethods = $this->classificationsRepository->getAllGradingMethods();
        $testerTypes    = $this->classificationsRepository->getAllTesterTypes();

        $update = true;

        return view('instanceForm.form', compact(
            'charon', 'gradeTypes', 'gradingMethods', 'testerTypes', 'update'
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
            $charon->calculation_formula = $gradeItem->calculation;
            $charon->max_score = $gradeItem->grademax;
        }

        return $charon;
    }
}
