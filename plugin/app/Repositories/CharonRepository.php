<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use TTU\Charon\Exceptions\CharonNotFoundException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\FileUploadService;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\ModuleService;

/**
 * Class CharonRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class CharonRepository
{
    /** @var ModuleService */
    protected $moduleService;

    /** @var GradebookService */
    protected $gradebookService;

    /** @var FileUploadService */
    private $fileUploadService;

    /**
     * @var CharonDefenseLabRepository
     */
    protected $charonDefenseLabRepository;

    /**
     * CharonRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param FileUploadService $fileUploadService
     * @param GradebookService $gradebookService
     * @param CharonDefenseLabRepository $charonDefenseLabRepository
     */
    public function __construct(ModuleService $moduleService, FileUploadService $fileUploadService,
                                GradebookService $gradebookService, CharonDefenseLabRepository $charonDefenseLabRepository)
    {
        $this->moduleService = $moduleService;
        $this->fileUploadService = $fileUploadService;
        $this->gradebookService = $gradebookService;
        $this->charonDefenseLabRepository = $charonDefenseLabRepository;
    }

    /**
     * Save the Charon instance.
     *
     * @param Charon $charon
     *
     * @return boolean
     */
    public function save($charon)
    {
        return $charon->save();
    }

    /**
     * Get all Charons.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCharons()
    {
        return Charon::all();
    }

    /**
     * Get an instance of Charon by its id.
     *
     * @param integer $id
     *
     * @return Charon
     */
    public function getCharonById($id)
    {
        return Charon::find($id);
    }

    /**
     * Gets a Charon instance by course module id.
     * Returns null if no course module is found or if the given course module is not a Charon.
     *
     * @param integer $id
     *
     * @return Charon
     * @throws CharonNotFoundException
     */
    public function getCharonByCourseModuleId($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule === null || !$courseModule->isInstanceOfPlugin()) {
            throw new CharonNotFoundException('charon_course_module_not_found', $id);
        }

        return Charon::where('id', $courseModule->instance)
            ->first();
    }

    /**
     * Gets a charon instance with eagerly loaded fields like tester type and grading method by
     * course module id.
     *
     * @param integer $id
     *
     * @return Charon
     * @throws CharonNotFoundException
     */
    public function getCharonByCourseModuleIdEager($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule === null || !$courseModule->isInstanceOfPlugin()) {
            throw new CharonNotFoundException('charon_course_module_not_found', $id);
        }
        $charon = Charon::with('testerType', 'gradingMethod', 'grademaps.gradeItem', 'deadlines', 'deadlines.group', 'grouping')
            ->where('id', $courseModule->instance)
            ->first();
        return $charon;
    }

    /**
     * Deletes the instance with given id.
     *
     * @param integer $id
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function deleteByInstanceId($id)
    {
        /** @var Charon $charon */
        $charon = Charon::find($id);

        GradeItem::where('itemtype', 'mod')
            ->where('itemmodule', config('moodle.plugin_slug'))
            ->where('iteminstance', $id)
            ->delete();
        Grademap::where('charon_id', $id)->delete();
        Deadline::where('charon_id', $id)->delete();
        CharonDefenseLab::where('charon_id', $id)->delete();

        $result = $charon->delete();

        $this->gradebookService->deleteGradeCategory(
            $charon->category_id,
            $charon->course
        );

        return $result;
    }

    /**
     * Takes the old instance and override its values with the new Charon values.
     *
     * @param Charon $oldCharon
     * @param Charon $newCharon
     *
     * @return boolean
     */
    public function update($oldCharon, $newCharon)
    {
        $oldCharon->name = $newCharon->name;
        $oldCharon->project_folder = $newCharon->project_folder;
        $oldCharon->tester_extra = $newCharon->tester_extra;
        $oldCharon->system_extra = $newCharon->system_extra;
        $oldCharon->tester_type_code = $newCharon->tester_type_code;
        $oldCharon->grading_method_code = $newCharon->grading_method_code;
        $oldCharon->grouping_id = $newCharon->grouping_id;
        $oldCharon->timemodified = Carbon::now()->timestamp;
        $oldCharon->defense_deadline = $newCharon->defense_deadline;
        $oldCharon->defense_start_time = $newCharon->defense_start_time;
        $oldCharon->defense_duration = $newCharon->defense_duration;
        $oldCharon->choose_teacher = $newCharon->choose_teacher;
        $oldCharon->defense_threshold = $newCharon->defense_threshold;
        $oldCharon->docker_timeout = $newCharon->docker_timeout;
        $oldCharon->docker_content_root = $newCharon->docker_content_root;
        $oldCharon->docker_test_root = $newCharon->docker_test_root;
        $oldCharon->group_size = $newCharon->group_size;

        $oldCharon->description = $this->fileUploadService->savePluginFiles(
            $newCharon->description,
            'description',
            $oldCharon->courseModule()->id
        );

        return $oldCharon->save();
    }

    /**
     * Find all Charons in course with given id. Also loads deadlines,
     * grademaps with grade items.
     *
     * @param integer $courseId
     *
     * @return Charon[]
     */
    public function findCharonsByCourse($courseId)
    {
        $moduleId = $this->moduleService->getModuleId();

        $charons = \DB::table('charon')
            ->leftJoin('course_modules', 'course_modules.instance', 'charon.id')
            ->join('charon_tester_type', 'charon.tester_type_code', 'charon_tester_type.code')
            ->where('charon.course', $courseId)
            ->where('course_modules.module', $moduleId)
            ->orWhereNull('course_modules.module')
            ->select(
                'charon.id',
                'charon.name',
                'charon_tester_type.name AS tester_type_name',
                'charon_tester_type.code AS tester_type_code',
                'charon.project_folder',
                'course_modules.id AS course_module_id',
                'charon.category_id',
                'charon.grouping_id',
                'charon.course',
                'charon.defense_deadline',
                'charon.defense_start_time',
                'charon.defense_duration',
                'charon.choose_teacher',
                'charon.defense_threshold',
                'charon.docker_timeout',
                'charon.docker_content_root',
                'charon.docker_test_root',
                'charon.group_size',
                'charon.tester_extra',
                'charon.system_extra'
            )
            ->orderBy('charon.name')
            ->get();

        foreach ($charons as $charon) {
            /** @var Charon $charon */
            $charon->charonDefenseLabs = $this->charonDefenseLabRepository->getDefenseLabsByCharonId($charon->id);
            $gradeItem = $this->gradebookService->getGradeItemByCategoryId($charon->category_id);
            $charon->calculation_formula = $gradeItem
                ? $this->gradebookService->denormalizeCalculationFormula(
                    $gradeItem->calculation,
                    $courseId
                )
                : '';

            $charon->grademaps = Grademap::with([
                'gradeItem' => function ($query) {
                    $query->select(['id', 'grademax']);
                },
            ])
                ->where('charon_id', $charon->id)
                ->get(['id', 'charon_id', 'grade_item_id', 'grade_type_code', 'name']);
            $charon->deadlines = Deadline::with([
                'group' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
                ->where('charon_id', $charon->id)
                ->get();
        }

        return $charons;
    }

    /**
     * Gets Charons by Charon and user.
     *
     * @param integer $charonId
     * @param integer $userId
     *
     * @return Submission[]
     */
    public function findSubmissionsByCharonAndUser($charonId, $userId)
    {
        return Submission::with('results', 'files')
            ->where('charon_id', $charonId)
            ->where('user_id', $userId)
            ->orderBy('git_timestamp', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find a Charon instance by a submission id for that Submission.
     *
     * @param int $submissionId
     *
     * @return Charon
     */
    public function findBySubmission($submissionId)
    {
        return Submission::where('id', $submissionId)
            ->with('charon')
            ->first()
            ->charon;
    }

    /**
     * Set the plagiarism checksuite id for the given Charon.
     *
     * @param Charon $charon
     * @param string $checksuiteId
     *
     * @return Charon
     */
    public function updatePlagiarismChecksuiteId(Charon $charon, $checksuiteId)
    {
        $charon->plagiarism_checksuite_id = $checksuiteId;
        $charon->save();

        return $charon;
    }

    /**
     * Save Charon defending stuff.
     *
     * @param Charon $charon
     *
     * @param array $updated
     * @return Charon
     */
    public function saveCharon(Charon $charon, array $updated)
    {
        if (isset($updated['defense_start_time'])) {
            $charon->defense_start_time = Carbon::parse($updated['defense_start_time'])->format('Y-m-d H:i:s');
        }
        if (isset($updated['defense_deadline'])) {
            $charon->defense_deadline = Carbon::parse($updated['defense_deadline'])->format('Y-m-d H:i:s');
        }

        $fields = [
            'defense_duration', 'defense_threshold', 'docker_timeout', 'docker_content_root', 'docker_test_root',
            'group_size', 'tester_extra', 'system_extra', 'tester_type_code', 'choose_teacher'
        ];

        foreach ($fields as $key => $value) {
            if (array_key_exists($key, $updated)) {
                $charon->{$key} = $updated[$key];
            }
        }

        $charon->save();

        DB::table('charon_defense_lab')
            ->where('charon_id', $charon->id)
            ->delete();

        for ($i = 0; $i < count($updated['defense_labs']); $i++) {
            $defenseLab = CharonDefenseLab::create([
                'lab_id' => $updated['defense_labs'][$i],
                'charon_id' => $charon->id
            ]);
            $defenseLab->save();
        }

        return $charon;
    }
}
