<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Facades\MoodleCron;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\TesterCommunicationService;
use TTU\Charon\Tasks\RetestSubmissions;

class RetestController extends Controller
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
     * @param Submission $submission
     * @param string|null $requestUrl
     * @param string|null $callbackUrl
     *
     * @return JsonResponse
     * @throws SubmissionNoGitCallbackException
     */
    public function index(Submission $submission, string $requestUrl = null, string $callbackUrl = null)
    {
        $gitCallback = $submission->gitCallback;
        if (!$gitCallback) {
            $exception = new SubmissionNoGitCallbackException('submission_git_callback_is_required');
            $exception->setSubmissionId($submission->id);

            throw $exception;
        }

        // TODO: Make this work
        $newGitCallback = $this->gitCallbacksRepository->save(
            $requestUrl ? $requestUrl : $this->request->fullUrl(),
            $gitCallback->repo,
            $gitCallback->user
        );

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($submission->charon->course);

        $groupUsernames = $submission->users->map(function ($user) {
            return $user->username;
        })->all();

        $request = (new AreteRequestDto())
            ->setDockerContentRoot($submission->charon->docker_content_root)
            ->setDockerTestRoot($submission->charon->docker_test_root)
            ->setDockerExtra($submission->charon->tester_extra)
            ->setDockerTimeout($submission->charon->docker_timeout)
            ->setGitStudentRepo($newGitCallback->repo)
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setHash($submission->git_hash)
            ->setTestingPlatform($submission->charon->testerType->name)
            ->setSystemExtra($submission->charon->system_extra)
            ->setReturnExtra(['usernames' => $groupUsernames])
            ->setTimestamp($submission->git_timestamp);

        $this->testerCommunicationService->sendGitCallback(
            $newGitCallback,
            $callbackUrl ? $callbackUrl : $this->request->getUriForPath('/api/tester_callback'),
            $request->toArray()
        );

        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Retesting has been triggered.',
            ],
        ], 200);
    }

    /**
     * Retest the latest Submissions for every user for the given Charon.
     *
     * @param Charon $charon
     *
     * @return JsonResponse
     */
    public function retestByCharon(Charon $charon): JsonResponse
    {
        $submissions = $this->submissionRepository->findLatestByCharon($charon->id);

        $count = sizeof($submissions);
        $delay = Config::get('queue.moodle.retest_delay');
        $requestUrl = $this->request->fullUrl();
        $callbackUrl = $this->request->getUriForPath('/api/tester_callback');

        foreach ($submissions as $key => $submission) {
            $this->cron->enqueue(
                RetestSubmissions::class,
                [
                    'id' => $submission,
                    'charon' => $charon->id,
                    'total' => $count,
                    'requestUrl' => $requestUrl,
                    'callbackUrl' => $callbackUrl,
                ],
                $delay * $key
            );
        }

        return response()->json([
            'message' => 'Retesting has been triggered for ' . $count . ' Submissions',
            'total' => $count,
        ]);
    }
}
