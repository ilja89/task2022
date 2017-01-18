<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class InstanceController.
 * Controls everything to do with updating/adding/deleting the plugin instance.
 * Basic CRUD operations.
 *
 * @package TTU\Charon\Http\Controllers
 */
class InstanceController extends Controller
{
    /** @var Request */
    protected $request;

    /** @var CharonRepository */
    protected $charonRepository;

    /** @var GradebookService */
    protected $gradebookService;

    /** @var GrademapService */
    protected $grademapService;

    /**
     * InstanceController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param GradebookService $gradebookService
     * @param GrademapService $grademapService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        GradebookService $gradebookService,
        GrademapService $grademapService
    ) {
        $this->request          = $request;
        $this->charonRepository = $charonRepository;
        $this->gradebookService = $gradebookService;
        $this->grademapService  = $grademapService;
    }

    /**
     * Store a new task instance.
     *
     * @return integer new task ID
     */
    public function store()
    {
        $charon = $this->getCharonFromRequest();

        if ( ! $this->charonRepository->save($charon)) {
            return null;
        }

        $this->saveGrademapsFromRequest($charon);

        return $charon->id;
    }

    /**
     * Updates the given plugin instance.
     * Takes the information from the request.
     *
     * If the update was successful returns true.
     *
     * @return bool
     */
    public function update()
    {
        $charon = $this->charonRepository->getCharonByCourseModuleId($this->request->update);

        return $this->charonRepository->update($charon, $this->getCharonFromRequest());
    }

    /**
     * Deletes the plugin instance with given id.
     *
     * @param  integer $id
     *
     * @return bool true if instance was deleted successfully
     */
    public function destroy($id)
    {
        return $this->charonRepository->deleteByInstanceId($id);
    }

    /**
     * Run after the course module has been created for the Charon instance.
     * This means that all Grade Items and Grade Categories have been created and can
     * be accessed, moved around and changed.
     *
     * @param  integer $charonId
     */
    public function postCourseModuleCreated($charonId)
    {
        $charon = $this->charonRepository->getCharonById($charonId);
        $this->grademapService->linkGrademapsAndGradeItems($charon);
    }

    /**
     * Gets the charon from the current request.
     *
     * @return Charon
     */
    private function getCharonFromRequest()
    {
        return new Charon([
            'name'                => $this->request->name,
            'description'         => $this->request->description['text'],
            'project_folder'      => $this->request->project_folder,
            'extra'               => $this->request->extra,
            'tester_type_code'    => $this->request->tester_type,
            'grading_method_code' => $this->request->grading_method,
        ]);
    }

    /**
     * Save Grademaps from the current request.
     * Assumes that these request parameters are set:
     *      grademaps (where tester_type_code => grademap)
     *          grademap_name
     *          max_points
     *          id_number
     *      course (automatically done by Moodle after submitting form)
     *
     * @param Charon $charon
     */
    private function saveGrademapsFromRequest(Charon $charon)
    {
        foreach ($this->request->grademaps as $grade_type_code => $grademap) {
            /** @var GradeItem $gradeItem */
            $this->gradebookService->addGradeItem(
                $this->request->course,
                $charon->id,
                $grade_type_code,
                $grademap['grademap_name'],
                $grademap['max_points'],
                $grademap['id_number']
            );

            // We cannot add Grade Item ID here because it is not yet in the database (Moodle is great!)
            // Instead we can use event listeners (db/events.php) and wait for them to be added.
            $charon->grademaps()->save(new Grademap([
                'grade_type_code' => $grade_type_code,
                'name'            => $grademap['grademap_name'],
            ]));
        }
    }
}
