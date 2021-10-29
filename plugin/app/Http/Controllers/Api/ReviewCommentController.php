<?php

namespace TTU\Charon\Http\Controllers\Api;
use Illuminate\Http\Request;
use TTU\Charon\Exceptions\ReviewCommentException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Services\ReviewCommentService;

/**
 * Class ReviewCommentController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */

class ReviewCommentController extends Controller
{
    /** @var ReviewCommentService */
    private $reviewCommentService;

    /**
     * ReviewCommentController constructor.
     *
     * @param Request $request
     * @param ReviewCommentService $reviewCommentService
     */
    public function __construct(
        Request $request,
        ReviewCommentService $reviewCommentService
    ) {
        parent::__construct($request);
        $this->reviewCommentService = $reviewCommentService;
    }

    /**
     * @throws ReviewCommentException
     */
    public function add(Request $request): void
    {
        $submissionFileId = $request->input('submission_file_id');
        $reviewComment = $request->input('review_comment');
        $notify = $request->input('notify');
        $this->reviewCommentService->add($submissionFileId, $reviewComment, $notify);
    }

    /**
     * @throws ReviewCommentException
     */
    public function delete(Request $request): void
    {
        $reviewCommentId = $request->route('reviewComment');
        $this->reviewCommentService->delete($reviewCommentId);
    }

    public function clearNotifications(Request $request): void
    {
        $reviewCommentIds = $request->input('reviewCommentIds');
        $this->reviewCommentService->clearNotifications($reviewCommentIds);
    }

    /**
     * Get all reviewComments for the specific charon and from the specific student.
     * @param Request $request
     * @return array
     */
    public function getReviewCommentsForCharon(Request $request): array
    {
        $charonId = $request->route('charon');
        $studentId = $request->route('student');
        return $this->reviewCommentService->getReviewCommentsForCharon($charonId, $studentId);
    }
}
