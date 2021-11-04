<?php

namespace TTU\Charon\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        Log::info("New comment was added: ", [
            "charonId" => $charon->id,
            "submissionFileId" => $request->input('submission_file_id'),
            "comment" => $request->input('review_comment'),
            "notify" => $request->input('notify'),
            "submissionId" => $request->input('submission_id'),
            "commentedFilePath" => $request->input('file_path')]);
        $submissionFileId = $request->input('submission_file_id');
        $reviewComment = $request->input('review_comment');
        $notify = $request->input('notify');
        $submissionId = $request->input('submission_id');
        $filePath = $request->input('file_path');
        $this->reviewCommentService->add($submissionFileId, $reviewComment, $notify, $charon, $submissionId, $filePath);
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
}
