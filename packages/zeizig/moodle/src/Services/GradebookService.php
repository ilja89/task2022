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

    /**
     * Add a grade category to the gradebook. Accepts the course id and the category name.
     * Returns the created category ID.
     *
     * @param  integer  $courseId
     * @param  string  $categoryName
     *
     * @return integer
     */
    public function addGradeCategory($courseId, $categoryName)
    {
        $grade_category = new \grade_category( [ 'courseid' => $courseId, 'fullname' => $categoryName ], false );
        $grade_category->insert();

        return $grade_category->id;
    }

    /**
     * Moves the Grade Item with the given ID to the given category.
     *
     * @param  integer  $gradeItemId
     * @param  integer  $categoryId
     */
    public function moveGradeItemToCategory($gradeItemId, $categoryId)
    {
        /** @var GradeItem $gradeItem */
        $gradeItem = GradeItem::find($gradeItemId);
        $gradeItem->categoryid = $categoryId;
        $gradeItem->save();
    }

    /**
     * Updates a Grade Item with the given parameters.
     * Parameters is an array where the keys are the changed value types and the value is the new value.
     *
     * @param  integer  $gradeItemId
     * @param  array  $parameters
     *
     * @return GradeItem
     */
    public function updateGradeItem($gradeItemId, $parameters)
    {
        $gradeItem = GradeItem::find($gradeItemId);
        foreach ($parameters as $key => $parameter) {
            $gradeItem->{$key} = $parameter;
        }
        $gradeItem->save();

        return $gradeItem;
    }

    /**
     * Get Category GradeItem.
     *
     * @param  integer  $categoryId
     *
     * @return GradeItem
     */
    public function getGradeItemByCategoryId($categoryId)
    {
        return GradeItem::where('itemtype', 'category')
            ->where('iteminstance', $categoryId)
            ->first();
    }
}
