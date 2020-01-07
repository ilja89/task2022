<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
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

    /** @var RequestHandlingService */
    private $requestHandlingService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /**
     * SubmissionService constructor.
     *
     * @param GradebookService $gradebookService
     * @param CharonGradingService $charonGradingService
     * @param RequestHandlingService $requestHandlingService
     * @param SubmissionsRepository $submissionsRepository
     */
    public function __construct(
        GradebookService $gradebookService,
        CharonGradingService $charonGradingService,
        RequestHandlingService $requestHandlingService,
        SubmissionsRepository $submissionsRepository
    )
    {
        $this->gradebookService       = $gradebookService;
        $this->charonGradingService   = $charonGradingService;
        $this->requestHandlingService = $requestHandlingService;
        $this->submissionsRepository  = $submissionsRepository;
    }

    /**
     * Saves the Submission from the given request.
     * Also saves the Results and Submission Files.
     *
     * @param  Request $submissionRequest
     * @param  GitCallback  $gitCallback
     *
     * @return Submission
     */
    public function saveSubmission($submissionRequest, $gitCallback)
    {
        $submission = null;
        $isNewArete = $submissionRequest->input("version") == "arete_2.0";
        if ($isNewArete) {
            $submission = $this->requestHandlingService->getSubmissionFromNewRequest($submissionRequest, $gitCallback);
        } else {
            $submission = $this->requestHandlingService->getSubmissionFromRequest($submissionRequest);
        }
        $submission->git_callback_id = $gitCallback->id;
        $submission->save();

        if ($isNewArete) {

            // style
            $styleError = false;
            foreach ($submissionRequest['errors'] as $error) {
                if (isset($error['kind']) && $error['kind'] == 'style error') {
                    $styleError = true;
                    break;
                }
            }
            if (true) {
                $result = new Result([
                    'submission_id'     => $submission->id,
                    'grade_type_code'   => 101,
                    'percentage'        => $styleError ? 0 : 1,
                    'calculated_result' => 0,
                    'stdout'            => null,
                    'stderr'            => null,
                ]);
                $result->save();
            }
            $this->saveNewResults($submission, $submissionRequest['testSuites']);

            $this->saveNewFiles($submission, $submissionRequest['files']);

        } else {
            $this->saveResults($submission, $submissionRequest['results']);
            $this->saveFiles($submission, $submissionRequest['files']);
        }

        return $submission;
    }

    /**
     * Save the results from given results request (arete v2).
     *
     * @param  Submission $submission
     * @param  array $resultsRequest
     *
     * @return void
     */
    private function saveNewResults($submission, $resultsRequest)
    {
        $gradeCode = 1;
        foreach ($resultsRequest as $resultRequest) {
            $result = $this->requestHandlingService->getResultFromNewRequest($submission->id, $resultRequest, $gradeCode++);
            $result->save();
        }

        $this->includeUnsentGrades($submission);
    }

    /**
     * Save the files from given results request (arete v2).
     *
     * @param  Submission $submission
     * @param  array $filesRequest
     *
     * @return void
     */
    private function saveNewFiles($submission, $filesRequest)
    {
        foreach ($filesRequest as $fileRequest) {
            $submissionFile = $this->requestHandlingService->getFileFromNewRequest($submission->id, $fileRequest, false);
            $submissionFile->save();
        }
    }

    /**
     * Save the results from given results request.
     *
     * @param  Submission $submission
     * @param  array $resultsRequest
     *
     * @return void
     */
    private function saveResults($submission, $resultsRequest)
    {
        foreach ($resultsRequest as $resultRequest) {
            $result = $this->requestHandlingService->getResultFromRequest($submission->id, $resultRequest);
            $result->save();
        }

        $this->includeUnsentGrades($submission);
    }

    /**
     * Save the files from given results request.
     *
     * @param  Submission $submission
     * @param  array $filesRequest
     *
     * @return void
     */
    private function saveFiles($submission, $filesRequest)
    {
        foreach ($filesRequest as $fileRequest) {
            $submissionFile = $this->requestHandlingService->getFileFromRequest($submission->id, $fileRequest);
            $submissionFile->save();
        }
    }

    /**
     * Updates the given submissions' results with the given new results.
     *
     * @param  Submission  $submission
     * @param  array  $newResults
     *
     * @return Submission
     * @throws ResultPointsRequiredException
     */
    public function updateSubmissionCalculatedResults(Submission $submission, $newResults)
    {
        foreach ($newResults as $result) {
            if ($result['calculated_result'] !== '0' && ! $result['calculated_result']) {
                throw (new ResultPointsRequiredException('result_points_are_required'))
                    ->setResultId($result['id']);
            }
        }

        foreach ($newResults as $result) {

            $existingResult = $submission->results->first(function ($resultLoop) use ($result) {
                return $resultLoop->id == $result['id'];
            });

            $existingResult->calculated_result = $result['calculated_result'];
            $existingResult->save();
        }

        $this->charonGradingService->updateGradeIfApplicable($submission, true);
        $this->charonGradingService->confirmSubmission($submission);

        return $submission;
    }

    /**
     * Adds a new empty submission for the given user.
     *
     * @param Charon $charon
     * @param  int $studentId
     *
     * @return Submission
     */
    public function addNewEmptySubmission(Charon $charon, $studentId)
    {
        $now = Carbon::now(config('app.timezone'));
        $now = $now->setTimezone('UTC');
        /** @var Submission $submission */
        $submission = $charon->submissions()->create([
            'user_id' => $studentId,
            'git_hash' => '',
            'git_timestamp' => $now,
            'created_at' => $now,
            'updated_at' => $now,
            'stdout' => 'Manually created by teacher',
        ]);

        foreach ($charon->grademaps as $grademap) {
            $this->submissionsRepository->saveNewEmptyResult($submission->id, $grademap->grade_type_code, '');
        }

        $this->charonGradingService->updateGradeIfApplicable($submission);

        return $submission;
    }

    /**
     * Calculates the total grade for the given submission.
     *
     * @param  Submission $submission
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
                $params[strtolower($result->getGrademap()->gradeItem->idnumber)] = $result->calculated_result;
            }

            return $this->gradebookService->calculateResultFromFormula(
                $calculation, $params, $charon->course
            );
        } else {
            $sum = 0;
            foreach ($submission->results as $result) {
                $sum += $result->calculated_result;
            }

            return $sum;
        }
    }

    /**
     * Include custom grades for the given submission. If custom grademaps exist
     * will create new result for them.
     *
     * @param  Submission $submission
     *
     * @return void
     */
    private function includeUnsentGrades(Submission $submission)
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

            $this->submissionsRepository->saveNewEmptyResult(
                $submission->id,
                $grademap->grade_type_code,
                'This result was automatically generated'
            );
        }
    }
}
