<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\TesterCommunicationService;

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

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param SubmissionsRepository $submissionRepository
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        CourseSettingsRepository $courseSettingsRepository,
        SubmissionsRepository $submissionRepository
    ) {
        parent::__construct($request);

        $this->testerCommunicationService = $testerCommunicationService;
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->submissionRepository = $submissionRepository;
    }

    /**
     * Trigger retesting the student's submission.
     *
     * @param Submission $submission
     *
     * @return JsonResponse
     *
     * @throws SubmissionNoGitCallbackException
     */
    public function index(Submission $submission)
    {
        $gitCallback = $submission->gitCallback;
        if (! $gitCallback) {
            $exception = new SubmissionNoGitCallbackException('submission_git_callback_is_required');
            $exception->setSubmissionId($submission->id);

            throw $exception;
        }

        // TODO: Make this work
        $newGitCallback = $this->gitCallbacksRepository->save(
            $this->request->fullUrl(),
            $gitCallback->repo,
            $gitCallback->user
        );

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($submission->charon->course);

        $request = (new AreteRequestDto())
            ->setDockerContentRoot($submission->charon->docker_content_root)
            ->setDockerTestRoot($submission->charon->docker_test_root)
            ->setDockerExtra($submission->charon->tester_extra)
            ->setDockerTimeout($submission->charon->docker_timeout)
            ->setGitStudentRepo($newGitCallback->repo)
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setHash($submission->git_hash)
            ->setTestingPlatform($submission->charon->testerType->name)
            ->setSystemExtra($submission->charon->system_extra);

        $this->testerCommunicationService->sendGitCallback(
            $newGitCallback,
            $this->request->getUriForPath('/api/tester_callback'),
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
     * Retest the latest Submissions for every user for the given Charon
     *
     * TODO: HttpCommunicationService::sendInfoToTester is failing silently
     * TODO: Look into using a queue for firing off these requests
     *
     * @param Charon $charon
     *
     * @return JsonResponse
     */
    public function retestByCharon(Charon $charon): JsonResponse
    {
        $submissions = $this->submissionRepository->findLatestByCharon($charon->id);

        $submissionCount = sizeof($submissions);
        $successCount = 0;

        foreach ($submissions as $submission) {
            $submission->setRelation('charon', $charon);
            try {
                $this->index($submission);
                $successCount++;
            } catch (Exception $exception) {
               Log::error('Retest for submission ' . $submission->id . ' failed', [$exception]);
            }
        }

//        require_once __DIR__ . '/../../../../../classes/task/adhock.php';

        $task = new \mod_charon\task\adhock();
        $task->set_custom_data(['some payload']);
        $task->set_component('mod_charon');
        // TODO: pass in class name via "task_name" keyword, wrap this thing into a service.

        \core\task\manager::queue_adhoc_task($task);


//        $this->taskService->setCallback(function(array $arg) {
//            Log::debug('In callback', $arg);
//        })->setCustomData(['some payload'])->queue();

        return response()->json([
            'message' => 'Retesting has been triggered for ' . $successCount . ' Submissions',
            'all' => $submissionCount,
            'sent' => $successCount
        ]);
    }
}
