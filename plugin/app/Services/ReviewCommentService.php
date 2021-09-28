<?php

namespace TTU\Charon\Services;

use TTU\Charon\Exceptions\ReviewCommentException;
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
     * @param $notify
     * @throws ReviewCommentException
     */
    public function add($submissionFileId, $reviewComment, $notify): void
    {
        if (strlen($reviewComment) > 10000) {
            throw new ReviewCommentException("review_comment_over_limit");
        }
        $userId = app(User::class)->currentUserId();
        $this->reviewCommentRepository->add($userId, $submissionFileId, $reviewComment, $notify);
    }

    /**
     * Delete review comment.
     *
     * @param $reviewCommentId
     * @throws ReviewCommentException
     */
    public function delete($reviewCommentId): void
    {
        $comment = $this->reviewCommentRepository->get($reviewCommentId);
        if (!$comment) {
            throw new ReviewCommentException("review_comment_not_found");
        }
        $this->reviewCommentRepository->delete($reviewCommentId);
    }

    /**
     * Clear notifications of comments got from given identifiers.
     *
     * @param $reviewCommentIds
     */
    public function clearNotifications($reviewCommentIds): void
    {
        $reviewComments = $this->reviewCommentRepository->getMany($reviewCommentIds);
        foreach ($reviewComments as $reviewComment) {
            $this->reviewCommentRepository->clearNotification($reviewComment);
        }
    }
}
