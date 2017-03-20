<?php

namespace TTU\Charon\Helpers;

use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;

/**
 * Class SubmissionCalculator.
 *
 * @package TTU\Charon\Helpers
 */
class SubmissionCalculator
{
    /**
     * Calculate results for the given Result taking into account the given deadlines.
     *
     * @param  Result $result
     * @param  Deadline[] $deadlines
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

        /** @var Submission $submission */
        $submission = $result->submission;
        foreach ($deadlines as $deadline) {
            $deadline->deadline_time->setTimezone(\Config::get('app.timezone'));
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
}
