<?php

namespace TTU\Charon\Services;

use TTU\Charon\Dto\FileReviewCommentsDTO;
use TTU\Charon\Dto\ReviewCommentDTO;
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
        $fileReviewCommentsDTOs = [];
        $rawResults = $this->reviewCommentRepository->getReviewCommentsForCharonAndStudent($charonId, $studentId);
        $fileId = null;
        foreach ($rawResults as $rawResult) {

            if ($rawResult->file_id !== $fileId) {
                $fileId = $rawResult->file_id;
                $fileReviewCommentsDTO = new FileReviewCommentsDTO(
                    $rawResult->file_id,
                    $rawResult->charon_id,
                    $rawResult->submission_id,
                    $rawResult->created_at,
                    $rawResult->student_id,
                    $rawResult->path,
                    $this->createReviewComment($rawResult)
                );
                array_unshift($fileReviewCommentsDTOs, $fileReviewCommentsDTO);
            } else {
                array_unshift(
                    $fileReviewCommentsDTOs[0]->reviewComments,
                    $this->createReviewComment($rawResult)
                );
            }
        }
        return $fileReviewCommentsDTOs;
    }

    /**
     * @param $rawResult
     * @return ReviewCommentDTO
     */
    public function createReviewComment($rawResult): ReviewCommentDTO
    {
        return new ReviewCommentDTO(
            $rawResult->review_comment_id,
            $rawResult->commented_by_id,
            $rawResult->commented_by_firstname,
            $rawResult->commented_by_lastname,
            $rawResult->code_row_no_start,
            $rawResult->code_row_no_end,
            $rawResult->review_comment,
            $rawResult->notify,
            $rawResult->comment_creation
        );
    }
}
