<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\SubmissionService;

/**
 * Class TesterCallbackController.
 * Handles accepting submissions and results from the tester (Arete v2).
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
    )
    {
        parent::__construct($request);
        $this->submissionService = $submissionService;
        $this->charonGradingService = $charonGradingService;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Accepts submissions from the tester.
     *
     * @param TesterCallbackRequest $request
     *
     * @return string|Submission
     */
    public function index(TesterCallbackRequest $request)
    {
        Log::info("Arete 2.0 callback", [$request->input('returnExtra')]);
        $gitCallback = $this->gitCallbackService->checkGitCallbackForToken(
            $request->input('returnExtra')['token']
        );

        try {
            $submission = $this->submissionService->saveSubmission($request, $gitCallback);
        } catch (\Exception $e) {
            Log::error("Saving submission failed with message:" . $e);
            return $e->getMessage();
        }

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
