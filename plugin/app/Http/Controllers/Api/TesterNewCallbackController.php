<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\TesterNewCallbackRequest;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\SubmissionService;

/**
 * Class TesterNewCallbackController.
 * Handles accepting submissions and results from the tester (Arete v2).
 *
 * @package TTU\Charon\Http\Controllers
 */
class TesterNewCallbackController extends Controller
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
     *
     * @param TesterNewCallbackRequest $request
     *
     * @return Submission
     */
    public function index(TesterNewCallbackRequest $request)
    {

        Log::info("new callback", [$request]);
        $gitCallback = $this->gitCallbackService->checkGitCallbackForToken(
            $request->input('returnExtra')['token']
        );

        $submission = $this->submissionService->saveSubmission($request, $gitCallback);
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
