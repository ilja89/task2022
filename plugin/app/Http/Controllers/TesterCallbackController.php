<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionService;

/**
 * Class TesterCallbackController.
 * Handles accepting submissions and results from the tester.
 *
 * @package TTU\Charon\Http\Controllers
 */
class TesterCallbackController extends Controller
{
    /** @var Request */
    protected $request;

    /** @var SubmissionService */
    private $submissionService;

    /** @var GrademapService */
    private $grademapService;

    /**
     * TesterCallbackController constructor.
     *
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param GrademapService $grademapService
     */
    public function __construct(Request $request, SubmissionService $submissionService, GrademapService $grademapService)
    {
        $this->request = $request;
        $this->submissionService = $submissionService;
        $this->grademapService = $grademapService;
    }

    /**
     * Accepts submissions from the tester.
     */
    public function index()
    {
        $submission = $this->submissionService->saveSubmission($this->request);
        $this->calculateCalculatedResults($submission);
        return $submission;
    }

    /**
     * @param  Submission  $submission
     */
    private function calculateCalculatedResults($submission)
    {
        $charon = $submission->charon;
        foreach ($submission->results as $result) {
            /** @var Result $result */
            $result->calculated_result = $this->calculateResultFromDeadlines($result, $charon->deadlines);
            $result->save();
        }
    }

    /**
     * Calculate results for the given Result taking into account the given deadlines.
     *
     * @param  Result $result
     * @param  Deadline[] $deadlines
     *
     * @return float
     */
    private function calculateResultFromDeadlines($result, $deadlines)
    {
        $maxPoints = $this->grademapService->getGrademapByResult($result)->gradeItem->grademax;
        /** @var Submission $submission */
        $submission = $result->submission;
        $smallestScore = $result->percentage * $maxPoints;

        if (!$result->isTestsGrade() || empty($deadlines)) {
            return $smallestScore;
        }

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
     * @param  Deadline  $deadline
     * @param  Result  $result
     * @param  float  $maxPoints
     *
     * @return float|int
     */
    private function calculateScoreFromResultAndDeadline($deadline, $result, $maxPoints)
    {
        return ($deadline->percentage / 100) * $result->percentage * $maxPoints;
    }
}
