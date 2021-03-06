<?php

namespace Zeizig\Moodle\Services;

/**
 * Class GradingService.
 * Grading students.
 *
 * @package Zeizig\Moodle\Services
 */
class GradingService
{
    /**
     * Grades the student with given score for given task (Grade Item).
     *
     * @param  integer  $courseId
     * @param  integer  $instanceId
     * @param  integer  $itemNumber
     * @param  integer  $userId
     * @param  float  $score
     */
    public function updateGrade($courseId, $instanceId, $itemNumber, $userId, $score)
    {
        global $CFG;
        require_once $CFG->dirroot . '/lib/gradelib.php';

        $grade           = new \stdClass();
        $grade->userid   = $userId;
        $grade->rawgrade = $score;

        grade_update(
            'mod/' . config('moodle.plugin_slug'),
            $courseId,
            'mod',
            config('moodle.plugin_slug'),
            $instanceId,
            $itemNumber,
            $grade
        );
    }
}
