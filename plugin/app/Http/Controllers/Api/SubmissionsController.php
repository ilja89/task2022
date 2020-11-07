<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;
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
    /** @var FilesController */
    private $filesController;

    /**
     * SubmissionsController constructor.
     *
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param SubmissionsRepository $submissionsRepository
     * @param CharonRepository $charonRepository
     * @param FilesController $filesController
     */
    public function __construct(
        Request $request,
        SubmissionService $submissionService,
        SubmissionsRepository $submissionsRepository,
        CharonRepository $charonRepository,
        FilesController $filesController
    )
    {
        parent::__construct($request);
        $this->submissionService = $submissionService;
        $this->submissionsRepository = $submissionsRepository;
        $this->charonRepository = $charonRepository;
        $this->filesController = $filesController;
    }

    /**
     * Get all outputs for given submission. Also includes outputs for
     * results.
     *
     * @param Submission $submission
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
        $submission->max_result = $charon->category->getGradeItem()->grademax;
        $submission->order_nr = $this->submissionsRepository->getSubmissionOrderNumber($submission);
        $submission->files = $this->filesController->index($submission);
        $submission->outputs = $this->getOutputs($submission);

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
     * @param Charon $charon
     * @param Submission $submission
     *
     * @return array
     * @throws \TTU\Charon\Exceptions\ResultPointsRequiredException
     */
    public function saveSubmission(Charon $charon, Submission $submission)
    {
        $newResults = $this->request['submission']['results'];

        $newSubmission = $this->submissionService->updateSubmissionCalculatedResults($charon, $submission, $newResults);

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
     * Find all confirmed submissions for user.
     *
     * @param Course $course
     * @param User $user
     *
     * @return array
     */
    public function getByUser(Course $course, User $user)
    {
        return $this->submissionsRepository->findConfirmedSubmissionsForUser($course->id, $user->id);
    }

    /**
     * Find average Charon submission result in the given course.
     *
     * @param Course $course
     *
     * @return array
     */
    public function findBestAverageCourseSubmissions(Course $course)
    {
        return $this->submissionsRepository->findBestAverageCourseSubmissions($course->id);
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

    public function findSubmissionCounts(Course $course)
    {
        return $this->submissionsRepository->findSubmissionCounts($course->id);
    }

    /**
     * Find all Submissions for report table.
     *
     * @param Course $course
     *
     * @return array
     */
    public function findAllSubmissionsForReport(Course $course, $page, $perPage, $sortField, $sortType, $firstName = null,
                                                $lastName = null, $exerciseName = null, $isConfirmed = null,
                                                $gitTimestampForStartDate = null, $gitTimestampForEndDate = null)
    {
        return $this->submissionsRepository->findAllSubmissionsForReport($course->id, $page, $perPage, $sortField, $sortType,
            $firstName, $lastName, $exerciseName, $isConfirmed, $gitTimestampForStartDate, $gitTimestampForEndDate);
    }
}
