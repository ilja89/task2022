<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Services\GradingService;

class CharonGradingService
{
    /** @var GradingService */
    private $gradingService;

    /**
     * CharonGradingService constructor.
     *
     * @param GradingService $gradingService
     */
    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Update the grade for the user if it should be updated.
     *
     * @param  Submission  $submission
     *
     * @return void
     */
    public function updateGradeIfApplicable($submission)
    {
        $charon = $submission->charon;
        $shouldBeUpdated = true;

        if ($charon->gradingMethod->isPreferBest()) {
            // TODO: Check if the Grade should be updated.
        }

        if (!$shouldBeUpdated) {
            return;
        }

        $courseId = $charon->courseModule()->course;

        foreach ($submission->results as $result) {
            $this->gradingService->updateGrade(
                $courseId,
                $charon->id,
                $result->grade_type_code,
                $submission->user_id,
                $result->calculated_result,
                'charon'
            );
        }
    }
}
