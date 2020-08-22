<?php

namespace TTU\Charon\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Events\CharonUpdated;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CreateCharonService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\PlagiarismService;
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

    /** @var PlagiarismService */
    private $plagiarismService;

    /**
     * InstanceController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     * @param GradebookService $gradebookService
     * @param GrademapService $grademapService
     * @param CreateCharonService $createCharonService
     * @param UpdateCharonService $updateCharonService
     * @param FileUploadService $fileUploadService
     * @param PlagiarismService $plagiarismService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        GradebookService $gradebookService,
        GrademapService $grademapService,
        CreateCharonService $createCharonService,
        UpdateCharonService $updateCharonService,
        FileUploadService $fileUploadService,
        PlagiarismService $plagiarismService
    )
    {
        parent::__construct($request);
        $this->charonRepository = $charonRepository;
        $this->gradebookService = $gradebookService;
        $this->grademapService = $grademapService;
        $this->createCharonService = $createCharonService;
        $this->updateCharonService = $updateCharonService;
        $this->fileUploadService = $fileUploadService;
        $this->plagiarismService = $plagiarismService;
    }

    /**
     * Store a new task instance.
     *
     * @return int - new task ID
     *
     * @throws GuzzleException
     * @throws \Exception
     */
    public function store()
    {

        global $DB;

        try {

            $sql = "BEGIN TRANSACTION STORE_CHARON";
            $DB->execute($sql);

            $charon = $this->getCharonFromRequest();
            $charon->category_id = $this->createCharonService->addCategoryForCharon(
                $charon,
                $this->request->input('course')
            );

            if (!$this->charonRepository->save($charon)) {
                return null;
            }

            $this->createCharonService->saveGrademapsFromRequest($this->request, $charon);
            $this->createCharonService->saveDeadlinesFromRequest($this->request, $charon);

            event(new CharonCreated($charon));

            Log::info("Has plagarism enabled: ", [$this->request->input('plagiarism_enabled')]);
            if ($this->request->input('plagiarism_enabled')) {
                $charon = $this->plagiarismService->createChecksuiteForCharon(
                    $charon,
                    $this->request->input('plagiarism_services'),
                    $this->request->input('resource_providers'),
                    $this->request->input('plagiarism_includes')
                );
            }

            $sql = "COMMIT TRANSACTION STORE_CHARON";
            $DB->execute($sql);

            return $charon->id;

        } catch (\Exception $e) {
            Log::info('CAUGHT AN EXCEPTION 2');
            $sql = "ROLLBACK TRANSACTION STORE_CHARON";
            $DB->execute($sql);

            throw $e;
        }
    }

    /**
     * Updates the given plugin instance. Takes the information from the
     * request.
     *
     * Returns "1" if the update was successful so that Moodle can understand
     * that it was successful, throws exceptions otherwise.
     *
     * @return string
     *
     * @throws \TTU\Charon\Exceptions\CharonNotFoundException
     * @throws \Exception
     */
    public function update()
    {

        global $DB;

        try {

            $sql = "BEGIN TRANSACTION UPDATE_CHARON";
            $DB->execute($sql);

            $charon = $this->charonRepository->getCharonByCourseModuleId($this->request->input('update'));
            Log::info("Update charon", [$charon]);

            if ($this->charonRepository->update($charon, $this->getCharonFromRequest())) {

                $deadlinesUpdated = $this->updateCharonService->updateDeadlines($this->request, $charon);
                $this->updateCharonService->updateGrademaps(
                    $this->request->input('grademaps'),
                    $charon,
                    $deadlinesUpdated,
                    $this->request->input('recalculate_grades')
                );

                // TODO: Plagiarism
            }

            $sql = "COMMIT TRANSACTION UPDATE_CHARON";
            $DB->execute($sql);

            return "1";

        } catch (\Exception $e) {
            Log::info('CAUGHT AN EXCEPTION!');
            $sql = "ROLLBACK TRANSACTION UPDATE_CHARON";
            $DB->execute($sql);

            throw $e;
        }

    }

    /**
     * Deletes the plugin instance with given id.
     *
     * @param int $charonId
     *
     * @return bool true if instance was deleted successfully
     *
     * @throws \Exception
     */
    public function destroy($charonId)
    {
        Log::info("Delete charon ", [$charonId]);
        return $this->charonRepository->deleteByInstanceId($charonId);
    }

    /**
     * Run after the course module has been created for the Charon instance.
     * This means that all Grade Items and Grade Categories have been created
     * and can be accessed, moved around and changed.
     *
     * @param int $charonId
     *
     * @return void
     */
    public function postCourseModuleCreated($charonId)
    {
        Log::info("postCourseModuleCreated: ", [$charonId]);
        $this->postCourseModuleCreatedOrUpdated($charonId);

        $charon = $this->charonRepository->getCharonById($charonId);
        $charon->description = $this->saveDescriptionFiles($charon);
        $charon->save();
    }

    /**
     * Run after the course module has been updated for the Charon instance.
     * This means that all Grade Items and Grade Categories have been created
     * and can be accessed, moved around and changed.
     *
     * @param integer $charonId
     *
     * @return void
     */
    public function postCourseModuleUpdated($charonId)
    {
        Log::info("postCourseModuleUpdated: ", [$charonId]);
        $this->postCourseModuleCreatedOrUpdated($charonId);
    }

    /**
     * Called when the Charon course module has been created or updated. Groups
     * identical functionality from both.
     *
     * @param integer $charonId
     *
     * @return void
     */
    private function postCourseModuleCreatedOrUpdated($charonId)
    {
        $charon = $this->charonRepository->getCharonById($charonId);
        $this->grademapService->linkGrademapsAndGradeItems($charon);

        foreach ($charon->grademaps as $grademap) {
            $this->gradebookService->moveGradeItemToCategory(
                $grademap->grade_item_id,
                $charon->category_id
            );
        }

        $this->updateCharonService->updateCategoryCalculationAndMaxScore(
            $charon,
            $this->request
        );
    }

    /**
     * Gets the charon from the current request.
     *
     * @return Charon
     */
    private function getCharonFromRequest()
    {
        $testerExtra = $this->request->input('tester_extra', '');
        if ($testerExtra === null) {
            $testerExtra = '';
        }

        $systemExtra = $this->request->input('system_extra', '');
        if ($systemExtra === null) {
            $systemExtra = '';
        }

        return new Charon([
            'name' => $this->request->input('name'),
            'description' => $this->request->input('description')['text'],
            'project_folder' => $this->request->input('project_folder'),
            'tester_type_code' => $this->request->input('tester_type'),
            'grading_method_code' => $this->request->input('grading_method'),
            'grouping_id' => $this->request->input('grouping_id'),
            'defense_deadline' => $this->request->input('defense_deadline'),
            'defense_duration' => $this->request->input('defense_duration'),
            'choose_teacher' => $this->request->input('choose_teacher'),
            'timemodified' => Carbon::now()->timestamp,
            'course' => $this->request->input('course'),
            'tester_extra' => $testerExtra,
            'system_extra' => $systemExtra,
        ]);
    }

    /**
     * Saves files from Charon's description.
     *
     * @param Charon $charon
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
