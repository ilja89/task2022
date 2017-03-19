<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Services\GradingService;

/**
 * Class CharonGradingService.
 * This is names so in order to avoid naming conflicts with \Zeizig\Moodle\GradingService
 *
 * @package TTU\Charon\Services
 */
class CharonGradingService
{
    /** @var GradingService */
    private $gradingService;

    /** @var SubmissionService */
    public $submissionService;

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
        if ( ! $this->gradesShouldBeUpdated($submission, $force)) {
            return;
        }

        $charon = $submission->charon;
        // TODO: Send commit hash info to tester! Grade will now be updated!
        $courseId = $charon->courseModule()->course;

        $gradeTypeCodes = $charon->getGradeTypeCodes();
        foreach ($submission->results as $result) {
            if ( ! $gradeTypeCodes->contains($result->grade_type_code)) {
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
     * Check if the given submission should update grades.
     *
     * @param  Submission $submission
     * @param  bool $force
     *
     * @return bool
     */
    public function gradesShouldBeUpdated(Submission $submission, $force)
    {
        if ($force) {
            return true;
        }

        if ($this->hasConfirmedSubmission($submission)) {
            return false;
        }

        return $this->shouldUpdateBasedOnGradingMethod($submission);
    }

    /**
     * Calculates the calculated results for given new submission and
     * saves them.
     *
     * @param Submission $submission
     *
     * @return void
     */
    public function calculateCalculatedResultsForNewSubmission(Submission $submission)
    {
        $charon = $submission->charon;
        foreach ($submission->results as $result) {
            $result->calculated_result = $this->calculateResultFromDeadlines($result, $charon->deadlines);
            $result->save();
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
            $grademap = $result->getGrademap();
            if ($grademap === null) {
                continue;
            }

            $gradeGrade = $grademap->gradeItem->gradesForUser($submission->user_id);

            if ($gradeGrade !== null) {
                $activeSubmissionSum += $gradeGrade->finalgrade;
            }

            $submissionSum += $result->calculated_result;
        }

        return $submissionSum >= $activeSubmissionSum;
    }

    /**
     * Calculate results for the given Result taking into account the given deadlines.
     *
     * @param  Result $result
     * @param  Deadline[] $deadlines
     *
     * @return float
     */
    private function calculateResultFromDeadlines(Result $result, $deadlines)
    {
        $grademap = $result->getGrademap();
        if ($grademap === null) {
            return 0;
        }

        $maxPoints     = $grademap->gradeItem->grademax;
        $smallestScore = $result->percentage * $maxPoints;

        if ( ! $result->isTestsGrade() || empty($deadlines)) {
            return $smallestScore;
        }

        /** @var Submission $submission */
        $submission = $result->submission;
        foreach ($deadlines as $deadline) {
            $deadline->deadline_time->setTimezone(config('app.timezone'));
            if ($deadline->deadline_time->lt($submission->git_timestamp)) {
                $score = $this->calculateScoreFromResultAndDeadline($deadline, $result, $maxPoints);
                if ($smallestScore > $score) {
                    $smallestScore = $score;
                }
            }
        }

        return $smallestScore;
    }

    /**
     * Calculate the score for the result considering the deadline and max points.
     *
     * @param  Deadline $deadline
     * @param  Result $result
     * @param  float $maxPoints
     *
     * @return float|int
     */
    private function calculateScoreFromResultAndDeadline($deadline, $result, $maxPoints)
    {
        return ($deadline->percentage / 100) * $result->percentage * $maxPoints;
    }

    /**
     * Check if the submission has a previously confirmed submission.
     *
     * @param  Submission  $submission
     *
     * @return bool
     */
    private function hasConfirmedSubmission(Submission $submission)
    {
        return $this->submissionService->charonHasConfirmedSubmission(
            $submission->charon_id,
            $submission->user_id
        );
    }

    /**
     * Check if the submission should be updated based on the grading
     * method of the charon.
     *
     * @param  Submission  $submission
     *
     * @return bool
     */
    private function shouldUpdateBasedOnGradingMethod(Submission $submission)
    {
        $charon = $submission->charon;
        if ($charon->gradingMethod->isPreferBest()) {
            return $this->submissionIsBetterThanLast($submission);
        }

        return true;
    }
}
