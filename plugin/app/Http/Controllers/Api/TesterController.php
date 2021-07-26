<?php

namespace TTU\Charon\Http\Controllers\Api;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Dto\SourceFileDTO;
use TTU\Charon\Dto\SubmissionsDTO;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Facades\MoodleCron;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\HttpCommunicationService;
use TTU\Charon\Services\TesterCommunicationService;

class TesterController extends Controller
{

    /** @var TesterCommunicationService */
    protected $testerCommunicationService;

    /** @var GitCallbacksRepository */
    protected $gitCallbacksRepository;

    /** @var CourseSettingsRepository */
    protected $courseSettingsRepository;

    /** @var SubmissionsRepository */
    protected $submissionRepository;

    /** @var MoodleCron */
    protected $cron;

    /**
     * @var CharonRepository
     */
    private $charonRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param SubmissionsRepository $submissionRepository
     * @param CharonRepository $charonRepository
     * @param MoodleCron $cron
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        CourseSettingsRepository $courseSettingsRepository,
        SubmissionsRepository $submissionRepository,
        CharonRepository $charonRepository,
        UserRepository $userRepository,
        MoodleCron $cron
    ) {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->submissionRepository = $submissionRepository;
        $this->charonRepository = $charonRepository;
        $this->userRepository = $userRepository;
        $this->cron = $cron;
    }

    /**
     * Trigger testing the student's inline submission.
     *
     * @param Request $request
     * @param string|null $requestUrl
     * @param string|null $callbackUrl
     *
     * @return JsonResponse
     * @throws SubmissionNoGitCallbackException
     */
    public function postFromInline(Request $request, string $requestUrl = null, string $callbackUrl = null) {

        Log::info("Inline submission input --->"
            . print_r($request->input('charonId'), true)
            . print_r($request->input('sourceFiles'), true)
            . print_r($request->input('userId')));

        $charon = $this->charonRepository->getCharonById($request->input('charonId'));

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($charon->course);

        $user = $this->userRepository->find($request->input('userId'));

        // If tester requires files to be of SourceFileDTO then uncomment this
        // and change ->setSource input with $finalListofSource
        $sourceFiles = json_decode($request->input('sourceFiles'));
        $finalListofSource = [];
        foreach ($sourceFiles as $sourceFile) {
            $finalFile = new SourceFileDTO();
            $finalFile->setPath($sourceFile->path);
            $finalFile->setContent($sourceFile->content);
            array_push($finalListofSource, $finalFile);
        }

        $areteRequest = (new AreteRequestDto())
            ->setDockerContentRoot($charon->docker_content_root)
            ->setDockerTestRoot($charon->docker_test_root)
            ->setDockerExtra($charon->tester_extra)
            ->setDockerTimeout($charon->docker_timeout)
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setTestingPlatform($charon->testerType->name)
            ->setSystemExtra($charon->system_extra)
            ->setSource($finalListofSource)
            ->setUniid($user->username);


        Log::info("Ready to send to tester------->" . print_r($areteRequest, true));

        try {
            $this->testerCommunicationService->sendInfoToTester($areteRequest, $this->request->getUriForPath('/api/tester_callback'));
        } catch (GuzzleException $e) {
            Log::error($e);
        }

        return response()->json([
            'message' => 'Testing triggered.'
        ]);
    }
}
