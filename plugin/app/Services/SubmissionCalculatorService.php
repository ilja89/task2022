<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Collection;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeGrade;
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

    /** @var GrademapService */
    protected $grademapService;

    /**
     * SubmissionCalculatorService constructor.
     *
     * @param GradebookService $gradebookService
     * @param GrademapService $grademapService
     */
    public function __construct(
        GradebookService $gradebookService,
        GrademapService $grademapService
    ) {
        $this->gradebookService = $gradebookService;
        $this->grademapService = $grademapService;
    }

    /**
     * Calculate results for the given Result taking into account the given deadlines.
     *
     * Currently, this assumes that other students connected to this submission share the same
     * group deadlines as the author of the submission.
     *
     * @param Result $result
     * @param Deadline[]|Collection $deadlines
     * @param ?Result $previousResult Previous result for grading method 'prefer_best_each_test_grade'
     *
     * @return float
     */
    public function calculateResultFromDeadlines(Result $result, $deadlines, ?Result $previousResult = null): float
    {
        $grademap = $result->getGrademap();
        if ($grademap === null) {
            return 0.0;
        }

        if ($grademap->persistent) {
            return $result->calculated_result;
        }

        $maxPoints = $grademap->gradeItem->grademax;
        $smallestScore = $result->percentage * $maxPoints;

        if (!$result->isTestsGrade() || $deadlines->isEmpty()) {
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
            return 0.0;
        }

        foreach ($deadlinesForUser as $deadline) {
            if ($deadline->deadline_time->lt($submissionTime)) {
                $score = $this->calculateScoreFromResultAndDeadline($deadline, $result, $maxPoints, $previousResult);
                if ($smallestScore > $score) {
                    $smallestScore = $score;
                }
            }
        }

        return $smallestScore;
    }

    /**
     * Calculate the score for the result considering the deadline, max points, and grading method.
     *
     * @param Deadline $deadline
     * @param Result $result
     * @param float $maxPoints
     * @param ?Result $previousResult Previous result for grading method 'prefer_best_each_test_grade'
     *
     * @return float|int
     */
    private function calculateScoreFromResultAndDeadline(
        Deadline $deadline,
        Result $result,
        float $maxPoints,
        ?Result $previousResult = null
    ) {
        if (
            $result->submission->charon->gradingMethod->isPreferBestEachTestGrade() &&
            $previousResult !== null &&
            $result->percentage >= $previousResult->percentage
        ) {
            $extra = round($result->percentage - $previousResult->percentage, 2);
            return $previousResult->calculated_result + $extra * ($deadline->percentage / 100) * $maxPoints;
        }

        return $result->percentage * ($deadline->percentage / 100) * $maxPoints;
    }

    /**
     * Check if the current submission is better than the active one.
     *
     * @param Submission $submission
     * @param int $studentId
     *
     * @return bool
     */
    public function submissionIsBetterThanActive(Submission $submission, int $studentId): bool
    {
        $thisResult   = $this->calculateSubmissionTotalGrade($submission, $studentId, true);
        $activeResult = $this->calculateActiveSubmissionTotalGrade($submission->charon, $studentId, true);

        return $thisResult > $activeResult;
    }

    /**
     * Get the currently active grade for the given charon and user.
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

    /**
     * Calculates the total grade for all Submission students
     *
     * @param Submission $submission
     *
     * @return array
     */
    public function calculateSubmissionTotalGrades(Submission $submission): array
    {
        $grades = [];

        foreach ($submission->users as $user) {
            $grades[$user->id] = $this->calculateSubmissionTotalGrade($submission, $user->id);
        }

        return $grades;
    }

    /**
     * Calculates the total grade for the given submission.
     *
     * @param Submission $submission
     * @param int $user_id
     * @param bool $ignoreCustom
     * @param bool $ignoreStyle
     *
     * @return float
     */
    public function calculateSubmissionTotalGrade(
        Submission $submission,
        int $user_id,
        bool $ignoreCustom = false,
        bool $ignoreStyle = false
    ): float {

        $calculation = $submission->charon->category->getGradeItem()->calculation;
        $results = $submission->results;

        if ($calculation == null) {
            $sum = 0;
            foreach ($results as $result) {
                if ($result->user_id == $user_id) {
                    $sum += $result->calculated_result;
                }
            }

            return round($sum, 3);
        }

        $params = $this->grademapService->findFormulaParams(
            $calculation,
            $results,
            $user_id,
            $ignoreCustom,
            $ignoreStyle
        );

        return round($this->gradebookService->calculateResultWithFormulaParams($calculation, $params), 3);
    }

    /**
     * Calculate total grade for given charon and user with grades got from gradebook.
     *
     * It is available to ignore custom/style grades in order to get potential total grade.
     *
     * @param Charon $charon
     * @param int $userId
     * @param bool $ignoreCustom
     * @param bool $ignoreStyle
     * @return float
     */
    private function calculateActiveSubmissionTotalGrade(
        Charon $charon,
        int $userId,
        bool $ignoreCustom = false,
        bool $ignoreStyle = false
    ): float {
        $calculationFormula = $charon->category->getGradeItem()->calculation;
        $total = $this->gradebookService->calculateResultWithFormulaParams(
            $calculationFormula,
            $this->grademapService->findFormulaParamsFromGradebook(
                $calculationFormula,
                [],
                $userId,
                $ignoreCustom,
                $ignoreStyle
            )
        );

        return round($total, 3);
    }
}
