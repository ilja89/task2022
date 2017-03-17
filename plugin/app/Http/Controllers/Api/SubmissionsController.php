<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Traits\GradesStudents;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class SubmissionsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class SubmissionsController extends Controller
{
    use GradesStudents;

    /** @var GradebookService */
    private $gradebookService;

    /** @var SubmissionService */
    private $submissionService;

    /**
     * SubmissionsController constructor.
     *
     * @param  GradebookService $gradebookService
     * @param Request $request
     * @param SubmissionService $submissionService
     */
    public function __construct(GradebookService $gradebookService, Request $request, SubmissionService $submissionService)
    {
        parent::__construct($request);
        $this->gradebookService = $gradebookService;
        $this->submissionService = $submissionService;
    }

    /**
     * Get all outputs for given submission. Also includes outputs for
     * results.
     *
     * @param  Submission $submission
     *
     * @return array
     */
    public function getOutputs(Submission $submission)
    {
        $outputs = [];
        $outputs['submission']['stdout'] = $submission->stdout;
        $outputs['submission']['stderr'] = $submission->stderr;
        foreach ($submission->results as $result) {
            if ($result->getGrademap() === null) {
                // If has no grademap - result exists but no grademap
                continue;
            }

            $outputs['results'][$result->id]['stdout'] = $result->stdout;
            $outputs['results'][$result->id]['stderr'] = $result->stderr;
        }

        return $outputs;
    }

    /**
     * Find a submission by its id.
     *
     * @param  Charon  $charon
     * @param  int  $submissionId
     *
     * @return Submission
     */
    public function findById(Charon $charon, $submissionId)
    {
        /** @var Submission $submission */
        $submission = Submission::with([
            'results' => function ($query) use ($charon) {
                // Only select results which have a corresponding grademap
                $query->whereIn('grade_type_code', $charon->getGradeTypes());
                $query->select(['id', 'submission_id', 'calculated_result', 'grade_type_code']);
            },
        ])
                                 ->where('id', $submissionId)
                                 ->where('charon_id', $charon->id)
                                 ->first([
                                     'id',
                                     'charon_id',
                                     'confirmed',
                                     'created_at',
                                     'git_hash',
                                     'git_commit_message',
                                     'git_timestamp',
                                     'user_id',
                                     'mail',
                                 ]);

        $params = [];
        foreach ($submission->results as $result) {
            $params[strtolower($result->getGrademap()->gradeItem->idnumber)] = $result->calculated_result;
        }

        $submission->total_result = $this->gradebookService->calculateResultFromFormula(
            $charon->category->getGradeItem()->calculation, $params, $charon->course
        );
        $submission->max_result = $charon->category->getGradeItem()->grademax;

        return $submission;
    }

    /**
     * Add a new empty submission to the given Charon. Empty means that all the
     * outputs are empty and all results are 0.
     *
     * @param Request $request
     * @param Charon $charon
     *
     * @return Submission
     */
    public function addNewEmpty(Request $request, Charon $charon)
    {
        $submission = $this->submissionService->addNewEmptySubmission($charon, $request['student_id']);
        return $submission;
    }

    /**
     * Saves the Submission results.
     *
     * @param  Charon $charon
     * @param  Submission $submission
     *
     * @return array
     */
    public function saveSubmission(Charon $charon, Submission $submission)
    {
        $newResults = $this->request['submission']['results'];

        $this->submissionService->updateSubmissionCalculatedResults($submission, $newResults);

        return [
            'status' => 'OK',
        ];
    }
}
