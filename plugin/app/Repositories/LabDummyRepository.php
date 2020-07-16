<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use TTU\Charon\Exceptions\CharonNotFoundException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\LabDummy;
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
class LabDummyRepository
{
    /** @var ModuleService */
    protected $moduleService;

    /**
     * LabRepository constructor.
     *
     * @param ModuleService $moduleService
     */
    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Save the lab instance.
     *
     * @param  LabDummy  $labDummy
     *
     * @return boolean
     */
    public function save(LabDummy $labDummy)
    {
        return $labDummy->save();
    }

    /**
     * Get all labs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllLabs()
    {
        return LabDummy::all();
    }

    /**
     * Get an instance of Charon by its id.
     *
     * @param  integer  $id
     *
     * @return LabDummy
     */
    public function getLabDummyById($id)
    {
        return LabDummy::find($id);
    }

    /**
     * Deletes the instance with given id.
     *
     * @param  integer $id
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function deleteByInstanceId($id)
    {
        /** @var LabDummy $labDummy */
        $labDummy = LabDummy::find($id);

        // TODO CharonDefenseLab::where('lab_id', $id)->delete();
        // TODO LabTeacher::where('lab_id', $id)->delete();

        return $labDummy->delete();
    }

    /**
     * Takes the old instance and override its values with the new Charon values.
     *
     * @param  LabDummy  $oldLabDummy
     * @param  LabDummy  $newLabDummy
     *
     * @return boolean
     */
    public function update($oldLabDummy, $newLabDummy)
    {
        $oldLabDummy->start = $newLabDummy->start;
        $oldLabDummy->end = $newLabDummy->end;
        $oldLabDummy->teachers = $newLabDummy->teachers;  // necessary?
        /*$oldCharon->name = $newCharon->name;
        $oldCharon->project_folder = $newCharon->project_folder;
        $oldCharon->tester_extra = $newCharon->tester_extra;
        $oldCharon->system_extra = $newCharon->system_extra;
        $oldCharon->tester_type_code = $newCharon->tester_type_code;
        $oldCharon->grading_method_code = $newCharon->grading_method_code;
        $oldCharon->grouping_id = $newCharon->grouping_id;
        $oldCharon->timemodified = Carbon::now()->timestamp;

        $oldCharon->description = $this->fileUploadService->savePluginFiles(
            $newCharon->description,
            'description',
            $oldCharon->courseModule()->id
        );*/

        return $oldLabDummy->save();
    }

    /**
     * Find all Charons in course with given id. Also loads deadlines,
     * grademaps with grade items.
     *
     * @param  integer $courseId
     *
     * @return LabDummy[]
     */
    public function findLabDummiesByCourse($courseId)
    {
        // someone else's to implement
    }
}
