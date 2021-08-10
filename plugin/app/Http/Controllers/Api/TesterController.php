<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Dto\SourceFileDTO;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\Flows\SaveTesterCallback;
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

    /** @var SaveTesterCallback */
    private $saveTesterFlow;

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
        Request $request,
        CourseSettingsRepository $courseSettingsRepository,
        CharonRepository $charonRepository,
        UserRepository $userRepository,
        SaveTesterCallback $saveTesterFlow
    )
    {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->charonRepository = $charonRepository;
        $this->userRepository = $userRepository;
        $this->saveTesterFlow = $saveTesterFlow;
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
        /*Log::info("Inline submission input for the tester: ", [
            'charon' => $request->route('charon'),
            'userId' => $request->input('userId'),
            'sourceFiles' => $request->input('sourceFiles'),
            ]);*/

        $charon = $this->charonRepository->getCharonById($request->route('charon'));

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($charon->course);

        $user = $this->userRepository->find($request->input('userId'));

        $finalListofSource = [];
        $sourceFiles = json_decode(json_encode($request->input('sourceFiles')));
        foreach ($sourceFiles as $sourceFile) {
            $finalFile = new SourceFileDTO();
            $finalFile->setPath($sourceFile->path);
            $finalFile->setContent($sourceFile->content);
            array_push($finalListofSource, $finalFile->toArray());
        }

        $finalListofSlugs = [];
        array_push($finalListofSlugs, $charon->project_folder);

        $areteRequest = (new AreteRequestDto())
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setTestingPlatform($charon->testerType->name)
            ->setSlugs($finalListofSlugs)
            ->setSource($finalListofSource)
            ->setReturnExtra(["course" => $charon->course])
            ->setUniid($user->username);

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
