<?php

namespace Zeizig\Moodle\Services;

class GradingService
{
    public function updateGrade($courseId, $instanceId, $itemNumber, $userId, $score, $pluginSlug)
    {
        $grade           = new \stdClass();
        $grade->userid   = $userId;
        $grade->rawgrade = $score;

        grade_update(
            'mod/' . $pluginSlug,
            $courseId,
            'mod',
            $pluginSlug,
            $instanceId,
            $itemNumber,
            $grade,
            null
        );
    }
}
