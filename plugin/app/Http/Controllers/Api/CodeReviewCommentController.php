<?php

namespace TTU\Charon\Http\Controllers\Api;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\CodeReviewCommentRepository;
use Zeizig\Moodle\Globals\User;


/**
 * Class CodeReviewCommentController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */

class CodeReviewCommentController extends Controller
{
    /** @var CodeReviewCommentRepository */
    private $codeReviewCommentsRepository;

    /**
     * CommentsController constructor.
     *
     * @param Request $request
     * @param CodeReviewCommentRepository $codeReviewCommentRepository
     */
    public function __construct(Request $request,
                                CodeReviewCommentRepository $codeReviewCommentRepository )
    {
        parent::__construct($request);
        $this->codeReviewCommentsRepository = $codeReviewCommentRepository;
    }

    public function saveComment($request): array
    {
        $teacherId = (new User)->currentUserId();
        $submissionFileId = $request->input('submission_file_id');
        $comment = $request->input('comment');

        $this->codeReviewCommentsRepository->saveComment($teacherId, $submissionFileId, $comment);

        return [
            'status'  => 'OK'
        ];
    }

}


