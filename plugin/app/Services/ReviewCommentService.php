<?php

namespace TTU\Charon\Services;

use TTU\Charon\Dto\FileReviewCommentsDTO;
use TTU\Charon\Dto\ReviewCommentDTO;
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
    private $submissionsRepository;

    /**
     * ReviewCommentService constructor.
     *
     * @param ReviewCommentRepository $reviewCommentRepository
     * @param NotificationService $notificationService
     * @param SubmissionsRepository $submissionsRespository
     */
    public function __construct(
        ReviewCommentRepository $reviewCommentRepository,
        NotificationService $notificationService,
        SubmissionsRepository $submissionsRespository
    ) {
        $this->reviewCommentRepository = $reviewCommentRepository;
        $this->notificationService = $notificationService;
        $this->submissionsRepository = $submissionsRespository;
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
        $user = app(User::class)->currentUser();
        $comment = $this->reviewCommentRepository->add($user->id, $submissionFileId, $reviewComment, $notify);
        if ($comment && $notify) {
            $submissionFile = $this->submissionsRepository->getSubmissionFileById($submissionFileId);
            if ($submissionFile) {
                $submission = $this->submissionsRepository->find($submissionFile->submission_id);

                $students = $this->submissionsRepository->findAllUsersAssociated($submission->id);

                $cm_id = $charon->courseModule()->id;
                $url = '/mod/charon/view.php?id=' . $cm_id . '#/submission-page/' . $submission->id;

                $messageText = htmlspecialchars($reviewComment);
                $messageText = str_replace( "\n", '<br />', $messageText );

                $messageTextHtml = <<<EOT
<h4>$charon->name</h4><br>
<b>You've got a new comment for the submission that was submitted at 
$submission->created_at</b><br>
<b>Author: $user->firstname $user->lastname</b><br>
<b>File that was commented: $submissionFile->path</b><br><br>
<p style="white-space: pre-wrap">$messageText</p>
EOT;

                foreach ($students as $student) {
                    $this->notificationService->sendNotification(
                        $user,
                        $student,
                        'comment',
                        "New comment: " . $charon->name,
                        $messageText,
                        $messageTextHtml,
                        $url,
                        'Submission('. $submission->created_at . ')'
                    );
                }
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
     * @throws ReviewCommentException
     */
    public function clearNotifications($reviewCommentIds): void
    {
        if (!$this->reviewCommentRepository->clearNotification($reviewCommentIds)) {
            throw new ReviewCommentException("notification_removal_failed");
        }
    }

    /**
     * Get all reviewComments for the specific charon and for the specific student.
     *
     * @param $charonId
     * @param $studentId
     * @return array
     */
    public function getReviewCommentsForCharonAndStudent($charonId, $studentId): array
    {
        return $this->reviewCommentRepository->getReviewCommentsForCharonAndStudent($charonId, $studentId);
    }
}
