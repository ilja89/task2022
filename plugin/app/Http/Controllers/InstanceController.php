<?php

namespace TTU\Charon\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Events\CharonDeleted;
use TTU\Charon\Events\CharonUpdated;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CreateCharonService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\UpdateCharonService;
use Zeizig\Moodle\Services\FileUploadService;
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

    /** @var CharonRepository */
    protected $charonRepository;

    /** @var GradebookService */
    protected $gradebookService;

    /** @var GrademapService */
    protected $grademapService;

    /** @var CreateCharonService */
    protected $createCharonService;

    /** @var UpdateCharonService */
    protected $updateCharonService;

    /** @var FileUploadService */
    private $fileUploadService;

    /**
     * InstanceController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  GradebookService $gradebookService
     * @param  GrademapService $grademapService
     * @param  CreateCharonService $createCharonService
     * @param  UpdateCharonService $updateCharonService
     * @param FileUploadService $fileUploadService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        GradebookService $gradebookService,
        GrademapService $grademapService,
        CreateCharonService $createCharonService,
        UpdateCharonService $updateCharonService,
        FileUploadService $fileUploadService
    ) {
        parent::__construct($request);
        $this->charonRepository    = $charonRepository;
        $this->gradebookService    = $gradebookService;
        $this->grademapService     = $grademapService;
        $this->createCharonService = $createCharonService;
        $this->updateCharonService = $updateCharonService;
        $this->fileUploadService   = $fileUploadService;
    }

    /**
     * Store a new task instance.
     *
     * @return integer new task ID
     */
    public function store()
    {
        $charon = $this->getCharonFromRequest();
        $charon->category_id = $this->createCharonService->addCategoryForCharon($charon, $this->request['course']);

        if ( ! $this->charonRepository->save($charon)) {
            return null;
        }

        $this->createCharonService->saveGrademapsFromRequest($this->request, $charon);
        $this->createCharonService->saveDeadlinesFromRequest($this->request, $charon);

        event(new CharonCreated($charon));

        return $charon->id;
    }

    /**
     * Updates the given plugin instance.
     * Takes the information from the request.
     *
     * If the update was successful returns true.
     *
     * @return bool
     * @throws \TTU\Charon\Exceptions\CharonNotFoundException
     */
    public function update()
    {
        $charon = $this->charonRepository->getCharonByCourseModuleId($this->request->update);

        if ($this->charonRepository->update($charon, $this->getCharonFromRequest())) {

            $oldDeadlineEventIds = $charon->deadlines->pluck('event_id');

            $deadlinesUpdated = $this->updateCharonService->updateDeadlines($this->request, $charon);
            $this->updateCharonService->updateGrademaps(
                $this->request->grademaps,
                $charon,
                $deadlinesUpdated,
                $this->request->input('recalculate_grades')
            );

            event(new CharonUpdated($charon, $oldDeadlineEventIds));
        }

        return "1";
    }

    /**
     * Deletes the plugin instance with given id.
     *
     * @param $charonId
     *
     * @return bool true if instance was deleted successfully
     *
     * @throws \Exception
     */
    public function destroy($charonId)
    {
        return $this->charonRepository->deleteByInstanceId($charonId);
    }

    /**
     * Run after the course module has been created for the Charon instance.
     * This means that all Grade Items and Grade Categories have been created and can
     * be accessed, moved around and changed.
     *
     * @param  integer $charonId
     *
     * @return void
     */
    public function postCourseModuleCreated($charonId)
    {
        $this->postCourseModuleCreatedOrUpdated($charonId);

        $charon = $this->charonRepository->getCharonById($charonId);
        $charon->description = $this->saveDescriptionFiles($charon);
        $charon->save();
    }

    /**
     * Run after the course module has been updated for the Charon instance.
     * This means that all Grade Items and Grade Categories have been created and can
     * be accessed, moved around and changed.
     *
     * @param  integer $charonId
     *
     * @return void
     */
    public function postCourseModuleUpdated($charonId)
    {
        $this->postCourseModuleCreatedOrUpdated($charonId);
    }

    /**
     * Called when the Charon course module has been created or updated.
     * Groups identical functionality from both.
     *
     * @param  integer $charonId
     *
     * @return void
     */
    private function postCourseModuleCreatedOrUpdated($charonId)
    {
        $charon = $this->charonRepository->getCharonById($charonId);
        $this->grademapService->linkGrademapsAndGradeItems($charon);

        foreach ($charon->grademaps as $grademap) {
            $this->gradebookService->moveGradeItemToCategory($grademap->grade_item_id, $charon->category_id);
        }

        $this->updateCharonService->updateCategoryCalculationAndMaxScore($charon, $this->request);
    }

    /**
     * Gets the charon from the current request.
     *
     * @return Charon
     */
    private function getCharonFromRequest()
    {
        return new Charon([
            'name'                => $this->request['name'],
            'description'         => $this->request['description']['text'],
            'project_folder'      => $this->request['project_folder'],
            'tester_extra'        => $this->request->input('tester_extra', ''),
            'system_extra'        => $this->request->input('system_extra', ''),
            'tester_type_code'    => $this->request['tester_type'],
            'grading_method_code' => $this->request['grading_method'],
            'timemodified'        => Carbon::now()->timestamp,
            'course'              => $this->request['course'],
        ]);
    }

    /**
     * Saves files from Charon's description.
     *
     * @param  Charon  $charon
     *
     * @return string
     */
    private function saveDescriptionFiles(Charon $charon)
    {
        $newDescription = $this->fileUploadService->savePluginFiles(
            $charon->description,
            'description',
            $charon->courseModule()->id
        );

        return $newDescription;
    }
}
