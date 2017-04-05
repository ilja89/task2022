<?php

namespace Zeizig\Moodle\Services;

use Illuminate\Contracts\Foundation\Application;
use Zeizig\Moodle\Models\GradeGrade;
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
     * @param  integer $courseId Course id
     * @param  integer $instanceId Plugin instance id
     * @param  integer $itemNumber Plugin item number
     * @param  string $itemName Grade item name
     * @param  float $maxGrade Max grade for this grade item
     * @param  integer $idNumber Grade item id number
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
     * @param  integer $courseId
     * @param  string $categoryName
     *
     * @return integer
     */
    public function addGradeCategory($courseId, $categoryName)
    {
        $grade_category = new \grade_category(['courseid' => $courseId, 'fullname' => $categoryName], false);
        $grade_category->insert();

        return $grade_category->id;
    }

    /**
     * Moves the Grade Item with the given ID to the given category.
     *
     * @param  integer $gradeItemId
     * @param  integer $categoryId
     */
    public function moveGradeItemToCategory($gradeItemId, $categoryId)
    {
        /** @var GradeItem $gradeItem */
        $gradeItem             = GradeItem::find($gradeItemId);
        $gradeItem->categoryid = $categoryId;
        $gradeItem->save();
    }

    /**
     * Updates a Grade Item with the given parameters.
     * Parameters is an array where the keys are the changed value types and the value is the new value.
     *
     * @param  integer $gradeItemId
     * @param  array $parameters
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
     * @param  integer $categoryId
     *
     * @return GradeItem
     */
    public function getGradeItemByCategoryId($categoryId)
    {
        return GradeItem::where('itemtype', 'category')
                        ->where('iteminstance', $categoryId)
                        ->first();
    }

    /**
     * Denormalizes the given calculation formula. The given formula
     * is in the format with ##grade item id##. The result will
     * have the format with [[id_number]].
     *
     * @param  string $formula
     * @param  int $courseId
     *
     * @return string
     */
    public function denormalizeCalculationFormula($formula, $courseId)
    {
        return \grade_item::denormalize_formula($formula, $courseId);
    }

    /**
     * Calculates the result for the given formula with given parameters.
     * Parameters array:
     *      [ Grade item id number => points, ... ]
     *
     * @param  string $formula normalized formula
     * @param  array $params
     * @param  int $courseId
     *
     * @return double
     */
    public function calculateResultFromFormula($formula, $params, $courseId)
    {
        global $CFG;
        require_once $CFG->dirroot . '/lib/mathslib.php';
        require_once $CFG->dirroot . '/lib/grade/grade_item.php';
        require_once $CFG->dirroot . '/lib/grade/constants.php';

        $formula = $this->denormalizeCalculationFormula($formula, $courseId);
        $formula = str_replace('[[', '', $formula);
        $formula = str_replace(']]', '', $formula);

        $calcFormula = new \calc_formula($formula, $params);

        $result = $calcFormula->evaluate();

        return $result;
    }

    /**
     * Get the grade grade belonging to grade item and user
     *
     * @param  int  $gradeItemId
     * @param  int  $userId
     *
     * @return GradeGrade
     */
    public function getGradeForGradeItemAndUser($gradeItemId, $userId)
    {
        return GradeGrade::where('itemid', $gradeItemId)
                         ->where('userid', $userId)
                         ->first();
    }
}
