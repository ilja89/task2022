<?php

namespace Zeizig\Moodle\Services;

use Illuminate\Contracts\Foundation\Application;
use Zeizig\Moodle\Models\GradeItem;

/**
 * Class GradebookService.
 * Wrapper for Moodle gradebook functions.
 *
 * Contains methods to create grade items, categories, etc.
 *
 * @package Zeizig\Moodle\Services
 */
class GradebookService extends MoodleService
{
    /**
     * GradebookService constructor.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }


    /**
     * Adds a grade item with given parameters.
     *
     * @param  integer  $courseId Course id
     * @param  integer  $instanceId Plugin instance id
     * @param  integer  $itemNumber Plugin item number
     * @param  string   $itemName Grade item name
     * @param  float    $maxGrade Max grade for this grade item
     * @param  integer  $idNumber Grade item id number
     *
     * @return void
     */
    public function addGradeItem($courseId, $instanceId, $itemNumber, $itemName, $maxGrade, $idNumber)
    {
        $extraParams = [
            'itemname' => $itemName,
            'grademin' => 0,
            'grademax' => $maxGrade,
            'idnumber' => $idNumber
        ];

        grade_update(
            'mod/' . config('moodle.plugin_slug'),
            $courseId,
            'mod',
            config('moodle.plugin_slug'),
            $instanceId,
            $itemNumber,
            null,
            $extraParams
        );
    }
}
