<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Traits\GradesStudents;

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
