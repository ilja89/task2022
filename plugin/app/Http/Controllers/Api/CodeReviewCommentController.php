<?php

namespace TTU\Charon\Http\Controllers\Api;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Services\CodeReviewCommentService;

/**
 * Class CodeReviewCommentController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */

class CodeReviewCommentController extends Controller
{
    /** @var CodeReviewCommentService */
    private $codeReviewCommentService;

    /**
     * CodeReviewCommentsController constructor.
     *
     * @param Request $request
     * @param CodeReviewCommentService $codeReviewCommentService
     */
    public function __construct(
        Request $request,
        CodeReviewCommentService $codeReviewCommentService
    ) {
        parent::__construct($request);
        $this->codeReviewCommentService = $codeReviewCommentService;
    }

    public function saveComment(Request $request): array
    {
        $submissionFileId = $request->input('submission_file_id');
        $comment = $request->input('comment');
        $notify = $request->input('notify');

        return $this->codeReviewCommentService->saveComment($submissionFileId, $comment, $notify);
    }

    public function deleteComment(Request $request): array
    {
        $commentId = $request->route('codeReviewComment');

        return $this->codeReviewCommentService->deleteComment($commentId);
    }

    public function clearNotifications(Request $request): array
    {
        $reviewCommentIds = $request->input('reviewCommentIds');
        return $this->codeReviewCommentService->clearNotifications($reviewCommentIds);
    }
}


