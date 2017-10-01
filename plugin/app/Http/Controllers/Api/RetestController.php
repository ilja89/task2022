<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Services\TesterCommunicationService;

class RetestController extends Controller
{
    /** @var TesterCommunicationService */
    protected $testerCommunicationService;
    /** @var GitCallbacksRepository */
    protected $gitCallbacksRepository;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository
    )
    {
        parent::__construct($request);

        $this->testerCommunicationService = $testerCommunicationService;
        $this->gitCallbacksRepository = $gitCallbacksRepository;
    }

    /**
     * Trigger retesting the student's submission.
     *
     * @param Submission $submission
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws SubmissionNoGitCallbackException
     */
    public function index(Submission $submission)
    {
        $gitCallback = $submission->gitCallback;
        if (! $gitCallback) {
            throw (new SubmissionNoGitCallbackException('submission_git_callback_is_required'))
                ->setSubmissionId($submission->id);
        }

        // TODO: Make this work
        $newGitCallback = $this->gitCallbacksRepository->save(
            $this->request->fullUrl(),
            $gitCallback->repo,
            $gitCallback->user
        );
        $params = [
            'repo' => $newGitCallback->repo,
            'user' => $newGitCallback->user,
            'retest' => 1,
            'submission_id' => $submission->id,
            'commit_hash' => $submission->git_hash,
            'project' => $submission->charon->project_folder,
        ];

        $this->testerCommunicationService->sendGitCallback(
            $newGitCallback,
            $this->request->getUriForPath('/api/tester_callback'),
            $params
        );

        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Retesting has been triggered.',
            ],
        ], 200);
    }
}
