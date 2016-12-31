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
     *
     * @param  integer  $id
     *
     * @return Charon
     */
    public function getCharonByCourseModuleId($id)
    {
        /** @var CourseModule $courseModule */
        $courseModule = CourseModule::find($id);

        if ($courseModule->isInstanceOfPlugin()) {
            return Charon::where('id', $courseModule->instance)
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
}
