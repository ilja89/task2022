<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use TTU\Charon\Exceptions\CharonNotFoundException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\LabTeacher;
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
class LabRepository
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
     * @param  Lab  $lab
     *
     * @return boolean
     */
    public function save(Lab $lab)
    {
        return $lab->save();
    }

    /**
     * Get all labs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllLabs()
    {
        return Lab::all();
    }

    /**
     * Get an instance of Charon by its id.
     *
     * @param  integer  $id
     *
     * @return Lab
     */
    public function getLabById($id)
    {
        return Lab::find($id);
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
        /** @var Lab $lab */
        $lab = Lab::find($id);

        CharonDefenseLab::where('lab_id', $id)->delete();
        LabTeacher::where('lab_id', $id)->delete();

        return $lab->delete();
    }

    /**
     * Takes the old instance and override its values with the new Charon values.
     *
     * @param  Lab  $oldLab
     * @param  Lab  $newLab
     *
     * @return boolean
     */
    public function update($oldLab, $newLab)
    {
        $oldLab->start = $newLab->start;
        $oldLab->end = $newLab->end;
        $oldLab->teachers = $newLab->teachers;  // necessary?
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

        return $oldLab->save();
    }

    /**
     * Find all Charons in course with given id. Also loads deadlines,
     * grademaps with grade items.
     *
     * @param  integer $courseId
     *
     * @return Lab[]
     */
    public function findLabsByCourse($courseId)
    {
        // someone else's to implement
    }
}
