<?php

namespace TTU\Charon\Http\Controllers\Api;

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
        MoodleCron $cron
    ) {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->submissionRepository = $submissionRepository;
        $this->charonRepository = $charonRepository;
        $this->cron = $cron;
    }

    /**
     * Trigger testing the student's submission.
     *
     * @param Request $request
     * @param string|null $requestUrl
     * @param string|null $callbackUrl
     *
     * @return JsonResponse
     * @throws SubmissionNoGitCallbackException
     */
    public function postFromInline(Request $request, string $requestUrl = null, string $callbackUrl = null) {


        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($request->input('courseId'));

        $charon = $this->charonRepository->getCharonById($request->input('charonId'));

        // If tester requires files to be of SourceFileDTO then uncomment this
        // and change ->setSource input with $finalListofSource
        /*$sourceFiles = json_decode($request->input('sourceFiles'));
        $finalListofSource = [];
        foreach ($sourceFiles as $sourceFile) {
            $finalFile = new SourceFileDTO();
            $finalFile->setPath($sourceFile->path);
            $finalFile->setContent($sourceFile->content);
            array_push($finalListofSource, $finalFile);
        }*/

        $areteRequest = (new AreteRequestDto())
            ->setDockerContentRoot($charon->docker_content_root)
            ->setDockerTestRoot($charon->docker_test_root)
            ->setDockerExtra($charon->tester_extra)
            ->setDockerTimeout($charon->docker_timeout)
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setTestingPlatform($charon->testerType->name)
            ->setSystemExtra($charon->system_extra)
            ->setSource(json_decode($request->input('sourceFiles')));


        Log::info("Ready to send to tester------->" . print_r($areteRequest, true));

        return response()->json([
            'message' => 'Testing triggered.'
        ]);
    }
}
