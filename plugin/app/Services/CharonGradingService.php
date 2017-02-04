<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Collection;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Services\GradingService;

class CharonGradingService
{
    /** @var GradingService */
    private $gradingService;

    /** @var SubmissionService */
    private $submissionService;

    /** @var GrademapService */
    private $grademapService;

    /** @var CharonRepository */
    private $charonRepository;

    /**
     * CharonGradingService constructor.
     *
     * @param GradingService $gradingService
     * @param SubmissionService $submissionService
     * @param GrademapService $grademapService
     * @param CharonRepository $charonRepository
     */
    public function __construct(
        GradingService $gradingService,
        SubmissionService $submissionService,
        GrademapService $grademapService,
        CharonRepository $charonRepository
    ) {
        $this->gradingService    = $gradingService;
        $this->submissionService = $submissionService;
        $this->grademapService   = $grademapService;
        $this->charonRepository  = $charonRepository;
    }

    /**
     * Update the grade for the user if it should be updated.
     *
     * @param  Submission $submission
     *
     * @param bool $force
     *
     * @return void
     */
    public function updateGradeIfApplicable($submission, $force = false)
    {
        $charon                 = $submission->charon;
        $shouldBeUpdated        = ! $this->submissionService->charonHasConfirmedSubmission(
            $submission->charon_id,
            $submission->user_id
        );
        $grademapGradeTypeCodes = $charon->grademaps->map(function ($grademap) {
            return $grademap->grade_type_code;
        });

        
        if ( ! $force && $shouldBeUpdated && $charon->gradingMethod->isPreferBest()) {
            $shouldBeUpdated = $this->submissionIsBetterThanLast($submission);
        }

        if ( ! $force && ! $shouldBeUpdated) {
            return;
        }

        $courseId = $charon->courseModule()->course;

        foreach ($submission->results as $result) {
            if ( ! $grademapGradeTypeCodes->contains($result->grade_type_code)) {
                continue;
            }

            $this->gradingService->updateGrade(
                $courseId,
                $charon->id,
                $result->grade_type_code,
                $submission->user_id,
                $result->calculated_result
            );
        }
    }

    /**
     * Confirms the given submission and unconfirms the rest for the user.
     *
     * @param  Submission $submission
     *
     * @return void
     */
    public function confirmSubmission($submission)
    {
        $userId      = $submission->user_id;
        $submissions = Submission::where('charon_id', $submission->charon_id)
                                 ->where('user_id', $userId)
                                 ->where('confirmed', 1)
                                 ->get();
        foreach ($submissions as $confirmedSubmission) {
            if ($submission->id === $confirmedSubmission->id) {
                continue;
            }
            
            $confirmedSubmission->confirmed = 0;
            $confirmedSubmission->save();
        }

        $submission->confirmed = 1;
        $submission->save();
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
            if ($grademap === null) {
                continue;
            }

            $gradeGrade = $grademap->gradeItem->gradeGrade;

            if ($gradeGrade !== null) {
                $activeSubmissionSum += $gradeGrade->finalgrade;
            }

            $submissionSum += $result->calculated_result;
        }

        return $submissionSum >= $activeSubmissionSum;
    }
}
