<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Services\TesterCommunicationService;

class TesterController extends Controller
{
    /** @var TesterCommunicationService */
    protected $testerCommunicationService;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request
    )
    {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
    }

    /**
     * Trigger testing the student's inline submission.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postSubmission(Request $request): JsonResponse
    {
        Log::info("Inline submission input for the tester: ", [
            'charon' => $request->route('charon'),
            'userId' => $request->input('userId'),
            'sourceFiles' => $request->input('sourceFiles'),
            ]);

        $areteRequest = $this->testerCommunicationService->prepareAreteRequest($request->route('charon'),
            $request->input('userId'),
            json_decode(json_encode($request->input('sourceFiles'))));

        $this->testerCommunicationService->sendInfoToTester($areteRequest,
            $this->request->getUriForPath('/api/tester_callback'));

        return response()->json([
            'message' => 'Testing triggered.'
        ]);
    }
}
