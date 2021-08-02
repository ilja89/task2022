<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Dto\SourceFileDTO;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\UserRepository;
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
        Request $request,
        CourseSettingsRepository $courseSettingsRepository,
        CharonRepository $charonRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->charonRepository = $charonRepository;
        $this->userRepository = $userRepository;
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
        Log::info("Inline submission input: ", [
            'charonId' => $request->input('charonId'),
            'userId' => $request->input('userId'),
            'sourceFiles' => $request->input('sourceFiles'),
            ]);

        $charon = $this->charonRepository->getCharonById($request->input('charonId'));

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
            ->setUniid($user->username);


        $this->testerCommunicationService->sendInfoToTester($areteRequest,
            $this->request->getUriForPath('/api/tester_callback'));

        return response()->json([
            'message' => 'Testing triggered.'
        ]);
    }
}
