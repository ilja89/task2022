<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\Course;
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
    /** @var CharonRepository */
    private $charonRepository;

    /**
     * SubmissionsController constructor.
     *
     * @param  GradebookService $gradebookService
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param SubmissionsRepository $submissionsRepository
     * @param CharonRepository $charonRepository
     */
    public function __construct(
        GradebookService $gradebookService,
        Request $request,
        SubmissionService $submissionService,
        SubmissionsRepository $submissionsRepository,
        CharonRepository $charonRepository
    ) {
        parent::__construct($request);
        $this->submissionService     = $submissionService;
        $this->submissionsRepository = $submissionsRepository;
        $this->charonRepository = $charonRepository;
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
     * @param Submission $submission
     *
     * @return Submission
     */
    public function findById(Submission $submission)
    {
        $charon = $this->charonRepository->findBySubmission($submission->id);
        $submission = $this->submissionsRepository->findByIdWithoutOutputs(
            $submission->id,
            $charon->getGradeTypeCodes()
        );

        $submission->total_result = $this->submissionService->calculateSubmissionTotalGrade($submission);
        $submission->max_result   = $charon->category->getGradeItem()->grademax;
        $submission->order_nr = $this->submissionsRepository->getSubmissionOrderNumber($submission);

        return $submission->makeHidden(['charon', 'grader_id']);
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

    /**
     * Find the latest submissions in the given course.
     *
     * @param Course $course
     *
     * @return Collection|Charon[]
     */
    public function findLatest(Course $course)
    {
        $submissions = $this->submissionsRepository->findLatestSubmissions($course->id);

        foreach ($submissions as $submission) {
            $submission->makeHidden(['charon_id', 'user_id']);
        }

        return $submissions;
    }
}
