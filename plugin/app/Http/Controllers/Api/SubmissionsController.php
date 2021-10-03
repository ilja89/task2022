<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\Flows\TeacherModifiesSubmission;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

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

    /** @var TeacherModifiesSubmission */
    private $teacherModifiesSubmission;

    /**
     * SubmissionsController constructor.
     *
     * @param Request $request
     * @param SubmissionService $submissionService
     * @param SubmissionsRepository $submissionsRepository
     * @param CharonRepository $charonRepository
     * @param FilesController $filesController
     * @param TeacherModifiesSubmission $teacherModificationFlow
     */
    public function __construct(
        Request $request,
        SubmissionService $submissionService,
        SubmissionsRepository $submissionsRepository,
        CharonRepository $charonRepository,
        FilesController $filesController,
        TeacherModifiesSubmission $teacherModificationFlow
    ) {
        parent::__construct($request);
        $this->submissionService = $submissionService;
        $this->submissionsRepository = $submissionsRepository;
        $this->charonRepository = $charonRepository;
        $this->filesController = $filesController;
        $this->teacherModifiesSubmission = $teacherModificationFlow;
    }

    /**
     * Find a submission by its id.
     *
     * Return provided user_id as the submission owner for context
     *
     * @param Submission $submission
     *
     * @return Submission
     */
    public function findById(Submission $submission)
    {
        $studentId = $this->getStudentId($submission);
        $charon = $this->charonRepository->findBySubmission($submission->id);
        $submission = $this->submissionsRepository->findById($submission->id, $charon->getGradeTypeCodes());

        $submission->total_results = $this->submissionService->calculateSubmissionTotalGrades($submission);
        $submission->max_result = $charon->category->getGradeItem()->grademax;
        $submission->course_order_nr = $this->submissionsRepository->getSubmissionCourseOrderNumber($submission, $studentId);
        $submission->charon_order_nr = $this->submissionsRepository->getSubmissionCharonOrderNumber($submission, $studentId);
        $submission->files = $this->filesController->index($submission);
        $submission->user_id = $studentId;

        return $submission->makeHidden(['charon', 'grader_id']);
    }

    /**
     * Add a new empty submission to the given Charon.
     *
     * Empty means that all the outputs are empty and all results are 0.
     * Used by teacher when a git submission is not an option or Charon has no tests.
     *
     * Currently supported only for individual submissions. Frontend support needed for group submissions.
     *
     * @param Request $request
     * @param Charon $charon
     *
     * @return Submission
     */
    public function addNewEmpty(Request $request, Charon $charon)
    {
        return $this->submissionService->addNewEmptySubmission($charon, intval($request['student_id']));
    }

    /**
     * Saves the Submission results.
     *
     * When a teacher assigns a new result for a submission, commonly during a defences.
     *
     * @param Submission $submission
     *
     * @return JsonResponse
     * @throws ResultPointsRequiredException
     */
    public function saveSubmission(Charon $charon, Submission $submission)
    {
        $results = $this->request->input('submission.results');

        foreach ($results as $result) {
            if ($result['calculated_result'] !== '0' && !$result['calculated_result']) {
                throw (new ResultPointsRequiredException('result_points_are_required'))->setResultId($result['id']);
            }
        }

        $this->teacherModifiesSubmission->run($submission, $results);

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
     * @return Paginator
     */
    public function getByCharon(Charon $charon)
    {
        return $this->submissionsRepository->paginateSubmissionsByCharonUser(
            $charon,
            intval($this->request['user_id'])
        );
    }

    /**
     * Find the latest submissions in the given course.
     *
     * @param int $charonId
     *
     * @return Submission[]
     */
    public function findLatestByCharonId(int $charonId)
    {
        return $this->submissionsRepository->findLatestSubmissionsForCharon($charonId);
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
        return $this->submissionsRepository->findGradedCharonsByUser($course->id, $user->id);
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
     * @param $page
     * @param $perPage
     * @param $sortField
     * @param $sortType
     * @param null $firstName
     * @param null $lastName
     * @param null $exerciseName
     * @param null $isConfirmed
     * @param null $gitTimestampForStartDate
     * @param null $gitTimestampForEndDate
     * @return array
     */
    public function findAllSubmissionsForReport(
        Course $course,
        $page,
        $perPage,
        $sortField,
        $sortType,
        $firstName = null,
        $lastName = null,
        $exerciseName = null,
        $isConfirmed = null,
        $gitTimestampForStartDate = null,
        $gitTimestampForEndDate = null
    ) {
        return $this->submissionsRepository->findAllSubmissionsForReport(
            $course->id,
            $page,
            $perPage,
            $sortField,
            $sortType,
            $firstName,
            $lastName,
            $exerciseName,
            $isConfirmed,
            $gitTimestampForStartDate,
            $gitTimestampForEndDate
        );
    }

    /**
     * Make sure the provided student is bound to submission, otherwise default to submission author.
     *
     * @param Submission $submission
     *
     * @return int
     */
    private function getStudentId(Submission $submission) {
        if (!$this->request->input('user_id')) {
            return $submission->user_id;
        }

        $studentId = intval($this->request->input('user_id'));
        if ($submission->users->pluck('id')->contains($studentId)) {
            return $studentId;
        }

        return $submission->user_id;
    }
}
