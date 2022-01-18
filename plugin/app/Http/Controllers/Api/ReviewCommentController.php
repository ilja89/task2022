<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Exceptions\ReviewCommentException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
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
    public function add(Request $request, Charon $charon): void
    {
        $submissionFileId = $request->input('submission_file_id');
        $reviewComment = $request->input('review_comment');
        $notify = $request->input('notify');
        $this->reviewCommentService->add($submissionFileId, $reviewComment, $notify, $charon);
    }

    /**
     * @throws ReviewCommentException
     */
    public function delete(Request $request): void
    {
        $reviewCommentId = $request->route('reviewComment');
        $this->reviewCommentService->delete($reviewCommentId);
    }

    /**
     * @throws ReviewCommentException
     */
    public function clearNotifications(Request $request): void
    {
        $reviewCommentIds = $request->input('reviewCommentIds');
        $this->reviewCommentService->clearNotifications($reviewCommentIds);
    }

    /**
     * Get all reviewComments for the specific charon and for the specific student.
     * @param Request $request
     * @return array
     */
    public function getReviewCommentsForCharonAndStudent(Request $request): array
    {
        $charonId = $request->route('charon');
        $studentId = $request->input('user_id');
        return $this->reviewCommentService->getReviewCommentsForCharonAndStudent($charonId, $studentId);
    }
}
