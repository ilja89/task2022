<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Comment;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;

/**
 * Class PopupController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PopupController extends Controller
{
    /** @var CharonRepository */
    protected $charonRepository;

    /** @var Request */
    private $request;

    /** @var GrademapService */
    private $grademapService;

    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var User */
    private $user;

    /**
     * PopupController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     * @param GrademapService $grademapService
     * @param CharonGradingService $charonGradingService
     * @param User $user
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        GrademapService $grademapService,
        CharonGradingService $charonGradingService,
        User $user
    ) {
        $this->charonRepository     = $charonRepository;
        $this->request              = $request;
        $this->grademapService      = $grademapService;
        $this->charonGradingService = $charonGradingService;
        $this->user                 = $user;
    }

    /**
     * Get Charons by course.
     *
     * @param  Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|Charon[]
     */
    public function getCharonsByCourse(Course $course)
    {
        $charons = $this->charonRepository->findCharonsByCourse($course->id);

        foreach ($charons as $charon) {
            $charon->grademaps = Grademap::with([
                'gradeItem' => function ($query) {
                    $query->select(['id', 'grademax']);
                },
            ])->where('charon_id', $charon->id)
                                         ->get(['id', 'charon_id', 'grade_item_id', 'grade_type_code', 'name']);
            $charon->deadlines = Deadline::where('charon_id', $charon->id)->get();
        }

        return $charons;
    }

    /**
     * @param Charon $charon
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getSubmissionsByCharon(Charon $charon)
    {
        $userId = $this->request['user_id'];

        $submissions = Submission::with([
            // Only select results which have a corresponding grademap
            'results' => function ($query) use ($charon) {
                $query->whereIn('grade_type_code', $charon->getGradeTypes());
            },
        ])
                                 ->where('charon_id', $charon->id)
                                 ->where('user_id', $userId)
                                 ->orderBy('git_timestamp', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->select([
                                     'id', 'charon_id', 'confirmed', 'created_at', 'git_hash', 'git_timestamp',
                                     'user_id',
                                 ])
                                 ->simplePaginate(10);

        $submissions->appends(['user_id' => $userId])->links();

        return $submissions;
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

        foreach ($newResults as $result) {
            $existingResult = $this->getResultByIdFromArray($submission->results, $result['id']);

            $existingResult->calculated_result = $result['calculated_result'];
            $existingResult->save();
        }

        $this->charonGradingService->updateGradeIfApplicable($submission, true);
        $this->charonGradingService->confirmSubmission($submission);

        return [
            'status' => 'OK',
        ];
    }

    /**
     * Saves a comment. Comment details are taken from the request.
     *
     * @param  Charon $charon
     *
     * @return array
     */
    public function saveComment(Charon $charon)
    {
        $comment = Comment::create([
            'charon_id'  => $charon->id,
            'student_id' => $this->request['student_id'],
            'teacher_id' => $this->user->currentUserId(),
            'message'    => $this->request['comment'],
            'created_at' => Carbon::now(),
        ]);

        $comment->teacher;

        return [
            'status'  => 'OK',
            'comment' => $comment,
        ];
    }

    /**
     * Get comments by Charon and student.
     *
     * @param  Charon $charon
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getComments(Charon $charon)
    {
        $studentId = $this->request['student_id'];
        $comments  = Comment::with('teacher')
                            ->where('student_id', $studentId)
                            ->where('charon_id', $charon->id)
                            ->get();

        return $comments;
    }

    /**
     * @param  Result[] $results
     * @param  integer $id
     *
     * @return null|Result
     */
    private function getResultByIdFromArray($results, $id)
    {
        foreach ($results as $result) {
            if ($result->id == $id) {
                return $result;
            }
        }

        return null;
    }
}
