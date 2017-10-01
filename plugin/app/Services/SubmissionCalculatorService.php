<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Collection;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class SubmissionCalculator.
 *
 * @package TTU\Charon\Helpers
 */
class SubmissionCalculatorService
{
    /** @var GradebookService */
    protected $gradebookService;

    /**
     * SubmissionCalculatorService constructor.
     *
     * @param GradebookService $gradebookService
     */
    public function __construct(GradebookService $gradebookService)
    {
        $this->gradebookService = $gradebookService;
    }

    /**
     * Calculate results for the given Result taking into account the given deadlines.
     *
     * @param  Result $result
     * @param  Deadline[]|Collection $deadlines
     *
     * @return float
     */
    public function calculateResultFromDeadlines(Result $result, $deadlines)
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

        $submission = $result->submission;
        $submissionTime = $submission->originalSubmission
            ? $submission->originalSubmission->created_at
            : $submission->created_at;
        // TODO: [Refactor] Maybe select only group IDs via query builder
        $userGroups = $submission->user
            ->groups()
            ->where('courseid', $submission->charon->course)
            ->get();

        $deadlinesForUser = $deadlines->filter(function ($deadline) use ($userGroups) {
            return $deadline->group_id === null || $userGroups->contains('id', $deadline->group_id);
        });

        if ($deadlinesForUser->isEmpty() && $deadlines->isNotEmpty()) {
            // No deadlines for user - shouldn't get a grade
            // TODO: [Feature] Think of what should happen
            return 0;
        }

        foreach ($deadlinesForUser as $deadline) {
            $deadline->deadline_time->setTimezone(\Config::get('app.timezone'));
            if ($deadline->deadline_time->lt($submissionTime)) {
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
     * Check if the current submission is better than the last active one.
     *
     * @param  Submission $submission
     *
     * @return bool
     */
    public function submissionIsBetterThanLast($submission)
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
     * Get the currently active grades for the given charon and user.
     *
     * @param Charon $charon
     * @param int $userId
     *
     * @return GradeGrade
     */
    public function getUserActiveGradeForCharon(Charon $charon, $userId)
    {
        $gradeItem = $charon->category->getGradeItem();

        return $this->gradebookService->getGradeForGradeItemAndUser($gradeItem->id, $userId);
    }
}
