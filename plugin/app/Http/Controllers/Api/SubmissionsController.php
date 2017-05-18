<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class SubmissionsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class SubmissionsController extends Controller
{
    /** @var SubmissionService */
    private $submissionService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /**
     * SubmissionsController constructor.
     *
     * @param  GradebookService $gradebookService
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param SubmissionsRepository $submissionsRepository
     */
    public function __construct(
        GradebookService $gradebookService,
        Request $request,
        SubmissionService $submissionService,
        SubmissionsRepository $submissionsRepository
    ) {
        parent::__construct($request);
        $this->submissionService     = $submissionService;
        $this->submissionsRepository = $submissionsRepository;
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
        $outputs = $this->submissionsRepository->findSubmissionOutputs($submission);
        return $outputs;
    }

    /**
     * Find a submission by its id.
     *
     * @param  Charon $charon
     * @param  int $submissionId
     *
     * @return Submission
     */
    public function findById(Charon $charon, $submissionId)
    {
        $submission = $this->submissionsRepository->findByIdWithoutOutputs($submissionId, $charon->getGradeTypeCodes());

        $submission->total_result = $this->submissionService->calculateSubmissionTotalGrade($submission);
        $submission->max_result   = $charon->category->getGradeItem()->grademax;
        $submission->order_nr = $this->submissionsRepository->getSubmissionOrderNumber($submission);

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

        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Submission saved!',
            ],
        ]);
    }

    /**
     * @param Charon $charon
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getByCharon(Charon $charon)
    {
        $submissions = $this->submissionsRepository->paginateSubmissionsByCharonUser(
            $charon,
            $this->request['user_id']
        );

        return $submissions;
    }
}
