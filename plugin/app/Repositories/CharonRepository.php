<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\CourseModule;

/**
 * Class CharonRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class CharonRepository
{
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
     * @param  integer  $id
     *
     * @return Charon
     */
    public function getCharonByCourseModuleId($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule == null) {
            return null;
        }

        if ($courseModule->isInstanceOfPlugin()) {
            return Charon::where('id', $courseModule->instance)
                ->first();
        }

        return null;
    }

    /**
     * Gets a charon instance with eagerly loaded tester type and grading method by course module id.
     * Returns null if no course module is found or if the given course module is not a Charon.
     *
     * @param  integer  $id
     *
     * @return Charon
     */
    public function getCharonByCourseModuleIdEager($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule == null) {
            return null;
        }

        if ($courseModule->isInstanceOfPlugin()) {
            return Charon::with('testerType', 'gradingMethod', 'grademaps.gradeItem', 'deadlines')
                         ->where('id', $courseModule->instance)
                         ->first();
        }

        return null;
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

        return $oldCharon->save();
    }
}
