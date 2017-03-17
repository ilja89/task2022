<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Traits\GradesStudents;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\UserService;

/**
 * Class SubmissionService.
 *
 * @package TTU\Charon\Services
 */
class SubmissionService
{
    use GradesStudents;

    /** @var UserService */
    protected $userService;

    /** @var GradebookService */
    private $gradebookService;

    /**
     * SubmissionService constructor.
     *
     * @param UserService $userService
     * @param GradebookService $gradebookService
     */
    public function __construct(UserService $userService, GradebookService $gradebookService)
    {
        $this->userService = $userService;
        $this->gradebookService = $gradebookService;
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
        $submission = $this->getSubmissionFromRequest($submissionRequest);
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
    public function saveResults($submission, $resultsRequest)
    {
        foreach ($resultsRequest as $resultRequest) {
            $result = $this->getResultFromRequest($submission->id, $resultRequest);
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
    public function saveFiles($submission, $filesRequest)
    {
        foreach ($filesRequest as $fileRequest) {
            $submission->files()->save(new SubmissionFile([
                'path'     => $fileRequest['path'],
                'contents' => $fileRequest['contents'],
            ]));
        }
    }

    /**
     * Check if the given Charon has any submissions which are confirmed.
     *
     * @param  integer $charonId
     * @param  integer $userId
     *
     * @return boolean
     */
    public function charonHasConfirmedSubmission($charonId, $userId)
    {
        /** @var Submission $submission */
        $submission = Submission::where('charon_id', $charonId)
                                ->where('confirmed', 1)
                                ->where('user_id', $userId)
                                ->get();

        return ! $submission->isEmpty();
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

        $this->updateGradeIfApplicable($submission, true);
        $this->confirmSubmission($submission);

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
        $this->updateGradeIfApplicable($submission);

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
     * Gets the Submission from the given request.
     * The request should have the following keys: git_timestamp, charon_id, uni_id, git_hash.
     * Mail, stdout, stderr are optional.
     *
     * @param  Request $request
     *
     * @return Submission
     */
    private function getSubmissionFromRequest($request)
    {
        $gitTimestamp = isset($request['git_timestamp'])
            ? Carbon::createFromTimestamp($request['git_timestamp'], config('app.timezone'))
            : Carbon::now(config('app.timezone'));
        $gitTimestamp->setTimezone('UTC');

        return new Submission([
            'charon_id'          => $request['charon_id'],
            'user_id'            => $this->userService->findUserByIdNumber($request['uni_id'])->id,
            'git_hash'           => $request['git_hash'],
            'git_timestamp'      => $gitTimestamp,
            'mail'               => isset($request['mail']) ? $request['mail'] : null,
            'stdout'             => isset($request['stdout']) ? $request['stdout'] : null,
            'stderr'             => isset($request['stderr']) ? $request['stderr'] : null,
            'git_commit_message' => isset($request['git_commit_message']) ? $request['git_commit_message'] : null,
        ]);
    }

    /**
     * This gets the result from given request. The calculated result is set to 0 by
     * default and will be calculated later.
     *
     * Example request:
     * {
     *     "grade_type_code": 1,
     *     "percentage": 100,
     *     "stdout": "Some result specific stdout",  // Optional
     *     "stderr": "Some result specific stderr"  // Optional
     * }
     *
     * @param  integer $submissionId
     * @param  array $request
     *
     * @return Result
     */
    private function getResultFromRequest($submissionId, $request)
    {
        return new Result([
            'submission_id'     => $submissionId,
            'grade_type_code'   => $request['grade_type_code'],
            'percentage'        => floatval($request['percentage']) / 100,
            'calculated_result' => 0,
            'stdout'            => isset($request['stdout']) ? $request['stdout'] : null,
            'stderr'            => isset($request['stderr']) ? $request['stderr'] : null,
        ]);
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
