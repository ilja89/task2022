<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\ClassificationsRepository;

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

    /**
     * InstanceFormController constructor.
     *
     * @param  CharonRepository $charonRepository
     * @param ClassificationsRepository $classificationsRepository
     */
    public function __construct(CharonRepository $charonRepository, ClassificationsRepository $classificationsRepository)
    {
        $this->charonRepository = $charonRepository;
        $this->classificationsRepository = $classificationsRepository;
    }

    /**
     * Renders the instance form when creating a new instance.
     *
     * @param  Request  $request
     *
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $gradeTypes = $this->classificationsRepository->getAllGradeTypes();
        $gradingMethods = $this->classificationsRepository->getAllGradingMethods();
        $testerTypes = $this->classificationsRepository->getAllTesterTypes();

        if ($this->isUpdate($request)) {
            $charon = $this->getCharon($request->update);

            return view('instanceForm.form', compact('charon', 'gradeTypes', 'gradingMethods', 'testerTypes'));
        }

        return view('instanceForm.form', compact(['gradeTypes', 'gradingMethods', 'testerTypes']));
    }

    /**
     * Check if the current request is an update request.
     *
     * @param  Request  $request
     *
     * @return bool
     */
    private function isUpdate($request)
    {
        return isset($request->update);
    }

    /**
     * Gets the charon instance with the given course module id.
     *
     * @param  integer  $courseModuleId
     *
     * @return Charon
     */
    private function getCharon($courseModuleId)
    {
        return $this->charonRepository->getCharonByCourseModuleId($courseModuleId);
    }
}
