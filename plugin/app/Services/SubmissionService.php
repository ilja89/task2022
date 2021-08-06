<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Exceptions\SubmissionNotFoundException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
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

    /** @var GrademapService */
    private $grademapService;

    /** @var SubmissionCalculatorService */
    private $submissionCalculatorService;

    /**
     * SubmissionService constructor.
     *
     * @param GradebookService $gradebookService
     * @param CharonGradingService $charonGradingService
     * @param AreteResponseParser $requestHandlingService
     * @param SubmissionsRepository $submissionsRepository
     * @param UserRepository $userRepository
     * @param GrademapService $grademapService
     * @param SubmissionCalculatorService $submissionCalculatorService
     */
    public function __construct(
        GradebookService $gradebookService,
        CharonGradingService $charonGradingService,
        AreteResponseParser $requestHandlingService,
        SubmissionsRepository $submissionsRepository,
        UserRepository $userRepository,
        GrademapService $grademapService,
        SubmissionCalculatorService $submissionCalculatorService
    ) {
        $this->gradebookService = $gradebookService;
        $this->charonGradingService = $charonGradingService;
        $this->requestHandlingService = $requestHandlingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->userRepository = $userRepository;
        $this->grademapService = $grademapService;
        $this->submissionCalculatorService = $submissionCalculatorService;
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
            $this->submissionsRepository->saveNewEmptyResult($submission->id, $studentId, $grademap->grade_type_code, '');
        }

        if ($this->charonGradingService->gradesShouldBeUpdated($submission, $studentId)) {
            $this->charonGradingService->updateGrade($submission, $studentId);
        }

        return $submission;
    }

    /**
     * Calculates the total grade for all Submission students
     *
     * @param Submission $submission
     *
     * @return array
     */
    public function calculateSubmissionTotalGrades(Submission $submission): array
    {
        $grades = [];

        foreach ($submission->users as $user) {
            $grades[$user->id] = $this->calculateSubmissionTotalGrade($submission, $user->id);
        }

        return $grades;
    }

    /**
     * Calculates the total grade for the given submission.
     *
     * @param Submission $submission
     * @param int $user_id
     *
     * @return float
     */
    public function calculateSubmissionTotalGrade(Submission $submission, int $user_id): float
    {
        $charon = $submission->charon;
        $calculation = $charon->category->getGradeItem()->calculation;

        if ($calculation == null) {
            $sum = 0;
            foreach ($submission->results as $result) {
                if ($result->user_id == $user_id) {
                    $sum += $result->calculated_result;
                }
            }

            return round($sum, 3);
        }

        $params = $this->grademapService->findFormulaParams(
            $calculation,
            $submission->results,
            $user_id
        );

        return round($this->gradebookService->calculateResultWithFormulaParams($calculation, $params), 3);
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
     * @param int $user_id
     *
     * @return void
     */
    public function includeUnsentGrades(Submission $submission, int $user_id)
    {
        $charon = $submission->charon;

        if (!$charon) {
            return;
        }

        foreach ($charon->grademaps as $grademap) {
            $existing = $submission->results()
                ->where('grade_type_code', $grademap->grade_type_code)
                ->where('user_id', $user_id)
                ->count();

            if ($existing > 0) {
                continue;
            }

            if ($grademap->persistent) {
                $this->submissionsRepository->carryPersistentResult(
                    $submission->id,
                    $user_id,
                    $submission->charon_id,
                    $grademap->grade_type_code
                );
            } else {
                $this->submissionsRepository->saveNewEmptyResult(
                    $submission->id,
                    $user_id,
                    $grademap->grade_type_code,
                    'This result was automatically generated'
                );
            }
        }
    }

    /**
     * Find the most suitable submission to register a defense with and return its identifier
     * or throw if no suitable submission exist.
     *
     * @param Charon $charon
     * @param int $userId
     *
     * @return Submission
     * @throws SubmissionNotFoundException
     */
    public function findSubmissionToDefend(Charon $charon, int $userId): Submission
    {
        $submissions = $this->submissionsRepository->getUngradedSubmissions($charon->id, $userId);

        if (count($submissions) < 1) {
            throw new SubmissionNotFoundException("no_submission");
        }

        $submission = $this->findMostSuitableSubmission($submissions, $charon);

        if ($submission === null) {
            throw new SubmissionNotFoundException("no_submission");
        }

        return $submission;
    }

    /**
     * Pick the most suitable submission from the given array of submissions or
     * return null if none there are no suitable submissions in given array.
     *
     * @param array $submissions
     * @param Charon $charon
     *
     * @return Submission|null
     */
    private function findMostSuitableSubmission(array $submissions, Charon $charon)
    {
        $bestScore = -1;
        $bestSubmission = null;
        $defenseThreshold = $charon->defense_threshold;

        foreach ($submissions as $submission) {
            if ($this->submissionCalculatorService->isSubmissionStyleOk($submission)) {

                $submissionWeightedScore = $this->submissionCalculatorService
                    ->calculateSubmissionWeightedScore($submission);
                if ($submissionWeightedScore >= $defenseThreshold && $bestScore < $submissionWeightedScore) {
                    $bestSubmission = $submission;
                    $bestScore = $submissionWeightedScore;
                }
            }
        }

        return $bestSubmission;
    }
}
