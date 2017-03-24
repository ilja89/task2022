<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\SubmissionService;

/**
 * Class TesterCallbackController.
 * Handles accepting submissions and results from the tester.
 *
 * @package TTU\Charon\Http\Controllers
 */
class TesterCallbackController extends Controller
{
    /** @var SubmissionService */
    private $submissionService;

    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var GitCallbackService */
    private $gitCallbackService;

    /**
     * TesterCallbackController constructor.
     *
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param CharonGradingService $charonGradingService
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(
        Request $request,
        SubmissionService $submissionService,
        CharonGradingService $charonGradingService,
        GitCallbackService $gitCallbackService
    ) {
        parent::__construct($request);
        $this->submissionService    = $submissionService;
        $this->charonGradingService = $charonGradingService;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Accepts submissions from the tester.
     */
    public function index()
    {
        $this->gitCallbackService->checkGitCallbackForToken($this->request->input('secret_token'));

        $submission = $this->submissionService->saveSubmission($this->request);
        $this->charonGradingService->calculateCalculatedResultsForNewSubmission($submission);
        $this->charonGradingService->updateGradeIfApplicable($submission);

        $this->hideUnneededFields($submission);

        return $submission;
    }

    /**
     * Hide unnecessary fields so that the tester doesn't get duplicate information.
     *
     * @param Submission $submission
     */
    private function hideUnneededFields(Submission $submission)
    {
        $submission->makeHidden('charon');
        foreach ($submission->results as $result) {
            $result->makeHidden('submission');
        }
    }
}
