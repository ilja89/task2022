<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Services\TesterCommunicationService;

class RetestController extends Controller
{
    /** @var TesterCommunicationService */
    protected $testerCommunicationService;
    /** @var GitCallbacksRepository */
    protected $gitCallbacksRepository;
    /** @var CourseSettingsRepository */
    protected $courseSettingsRepository;

    /**
     * RetestController constructor.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     */
    public function __construct(
        TesterCommunicationService $testerCommunicationService,
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        CourseSettingsRepository $courseSettingsRepository
    )
    {
        parent::__construct($request);

        $this->testerCommunicationService = $testerCommunicationService;
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
    }

    /**
     * Trigger retesting the student's submission.
     *
     * @param Submission $submission
     *
     * @return \Illuminate\Http\JsonResponse
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
            ->setProject($submission->charon->project_folder)
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
}
