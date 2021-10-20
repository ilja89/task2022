<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\ReviewCommentRepository;
use Zeizig\Moodle\Globals\User;

/**
 * Class ReviewCommentService
 *
 * @package TTU\Charon\Services
 */
class ReviewCommentService
{
    /** @var ReviewCommentRepository */
    private $reviewCommentRepository;

    /**
     * ReviewCommentService constructor.
     *
     * @param ReviewCommentRepository $reviewCommentRepository
     */
    public function __construct(ReviewCommentRepository $reviewCommentRepository)
    {
        $this->reviewCommentRepository = $reviewCommentRepository;
    }

    /**
     * Get logged-in user's identifier and save their review comment.
     *
     * @param $submissionFileId
     * @param $reviewComment
     * @return string[]
     */
    public function add($submissionFileId, $reviewComment): array
    {
        $userId = app(User::class)->currentUserId();
        $this->reviewCommentRepository->add($userId, $submissionFileId, $reviewComment);
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
        $comment = $this->reviewCommentRepository->get($reviewCommentId);
        if ($comment) {
            $result = $this->reviewCommentRepository->delete($reviewCommentId);
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