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

    public function save(Request $request): array
    {
        $submissionFileId = $request->input('submission_file_id');
        $reviewComment = $request->input('review_comment');

        return $this->codeReviewCommentService->save($submissionFileId, $reviewComment);
    }

    public function delete(Request $request): array
    {
        $reviewCommentId = $request->route('codeReviewComment');

        return $this->codeReviewCommentService->delete($reviewCommentId);
    }
}


