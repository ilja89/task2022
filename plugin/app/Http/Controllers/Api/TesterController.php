<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Services\Flows\SaveTesterCallback;
use TTU\Charon\Services\TesterCommunicationService;

class TesterController extends Controller
{
    /** @var TesterCommunicationService */
    protected $testerCommunicationService;

    /** @var SaveTesterCallback */
    private $saveTesterFlow;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param SaveTesterCallback $saveTesterFlow
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        SaveTesterCallback $saveTesterFlow
    )
    {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->saveTesterFlow = $saveTesterFlow;
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
            'userId' => $content['userId'],
            'sourceFiles[]' => $content['sourceFiles']
            ]);

        $areteRequest = $this->testerCommunicationService->prepareAreteRequest($request->route('charon'),
            $content['userId'],
            $content['sourceFiles']);

        $this->testerCommunicationService->sendInfoToTester($areteRequest,
            $this->request->getUriForPath('/api/submissions/saveResults'));

        return response()->json([
            'message' => 'Testing triggered.'
        ]);
    }

    /**
     * Save submission results that come from tester.
     *
     * @param CharonViewTesterCallbackRequest $request
     *
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
    }
}
