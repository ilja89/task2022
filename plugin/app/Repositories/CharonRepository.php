<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\CharonNotFoundException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
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
    private $moduleService;

    /** @var GradebookService */
    private $gradebookService;

    /** @var FileUploadService */
    private $fileUploadService;

    /** @var CharonDefenseLabRepository */
    private $charonDefenseLabRepository;

    /** @var LabRepository */
    private $labRepository;

    /**
     * CharonRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param FileUploadService $fileUploadService
     * @param GradebookService $gradebookService
     * @param CharonDefenseLabRepository $charonDefenseLabRepository
     * @param LabRepository $labRepository
     */
    public function __construct(
        ModuleService $moduleService,
        FileUploadService $fileUploadService,
        GradebookService $gradebookService,
        CharonDefenseLabRepository $charonDefenseLabRepository,
        LabRepository $labRepository
    )
    {
        $this->moduleService = $moduleService;
        $this->fileUploadService = $fileUploadService;
        $this->gradebookService = $gradebookService;
        $this->charonDefenseLabRepository = $charonDefenseLabRepository;
        $this->labRepository = $labRepository;
    }

    /**
     * @return Builder|Charon
     */
    public function query()
    {
        return Charon::query();
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
     * @return Collection|static[]
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
     * @param integer $courseModuleId
     *
     * @return Charon
     * @throws CharonNotFoundException
     */
    public function getCharonByCourseModuleId(int $courseModuleId)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($courseModuleId);

        if ($courseModule === null || !$courseModule->isInstanceOfPlugin()) {
            throw new CharonNotFoundException('charon_course_module_not_found', $courseModuleId);
        }

        return Charon::where('id', $courseModule->instance)
            ->first();
    }

    /**
     * Gets a charon instance with eagerly loaded fields like tester type and grading method by
     * course module id.
     *
     * @param integer $courseModuleId
     *
     * @return Charon
     * @throws CharonNotFoundException
     */
    public function getCharonByCourseModuleIdEager(int $courseModuleId)
    {
        $courseModule = CourseModule::find($courseModuleId);

        if ($courseModule === null || !$courseModule->isInstanceOfPlugin()) {
            throw new CharonNotFoundException('charon_course_module_not_found', $courseModuleId);
        }

        return Charon::with('defenseLabs', 'testerType', 'gradingMethod', 'grademaps.gradeItem', 'deadlines', 'deadlines.group', 'grouping', 'templates')
            ->where('id', $courseModule->instance)
            ->first();
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
        $id = intval($id);
        if (intval($id) == 0) {
            return false;
        }

        /** @var Charon $charon */
        $charon = Charon::find($id);

        GradeItem::where('itemtype', 'mod')
            ->where('itemmodule', config('moodle.plugin_slug'))
            ->where('iteminstance', $id)
            ->delete();
        Grademap::where('charon_id', $id)->delete();
        Deadline::where('charon_id', $id)->delete();
        CharonDefenseLab::where('charon_id', $id)->delete();

        if ($charon != null) {

            $result = $charon->delete();

            $this->gradebookService->deleteGradeCategory(
                $charon->category_id,
                $charon->course
            );

            return $result;
        }

        return false;
    }

    /**
     * Takes the old instance and override its values with the new Charon values.
     *
     * @param Charon $oldCharon
     * @param array $newCharon
     *
     * @return boolean
     */
    public function update($oldCharon, $newCharon)
    {
        $modifiableFields = ['name', 'project_folder', 'tester_extra', 'system_extra', 'tester_type_code',
            'grouping_id', 'allow_submission'];

        if (array_key_exists('allow_submission', $newCharon) and $newCharon['allow_submission'] == 'true') {
            $newCharon['allow_submission'] = 1;
        } else {
            $newCharon['allow_submission'] = 0;
        }

        $charon = $this->saveCharon($oldCharon, $newCharon, $modifiableFields);

        $charon->description = $this->fileUploadService->savePluginFiles(
            $newCharon['description']['text'],
            'description',
            $oldCharon->courseModule()->id
        );

        return $oldCharon->save();
    }

    /**
     * Find all Charons in course with given identifier. Also loads deadlines,
     * grademaps with grade items, and labs.
     *
     * @param integer $courseId
     *
     * @return Charon[]
     */
    public function findCharonsByCourse(int $courseId): array
    {
        $moduleId = $this->moduleService->getModuleId();

        $charons = Charon::leftJoin('course_modules', 'course_modules.instance', 'charon.id')
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
            ->get()
            ->all();

        foreach ($charons as $charon) {
            /** @var Charon $charon */
            $charon->defense_labs = $this->charonDefenseLabRepository->getDefenseLabsByCharonId($charon->id);
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
                ->get(['id', 'charon_id', 'grade_item_id', 'grade_type_code', 'name', 'persistent']);
            $charon->deadlines = Deadline::with([
                'group' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
                ->where('charon_id', $charon->id)
                ->get();

            $charon->labs = $this->labRepository->getLabsByCharonIdLaterEqualToday($charon->id);
        }

        return $charons;
    }

    /**
     * Find all ongoing and upcoming Charons in course with given id. Also include labs.
     *
     * @param integer $courseId
     *
     * @return Charon[]
     */
    public function findCharonsByCourseIdWithLabs(int $courseId): array
    {
        $charons = Charon::where('charon.course', $courseId)
            ->where('charon.defense_deadline', '>=', Carbon::now())
            ->select('charon.id', 'charon.name')
            ->get()
            ->all();

        foreach ($charons as $charon) {
            $charon->labs = $this->labRepository->getLabsByCharonIdLaterEqualToday($charon->id);
        }

        return $charons;
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
     * Save Charon with updated labs.
     *
     * @param Charon $charon
     * @param array $updated
     * @param array $modifiableFields
     *
     * @return Charon
     */
    public function saveCharon(Charon $charon, array $updated, array $modifiableFields)
    {
        $nullable_fields = ['docker_test_root', 'docker_content_root', 'system_extra', 'tester_extra'];

        foreach ($nullable_fields as $key) {
            if (Arr::has($modifiableFields, $key)) {
                if (isset($updated[$key]) && $updated[$key] != '') {
                    $charon[$key] = $updated[$key];
                } else {
                    $charon[$key] = null;
                }
            }
        }

        foreach ($modifiableFields as $key) {
            if (!Arr::has($nullable_fields, $key)) {
                if (isset($updated[$key])) {
                    $charon[$key] = $updated[$key];
                } else {
                    $charon[$key] = null;
                }
            }
        }

        $charon->save();

        if (isset($updated['defense_labs'])) {
            return $this->saveCharonLabs($charon, $updated);
        }

        return $charon;
    }

    /**
     * @param Charon $charon
     * @param array $updated
     * @return Charon
     */
    public function saveCharonLabs(Charon $charon, array $updated): Charon
    {
        $labs = $this->labRepository->getLabsIdsByCharonId($charon->id);

        $new_labs = [];
        if (isset($updated['defense_labs'])) {
            $new_labs = [];
            for ($i = 0; $i < count($updated['defense_labs']); $i++) {
                array_push($new_labs, $updated['defense_labs'][$i]["id"]);
            }
        }

        Log::info("Update charon labs:", [$new_labs]);

        foreach ($labs as $lab) {
            if (!in_array($lab, $new_labs)) {
                $this->labRepository->deleteLab($charon->id, $lab);
            }
        }

        foreach ($new_labs as $new_lab) {
            if (!in_array($new_lab, $labs)) {
                $this->labRepository->makeLab($charon->id, $new_lab);
            }
        }

        return $charon;
    }
}
