<?php

namespace TTU\Charon\Traits;

use TTU\Charon\Models\Submission;
use TTU\Charon\Services\CharonGradingService;

/**
 * Grades students.
 */
trait GradesStudents
{
    private function updateGradeIfApplicable(Submission $submission, $force = false)
    {
        app(CharonGradingService::class)->updateGradeIfApplicable($submission, $force);
    }

    private function confirmSubmission(Submission $submission)
    {
        app(CharonGradingService::class)->confirmSubmission($submission);
    }
}
