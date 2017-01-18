<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\CharonRepository;
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

    /**
     * InstanceController constructor.
     *
     * @param  Request  $request
     * @param  CharonRepository  $charonRepository
     */
    public function __construct(Request $request, CharonRepository $charonRepository, GradebookService $gradebookService)
    {
        $this->request = $request;
        $this->charonRepository = $charonRepository;
        $this->gradebookService = $gradebookService;
    }

    /**
     * Store a new task instance.
     *
     * @return integer new task ID
     */
    public function store()
    {
        $charon = $this->getCharonFromRequest();

        if (!$this->charonRepository->save($charon)) {
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
     * @param  integer  $id
     *
     * @return bool true if instance was deleted successfully
     */
    public function destroy($id)
    {
        return $this->charonRepository->deleteByInstanceId($id);
    }

    /**
     * Gets the charon from the current request.
     * 
     * @return Charon
     */
    private function getCharonFromRequest()
    {
        return new Charon([
            'name' => $this->request->name,
            'description' => $this->request->description['text'],
            'project_folder' => $this->request->project_folder,
            'extra' => $this->request->extra,
            'tester_type_code' => $this->request->tester_type,
            'grading_method_code' => $this->request->grading_method
        ]);
    }

    /**
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

            $charon->grademaps()->save(new Grademap([
                'grade_type_code' => $grade_type_code,
                'name' => $grademap['grademap_name']
            ]));
        }
    }
}
