<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\LabTeacher;
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
     * @param $start
     * @param $end
     * @param $courseId
     *
     * @return boolean
     */
    public function save($start, $end, $courseId)
    {
        $lab = Lab::create([
            'start'  => Carbon::parse($start)->format('Y-m-d H:i:s'),
            'end' => Carbon::parse($end)->format('Y-m-d H:i:s'),
            'course_id' => $courseId
        ]);
        $lab->save();
        return $lab;
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
        $labs = \DB::table('lab')  // id, start, end
        //->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'lab.id') // id, lab_id, charon_id
            ->where('course_id', $courseId)
            ->select('id', 'start', 'end', 'course_id')
            ->get();
        return $labs;
    }

}
