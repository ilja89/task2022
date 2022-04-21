<?php

namespace TTU\Charon\Repositories;

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

    /** @var LabRepository */
    private $labRepository;

    /**
     * CharonRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param FileUploadService $fileUploadService
     * @param GradebookService $gradebookService
     * @param LabRepository $labRepository
     */
    public function __construct(
        ModuleService $moduleService,
        FileUploadService $fileUploadService,
        GradebookService $gradebookService,
        LabRepository $labRepository
    )
    {
        $this->moduleService = $moduleService;
        $this->fileUploadService = $fileUploadService;
        $this->gradebookService = $gradebookService;
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
            'grouping_id', 'allow_submission', 'defense_threshold', 'unittests_git', 'grading_method_code'];

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
     * Find all Charons in course with given id. Also loads deadlines,
     * grademaps with grade items.
     *
     * @param integer $courseId
     *
     * @return Charon[]
     */
    public function findCharonsByCourse(int $courseId)
    {
        $moduleId = $this->moduleService->getModuleId();

        $charonFields = [
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
        ];

        $charons = Charon::select($charonFields)
            ->leftJoin('course_modules', 'course_modules.instance', 'charon.id')
            ->join('charon_tester_type', 'charon.tester_type_code', 'charon_tester_type.code')
            ->where('charon.course', $courseId)
            ->where('course_modules.module', $moduleId)
            ->orWhereNull('course_modules.module')
            ->with([
                'labs' => function ($query) {
                    $query->select(
                        'charon_lab.id',
                        "charon_lab.start",
                        "charon_lab.end",
                        "charon_lab.course_id",
                        "charon_lab.name"
                    );
                },
                'gradeItem' => function ($query) {
                    $query->select(
                        'grade_categories.id',
                        'grade_items.calculation',
                        'grade_items.courseid',
                        'grade_categories.path',
                        'grade_categories.fullname',
                        'grade_items.itemtype',
                        'grade_items.iteminstance',
                        'grade_items.grademax'
                    );
                },
                'grademaps' => function ($query) {
                    $query->with([
                        'gradeItem' => function ($query) {
                            $query->select(['id', 'grademax']);
                        }
                    ])
                    ->select(
                        'charon_grademap.id',
                        'charon_grademap.charon_id',
                        'charon_grademap.grade_item_id',
                        'charon_grademap.grade_type_code',
                        'charon_grademap.name',
                        'charon_grademap.persistent'
                    );
                },
                'deadlines' => function ($query) {
                    $query->with([
                        'group' => function ($query) {
                            $query->select(['id', 'name']);
                        }
                    ]);
                }
            ])
            ->orderBy('charon.name')
            ->get();

        foreach ($charons as $charon) {
            $charon->calculation_formula = $charon->gradeItem
                ? $this->gradebookService->denormalizeCalculationFormula(
                    $charon->gradeItem->calculation,
                    $courseId
                )
                : '';
            $charon->defense_labs = $charon->labs;
            unset($charon->labs);
            unset($charon->gradeItem);
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
