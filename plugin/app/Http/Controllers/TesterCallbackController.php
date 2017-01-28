<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\CharonGradingService;
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

    /** @var CharonGradingService */
    private $charonGradingService;

    /**
     * TesterCallbackController constructor.
     *
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param GrademapService $grademapService
     * @param CharonGradingService $charonGradingService
     */
    public function __construct(
        Request $request,
        SubmissionService $submissionService,
        GrademapService $grademapService,
        CharonGradingService $charonGradingService
    ) {
        $this->request              = $request;
        $this->submissionService    = $submissionService;
        $this->grademapService      = $grademapService;
        $this->charonGradingService = $charonGradingService;
    }

    /**
     * Accepts submissions from the tester.
     */
    public function index()
    {
        $gitCallback = $this->getGitCallback();
        $this->checkGitCallback($gitCallback);

        $submission = $this->submissionService->saveSubmission($this->request);
        $this->calculateCalculatedResults($submission);
        $this->charonGradingService->updateGradeIfApplicable($submission);

        return $submission;
    }

    /**
     * Calculate the calculated_result for the Results in given submission.
     *
     * @param  Submission $submission
     *
     * @return void
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
        $maxPoints     = $this->grademapService->getGrademapByResult($result)->gradeItem->grademax;
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
     * Gets the git callback with the secret token from the request.
     *
     * @return GitCallback
     */
    private function getGitCallback()
    {
        $token = $this->request['secret_token'];
        $gitCallback = GitCallback::where('secret_token', $token)
            ->where('response_received', 0)
            ->get();

        if ($gitCallback->isEmpty()) {
            return null;
        }

        return $gitCallback->first();
    }

    /**
     * Check the given Git callback. If the secret token isn't correct
     * throw an exception. Also set the response received flag to true.
     *
     * @param  GitCallback  $gitCallback
     */
    private function checkGitCallback($gitCallback)
    {
        if ($gitCallback === null) {
            throw new UnauthorizedException('Secret key is incorrect.');
        }
        $gitCallback->response_received = 1;
        $gitCallback->save();
    }
}
