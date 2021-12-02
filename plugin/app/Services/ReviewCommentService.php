<?php

namespace TTU\Charon\Services;

use TTU\Charon\Exceptions\ReviewCommentException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\ReviewCommentRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
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

    /** @var NotificationService */
    private $notificationService;

    /** @var SubmissionsRepository */
    private $submissionRepository;

    /**
     * ReviewCommentService constructor.
     *
     * @param ReviewCommentRepository $reviewCommentRepository
     * @param NotificationService $notificationService
     * @param SubmissionsRepository $submissionRespository
     */
    public function __construct(
        ReviewCommentRepository $reviewCommentRepository,
        NotificationService $notificationService,
        SubmissionsRepository $submissionRespository
    ) {
        $this->reviewCommentRepository = $reviewCommentRepository;
        $this->notificationService = $notificationService;
        $this->submissionRepository = $submissionRespository;
    }

    /**
     * Get logged-in user's identifier and save their review comment.
     *
     * @param int $submissionFileId
     * @param string $reviewComment
     * @param bool $notify
     * @param Charon $charon
     * @throws ReviewCommentException
     */
    public function add(
        int $submissionFileId,
        string $reviewComment,
        bool $notify,
        Charon $charon
    ) {
        if (strlen($reviewComment) > 10000) {
            throw new ReviewCommentException("review_comment_over_limit");
        }
        $userId = app(User::class)->currentUserId();
        $comment = $this->reviewCommentRepository->add($userId, $submissionFileId, $reviewComment, $notify);
        if ($comment && $notify) {
            $submissionFile = $this->submissionRepository->getSubmissionFileById($submissionFileId);
            if ($submissionFile) {
                $this->notificationService->sendNotificationToStudent(
                    $submissionFile->submission_id,
                    $reviewComment,
                    $charon,
                    $submissionFile->path);
            }
        }
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
            throw new ReviewCommentException("delete_review_comment_not_found");
        }
        $this->reviewCommentRepository->delete($reviewCommentId);
    }

    /**
     * Remove notification setting from review comments got by given identifiers.
     *
     * @param $reviewCommentIds
     */
    public function clearNotifications($reviewCommentIds): void
    {
        $this->reviewCommentRepository->clearNotification($reviewCommentIds);
    }
}
