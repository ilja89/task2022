<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\CodeReviewCommentRepository;
use Zeizig\Moodle\Globals\User;

/**
 * Class CodeReviewCommentService
 *
 * @package TTU\Charon\Services
 */
class CodeReviewCommentService
{
    /** @var CodeReviewCommentRepository */
    private $codeReviewCommentRepository;

    /**
     * CodeReviewCommentService constructor.
     *
     * @param CodeReviewCommentRepository $codeReviewCommentRepository
     */
    public function __construct(CodeReviewCommentRepository $codeReviewCommentRepository)
    {
        $this->codeReviewCommentRepository = $codeReviewCommentRepository;
    }

    /**
     * Get logged-in user's identifier and save their comment.
     *
     * @param $submissionFileId
     * @param $comment
     * @return string[]
     */
    public function saveComment($submissionFileId, $comment): array
    {
        $teacherId = (new User)->currentUserId();
        $this->codeReviewCommentRepository->save($teacherId, $submissionFileId, $comment);
        return [
            'status'  => 'OK'
        ];
    }

    /**
     * Delete comment.
     *
     * @param $commentId
     * @return array
     */
    public function deleteComment($commentId): array
    {
        $id = $this->codeReviewCommentRepository->delete($commentId);
        if ($id) {
            return [
                'status' => 'OK'
            ];
        } else {

            return [
                'status'=>'Failed',
            ];

        }
    }
}