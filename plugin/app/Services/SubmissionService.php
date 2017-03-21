<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Helpers\RequestHandler;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\UserService;

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

    /** @var RequestHandler */
    private $requestHandler;

    /**
     * SubmissionService constructor.
     *
     * @param GradebookService $gradebookService
     * @param CharonGradingService $charonGradingService
     * @param RequestHandler $requestHandler
     */
    public function __construct(
        GradebookService $gradebookService,
        CharonGradingService $charonGradingService,
        RequestHandler $requestHandler
    )
    {
        $this->gradebookService = $gradebookService;
        $this->charonGradingService = $charonGradingService;
        $this->requestHandler = $requestHandler;
    }

    /**
     * Saves the Submission from the given request.
     * Also saves the Results and Submission Files.
     *
     * @param  Request $submissionRequest
     *
     * @return Submission
     */
    public function saveSubmission($submissionRequest)
    {
        $submission = $this->requestHandler->getSubmissionFromRequest($submissionRequest);
        $submission->save();

        $this->saveResults($submission, $submissionRequest['results']);
        $this->saveFiles($submission, $submissionRequest['files']);

        return $submission;
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
            $result = $this->requestHandler->getResultFromRequest($submission->id, $resultRequest);
            $result->save();
        }

        $this->includeCustomGrades($submission);
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
            $submissionFile = $this->requestHandler->getFileFromRequest($submission->id, $fileRequest);
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
     */
    public function updateSubmissionCalculatedResults(Submission $submission, $newResults)
    {
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
        /** @var Submission $submission */
        $submission = $charon->submissions()->create([
            'user_id' => $studentId,
            'git_hash' => '',
            'git_timestamp' => Carbon::now(),
            'stdout' => 'Manually created by teacher',
        ]);

        foreach ($charon->grademaps as $grademap) {
            $submission->results()->create([
                'grade_type_code' => $grademap->grade_type_code,
                'percentage' => 0,
                'calculated_result' => 0,
            ]);
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

        $params = [];
        foreach ($submission->results as $result) {
            $params[strtolower($result->getGrademap()->gradeItem->idnumber)] = $result->calculated_result;
        }

        return $this->gradebookService->calculateResultFromFormula(
            $charon->category->getGradeItem()->calculation, $params, $charon->course
        );
    }

    /**
     * Include custom grades for the given submission. If custom grademaps exist
     * will create new result for them.
     *
     * @param  Submission $submission
     *
     * @return void
     */
    private function includeCustomGrades(Submission $submission)
    {
        $charon = $submission->charon;

        foreach ($charon->grademaps as $grademap) {
            if ($grademap->gradeType->isCustomGrade()) {
                $result = new Result([
                    'submission_id'     => $submission->id,
                    'grade_type_code'   => $grademap->grade_type_code,
                    'percentage'        => 0,
                    'calculated_result' => 0,
                    'stdout'            => 'This result was automatically generated.',
                ]);
                $result->save();
            }
        }
    }
}
