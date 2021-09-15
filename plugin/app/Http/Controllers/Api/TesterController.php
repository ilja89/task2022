<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Models\GitCallback;
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
     *
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postSubmission(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        Log::info("Inline submission input for the tester: ", [
            'charon' => $request->route('charon'),
            'userId' => app(User::class)->currentUserId(),
            'sourceFiles' => $content['sourceFiles'],
            ]);

        $areteRequest = $this->testerCommunicationService->prepareAreteRequest($request->route('charon'),
            app(User::class)->currentUserId(),
            $content['sourceFiles']);

        $response = $this->testerCommunicationService->sendInfoToTester($areteRequest,
            $this->request->getUriForPath('/api/submissions/saveResults'));

        if ($response->getStatus() == 202) {
            try {
                $responseSubmission = $this->submissionService
                    ->prepareSubmissionResponse($this->saveResults($response));

                return response()->json([
                    'message' => 'Testing the submission was successful',
                    'submission' => $responseSubmission
                ]);
            } catch (Exception $e) {
                Log::info("Exception when saving results" , ["exception" => $e]);
            }
        } else if ($response->getStatus() == 204) {
            return response()->json([
                'message' => 'Code has been sent to tester. Please refresh submissions in a while.'
            ]);
        }

        return response()->json([
            'message' => 'Failed to send submission to tester'
        ]);
    }

    /**
     * Save submission results that come from tester.
     *
     * @param CharonViewTesterCallbackRequest $request
     *
     * @return Submission
     * @throws Exception
     */
    public function saveResults(CharonViewTesterCallbackRequest $request)
    {
        Log::info("submissionresults", ["results" => $request]);

        $usernames = collect([$request->input('uniid')])
            ->merge($request->input('returnExtra.usernames'))
            ->map(function ($name) { return strtolower($name); })
            ->unique()
            ->values()
            ->all();

        $submission = $this->saveTesterFlow->run($request, new GitCallback(), $usernames,
            intval($request->input('returnExtra')['course']));

        $this->saveTesterFlow->hideUnneededFields($submission);

        return $submission;
    }
}
