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
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param SubmissionsRepository $submissionRepository
     * @param MoodleCron $cron
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        CourseSettingsRepository $courseSettingsRepository,
        SubmissionsRepository $submissionRepository,
        MoodleCron $cron
    ) {
        parent::__construct($request);
        $this->testerCommunicationService = $testerCommunicationService;
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->submissionRepository = $submissionRepository;
        $this->cron = $cron;
    }

    /**
     * Trigger retesting the student's submission.
     *
     * @param SubmissionsDTO $submission
     * @param string|null $requestUrl
     * @param string|null $callbackUrl
     *
     * @return JsonResponse
     * @throws SubmissionNoGitCallbackException
     */
    public function postFromInline(SubmissionsDTO $sentSubmission, string $requestUrl = null, string $callbackUrl = null) {


        $submission = $sentSubmission->getSubmission();

        Log::info("gitCallback --->" . $gitCallback);

        /*if (!$gitCallback) {
            $exception = new SubmissionNoGitCallbackException('submission_git_callback_is_required');
            $exception->setSubmissionId($submission->id);

            throw $exception;
        }*/
        $newGitCallback = $this->gitCallbacksRepository->save(
            $requestUrl ? $requestUrl : $this->request->fullUrl(),
            $gitCallback->repo,
            $gitCallback->user
        );
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($submission->charon->course);

        $sourceFile = new SourceFileDTO();
        $sourceFile->setPath($submission->charon->name);
        $sourceFile->setContent("Java function\n Java code");

        $request = (new AreteRequestDto())
            ->setDockerContentRoot($submission->charon->docker_content_root)
            ->setDockerTestRoot($submission->charon->docker_test_root)
            ->setDockerExtra($submission->charon->tester_extra)
            ->setDockerTimeout($submission->charon->docker_timeout)
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setHash($submission->git_hash)
            ->setTestingPlatform($submission->charon->testerType->name)
            ->setSystemExtra($submission->charon->system_extra)
            ->setSource([$sourceFile])
            ->setTimestamp($submission->git_timestamp);

        $this->testerCommunicationService->sendGitCallback(
            $newGitCallback,
            $callbackUrl ? $callbackUrl : $this->request->getUriForPath('/api/tester_callback'),
            $request->toArray()
        );

        Log::info("Sending to git");

        return response()->json([
            'message' => 'Testing triggered.'
        ]);
    }
}
