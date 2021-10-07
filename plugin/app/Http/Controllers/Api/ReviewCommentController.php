<?php

namespace TTU\Charon\Http\Controllers\Api;
use Illuminate\Http\Request;
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

    public function add(Request $request): array
    {
        $submissionFileId = $request->input('submission_file_id');
        $reviewComment = $request->input('review_comment');

        return $this->reviewCommentService->add($submissionFileId, $reviewComment);
    }

    public function delete(Request $request): array
    {
        $reviewCommentId = $request->route('reviewComment');

        return $this->reviewCommentService->delete($reviewCommentId);
    }
}

