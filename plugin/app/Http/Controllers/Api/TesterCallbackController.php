<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\Flows\SaveTesterCallback;
use TTU\Charon\Services\GitCallbackService;

/**
 * Class TesterCallbackController.
 * Handles accepting submissions and results from the tester (Arete v2).
 *
 * @package TTU\Charon\Http\Controllers
 */
class TesterCallbackController extends Controller
{
    /** @var GitCallbackService */
    private $gitCallbackService;

    /** @var SaveTesterCallback */
    private $saveCallbackFlow;

    /**
     * TesterCallbackController constructor.
     *
     * @param Request $request
     * @param GitCallbackService $gitCallbackService
     * @param SaveTesterCallback $saveCallbackFlow
     */
    public function __construct(
        Request $request,
        GitCallbackService $gitCallbackService,
        SaveTesterCallback $saveCallbackFlow
    ) {
        parent::__construct($request);
        $this->gitCallbackService = $gitCallbackService;
        $this->saveCallbackFlow = $saveCallbackFlow;
    }

    /**
     * Accepts submissions from the tester.
     *
     * @param TesterCallbackRequest $request
     *
     * @throws IncorrectSecretTokenException
     * @throws Exception
     */
    public function index(TesterCallbackRequest $request)
    {
        Log::info("Arete 2.0 callback", [$request->input('returnExtra')]);

        $gitCallback = $this->gitCallbackService->checkGitCallbackForToken(
            $request->input('returnExtra.token')
        );

        $usernames = collect([$request->input('uniid')])
            ->merge($request->input('returnExtra.usernames'))
            ->unique()
            ->values()
            ->all();

        $submission = $this->saveCallbackFlow->run($request, $gitCallback, $usernames);

        return $this->hideUnneededFields($submission);
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
