<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;

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

    /**
     * InstanceController constructor.
     *
     * @param  Request  $request
     * @param  CharonRepository  $charonRepository
     */
    public function __construct(Request $request, CharonRepository $charonRepository)
    {
        $this->request = $request;
        $this->charonRepository = $charonRepository;
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
        $charon->name = $this->request->name;
        $charon->description = $this->request->description['text'];

        return $this->charonRepository->save($charon);
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

    private function getCharonFromRequest()
    {
        return new Charon([
            'name' => $this->request->name,
            'description' => $this->request->description['text'],
            'project_folder' => $this->request->project_folder,
            'tester_type_code' => $this->request->tester_type,
            'grading_method_code' => 1
        ]);
    }
}
