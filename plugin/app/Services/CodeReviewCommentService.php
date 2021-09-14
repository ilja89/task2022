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
     * Get logged-in user's identifier and save their review comment.
     *
     * @param $submissionFileId
     * @param $reviewComment
     * @return string[]
     */
    public function save($submissionFileId, $reviewComment): array
    {
        $teacherId = (new User)->currentUserId();
        $this->codeReviewCommentRepository->save($teacherId, $submissionFileId, $reviewComment);
        return [
            'status'  => 'OK'
        ];
    }

    /**
     * Delete review comment.
     *
     * @param $reviewCommentId
     * @return array
     */
    public function delete($reviewCommentId): array
    {
        $comment = $this->codeReviewCommentRepository->get($reviewCommentId);
        if ($comment) {
            $result = $this->codeReviewCommentRepository->delete($reviewCommentId);
            if ($result) {
                return [
                    'status' => 'OK'
                ];
            } else {
                return [
                    'status'=>'Failed',
                ];
            }
        }
        return [
            'status'=>'Failed',
        ];
    }
}