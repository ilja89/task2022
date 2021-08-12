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

    /** @var CourseSettingsRepository */
    protected $courseSettingsRepository;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var UserRepository */
    private $userRepository;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param CharonRepository $charonRepository
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
    public function postFromInline(Request $request): JsonResponse
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
