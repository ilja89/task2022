<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Exceptions\CharonNotFoundException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\GradeItem;
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

    /**
     * CharonRepository constructor.
     *
     * @param ModuleService $moduleService
     */
    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Save the charon instance.
     *
     * @param  Charon  $charon
     *
     * @return boolean
     */
    public function save($charon)
    {
        return $charon->save();
    }

    /**
     * Get all charons.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCharons()
    {
        return Charon::all();
    }

    /**
     * Get an instance of charon by its id.
     *
     * @param  integer  $id
     *
     * @return Charon
     */
    public function getCharonById($id)
    {
        return Charon::find($id);
    }

    /**
     * Gets a charon instance by course module id.
     * Returns null if no course module is found or if the given course module is not a Charon.
     *
     * @param  integer $id
     *
     * @return Charon
     * @throws CharonNotFoundException
     */
    public function getCharonByCourseModuleId($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule === null || ! $courseModule->isInstanceOfPlugin()) {
            throw new CharonNotFoundException('charon_course_module_not_found', $id);
        }

        return Charon::where('id', $courseModule->instance)
                     ->first();
    }

    /**
     * Gets a charon instance with eagerly loaded fields like tester type and grading method by
     * course module id.
     *
     * @param  integer $id
     *
     * @return Charon
     * @throws CharonNotFoundException
     */
    public function getCharonByCourseModuleIdEager($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule === null || ! $courseModule->isInstanceOfPlugin()) {
            throw new CharonNotFoundException('charon_course_module_not_found', $id);
        }

        return Charon::with('testerType', 'gradingMethod', 'grademaps.gradeItem', 'deadlines')
                     ->where('id', $courseModule->instance)
                     ->first();
    }

    /**
     * Deletes the instance with given id.
     *
     * @param  integer  $id
     *
     * @return boolean
     */
    public function deleteByInstanceId($id)
    {
        $result = GradeItem::where('itemtype', 'mod')
                           ->where('itemmodule', config('moodle.plugin_slug'))
                           ->where('iteminstance', $id)
                           ->delete();
        Grademap::where('charon_id', $id)
            ->delete();
        Deadline::where('charon_id', $id)
            ->delete();

        return Charon::destroy($id);
    }

    /**
     * Takes the old instance and override its values with the new Charon values.
     *
     * @param  Charon  $oldCharon
     * @param  Charon  $newCharon
     *
     * @return boolean
     */
    public function update($oldCharon, $newCharon)
    {
        $oldCharon->name = $newCharon->name;
        $oldCharon->description = $newCharon->description;
        $oldCharon->project_folder = $newCharon->project_folder;
        $oldCharon->extra = $newCharon->extra;
        $oldCharon->tester_type_code = $newCharon->tester_type_code;
        $oldCharon->grading_method_code = $newCharon->grading_method_code;
        $oldCharon->timemodified = Carbon::now()->timestamp;

        return $oldCharon->save();
    }

    /**
     * Find all Charons in course with given id.
     *
     * @param  integer $courseId
     * 
     * @return Charon[]
     */
    public function findCharonsByCourse($courseId)
    {
        $moduleId = $this->moduleService->getModuleId();
        
        return DB::table('charon')
            ->join('course_modules', 'course_modules.instance', 'charon.id')
            ->join('charon_tester_type', 'charon.tester_type_code', 'charon_tester_type.code')
            ->where('course_modules.course', $courseId)
            ->where('course_modules.module', $moduleId)
            ->select(
                'charon.id',
                'charon.name',
                'charon_tester_type.name AS tester_type_name'
            )
            ->orderBy('charon.name')
            ->get();
    }

    /**
     * Gets Submissions by Charon and user.
     *
     * @param  integer  $charonId
     * @param  integer  $userId
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
}
