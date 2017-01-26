<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Services\GradingService;

class CharonGradingService
{
    /** @var GradingService */
    private $gradingService;

    /** @var SubmissionService */
    private $submissionService;

    /** @var GrademapService */
    private $grademapService;

    /**
     * CharonGradingService constructor.
     *
     * @param GradingService $gradingService
     * @param SubmissionService $submissionService
     * @param GrademapService $grademapService
     */
    public function __construct(
        GradingService $gradingService,
        SubmissionService $submissionService,
        GrademapService $grademapService
    ) {
        $this->gradingService    = $gradingService;
        $this->submissionService = $submissionService;
        $this->grademapService   = $grademapService;
    }

    /**
     * Update the grade for the user if it should be updated.
     *
     * @param  Submission $submission
     *
     * @return void
     */
    public function updateGradeIfApplicable($submission)
    {
        $charon          = $submission->charon;
        $shouldBeUpdated = ! $this->submissionService->charonHasConfirmedSubmission($submission->charon_id);

        if ($shouldBeUpdated && $charon->gradingMethod->isPreferBest()) {
            // TODO: Check if the Grade should be updated.
            $shouldBeUpdated = $this->submissionIsBetterThanLast($submission);
        }

        if ( ! $shouldBeUpdated) {
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

    /**
     * Check if the current submission is better than the last active one.
     *
     * @param  Submission $submission
     *
     * @return bool
     */
    private function submissionIsBetterThanLast($submission)
    {
        $submissionSum       = 0;
        $activeSubmissionSum = 0;
        foreach ($submission->results as $result) {
            $grademap   = $this->grademapService->getGrademapByResult($result);
            $gradeGrade = $grademap->gradeItem->gradeGrade;

            $submissionSum += $result->calculated_result;
            $activeSubmissionSum += $gradeGrade->finalgrade;
        }

        return $submissionSum >= $activeSubmissionSum;
    }
}
