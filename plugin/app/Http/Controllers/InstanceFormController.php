<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\ClassificationsRepository;
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

    /**
     * InstanceFormController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  ClassificationsRepository $classificationsRepository
     * @param GradebookService $gradebookService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        ClassificationsRepository $classificationsRepository,
        GradebookService $gradebookService
    ) {
        $this->request                   = $request;
        $this->charonRepository          = $charonRepository;
        $this->classificationsRepository = $classificationsRepository;
        $this->gradebookService = $gradebookService;
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

        if ($this->isUpdate()) {
            $charon = $this->getCharon();

            if ($charon !== null) {
                return view('instanceForm.form', compact(
                    'charon', 'gradeTypes', 'gradingMethods', 'testerTypes'
                ));
            }
        }

        return view('instanceForm.form', compact(
            'gradeTypes', 'gradingMethods', 'testerTypes'
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
        $charon = $this->charonRepository->getCharonByCourseModuleIdEager($this->request->update);

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
