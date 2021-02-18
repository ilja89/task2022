<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Repositories\UserRepository;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class SubmissionService.
 *
 * @package TTU\Charon\Services
 */
class SubmissionService
{
    /** @var GradebookService */
    private $gradebookService;

    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var AreteResponseParser */
    private $requestHandlingService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var UserRepository */
    private $userRepository;

    /**
     * SubmissionService constructor.
     *
     * @param GradebookService $gradebookService
     * @param CharonGradingService $charonGradingService
     * @param AreteResponseParser $requestHandlingService
     * @param SubmissionsRepository $submissionsRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        GradebookService $gradebookService,
        CharonGradingService $charonGradingService,
        AreteResponseParser $requestHandlingService,
        SubmissionsRepository $submissionsRepository,
        UserRepository $userRepository
    ) {
        $this->gradebookService = $gradebookService;
        $this->charonGradingService = $charonGradingService;
        $this->requestHandlingService = $requestHandlingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Saves the Submission from the given request.
     *
     * @param Request $submissionRequest
     * @param GitCallback $gitCallback
     * @param int $authorId
     *
     * @return Submission
     * @throws Exception
     */
    public function saveSubmission(Request $submissionRequest, GitCallback $gitCallback, int $authorId)
    {
        $submission = $this->requestHandlingService->getSubmissionFromRequest(
            $submissionRequest,
            $gitCallback->repo,
            $authorId
        );

        $submission->git_callback_id = $gitCallback->id;
        $submission->save();

        return $submission;
    }

    /**
     * Adds a new empty submission for the given user.
     *
     * @param Charon $charon
     * @param int $studentId
     *
     * @return Submission
     * @throws ModelNotFoundException
     */
    public function addNewEmptySubmission(Charon $charon, int $studentId)
    {
        $now = Carbon::now()->setTimezone('UTC');

        $student = $this->userRepository->findOrFail($studentId);

        /** @var Submission $submission */
        $submission = $charon->submissions()->create([
            'user_id' => $studentId,
            'git_hash' => '',
            'git_timestamp' => $now,
            'created_at' => $now,
            'updated_at' => $now,
            'stdout' => 'Manually created by teacher',
        ]);

        $submission->users()->save($student);

        foreach ($charon->grademaps as $grademap) {
            $this->submissionsRepository->saveNewEmptyResult($submission->id, $grademap->grade_type_code, '');
        }

        if ($this->charonGradingService->gradesShouldBeUpdated($submission, $studentId)) {
            $this->charonGradingService->updateGrade($submission, $studentId);
        }

        return $submission;
    }

    /**
     * Calculates the total grade for the given submission.
     *
     * @param Submission $submission
     *
     * @return float
     */
    public function calculateSubmissionTotalGrade(Submission $submission)
    {
        $charon = $submission->charon;
        $calculation = $charon->category->getGradeItem()->calculation;

        if ($calculation !== null) {
            $params = [];
            foreach ($submission->results as $result) {
                $params["gi" . $result->getGrademap()->gradeItem->id] = $result->calculated_result;
            }

            return round($this->gradebookService->calculateResultWithFormulaParams(
                $calculation, $params
            ), 3);
        } else {
            $sum = 0;
            foreach ($submission->results as $result) {
                $sum += $result->calculated_result;
            }

            return round($sum, 3);
        }
    }

    /**
     * Save the files from given results request (arete v2).
     *
     * @param int $submissionId
     * @param array $filesRequest
     *
     * @return void
     */
    public function saveFiles(int $submissionId, $filesRequest)
    {
        if ($filesRequest == null) {
            return;
        }

        Log::debug("Saving files: ", [sizeof($filesRequest)]);

        foreach ($filesRequest as $fileRequest) {
            $submissionFile = $this->requestHandlingService->getFileFromRequest($submissionId, $fileRequest, false);
            $submissionFile->save();
        }
    }

    /**
     * Include custom grades for the given submission. If custom grademaps exist
     * will create new result for them.
     *
     * @param Submission $submission
     *
     * @return void
     */
    public function includeUnsentGrades(Submission $submission)
    {
        $charon = $submission->charon;

        if (!$charon) {
            return;
        }

        foreach ($charon->grademaps as $grademap) {

            $result = $submission->results->first(function ($result) use ($grademap) {
                /** @var Result $result */
                return $result->grade_type_code === $grademap->grade_type_code;
            });

            if ($result !== null) {
                continue;
            }

            if ($grademap->persistent) {
                $this->submissionsRepository->carryPersistentResult(
                    $submission->id,
                    $submission->user_id,
                    $submission->charon_id,
                    $grademap->grade_type_code
                );
            } else {
                $this->submissionsRepository->saveNewEmptyResult(
                    $submission->id,
                    $grademap->grade_type_code,
                    'This result was automatically generated'
                );
            }
        }
    }
}
