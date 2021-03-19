<?php

namespace TTU\Charon\Tasks;

use Exception;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\SubmissionNoGitCallbackException;
use TTU\Charon\Http\Controllers\Api\RetestController;
use TTU\Charon\Repositories\SubmissionsRepository;

class RetestSubmissions implements AdhocTask
{
    /** @var SubmissionsRepository */
    private $submissionRepository;

    /** @var RetestController */
    private $retestController;

    /**
     * @param SubmissionsRepository $submissionRepository
     * @param RetestController $retestController
     */
    public function __construct(SubmissionsRepository $submissionRepository, RetestController $retestController) {
        $this->submissionRepository = $submissionRepository;
        $this->retestController = $retestController;
    }

    /**
     * Expecting argument in the following form:
     * stdClass(int $id, int $charon, int $total)
     *
     * @param mixed $arguments
     * @throws SubmissionNoGitCallbackException
     */
    public function execute($arguments)
    {
        $submission = $this->submissionRepository->find($arguments->id);

        Log::debug('Re-testing Submission ' . $arguments->id . ' for Charon ' . $arguments->charon);

        try {
            $this->retestController->index($submission);
        } catch (SubmissionNoGitCallbackException $exception) {
            Log::warning('Submission ' . $arguments->id . ' is missing git callback, unable to re-test');
        } catch (Exception $exception) {
            Log::error(
                'Failed to send submission ' . $arguments->id . ' to the tester, error: ' . $exception->getMessage(),
                $exception->getTrace()
            );
            throw $exception;
        }
    }
}
