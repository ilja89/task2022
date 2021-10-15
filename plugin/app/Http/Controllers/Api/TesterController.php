<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\Flows\SaveTesterCallback;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Services\TesterCommunicationService;
use Zeizig\Moodle\Globals\User;

class TesterController extends Controller
{
    /** @var TesterCommunicationService */
    protected $testerCommunicationService;

    /** @var SaveTesterCallback */
    private $saveTesterFlow;

    /** @var SubmissionService */
    private $submissionService;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param SubmissionService $submissionService
     * @param Request $request
     * @param SaveTesterCallback $saveTesterFlow
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        SaveTesterCallback $saveTesterFlow,
        SubmissionService $submissionService
    )
    {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->saveTesterFlow = $saveTesterFlow;
        $this->submissionService = $submissionService;
    }

    /**
     * Trigger testing the student's inline submission.
     *
     * @param Request $request
     * @param Charon $charon
     *
     * @return JsonResponse
     */
    public function postSubmission(Request $request, Charon $charon): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $user = app(User::class)->currentUser();

        Log::info("Preparing to send inline submission to tester with: ", [
            'charon' => $charon->id,
            'userId' => $user->id,
            'content' => $content
        ]);

        $areteRequest = $this->testerCommunicationService->prepareAreteRequest($charon,
            $user,
            $content['sourceFiles']);

        $response = $this->testerCommunicationService->sendInfoToTesterSync($areteRequest,
            $this->request->getUriForPath('/api/submissions/saveResults'));

        if ($response->getStatus() == 202) {
            try {
                $responseSubmission = $this->submissionService
                    ->prepareSubmissionResponse($this->saveResults($response, $charon, $user));

                return response()->json([
                    'message' => 'Testing the submission was successful',
                    'submission' => $responseSubmission
                ]);
            } catch (Exception $e) {
                Log::info("Exception when saving results" , ["exception" => $e]);
            }
        }

        return response()->json([
            'message' => 'Failed to send submission to tester'
        ]);
    }

    /**
     * Save submission results that come from tester.
     *
     * @param CharonViewTesterCallbackRequest $request
     * @param Charon $charon
     * @param \Zeizig\Moodle\Models\User $user
     *
     * @return Submission
     * @throws Exception
     */
    public function saveResults(CharonViewTesterCallbackRequest $request, Charon $charon,
                                \Zeizig\Moodle\Models\User $user): Submission
    {
        $usernames = collect($request->input('returnExtra.usernames'))
            ->map(function ($name) {
                return strtolower($name);
            })
            ->unique()
            ->values()
            ->all();

        $submission = $this->saveTesterFlow->saveTestersSyncResponse($request, $user,
            $charon, $usernames);

        $this->saveTesterFlow->hideUnneededFields($submission);

        return $submission;
    }
}
