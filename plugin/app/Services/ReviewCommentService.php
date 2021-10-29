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
     */
    public function clearNotifications($reviewCommentIds): void
    {
        $this->reviewCommentRepository->clearNotification($reviewCommentIds);
    }

    /**
     * Get all reviewComments for the specific charon and from the specific student.
     *
     * @param $charonId
     * @param $studentId
     * @return array
     */
    public function getReviewCommentsForCharon($charonId, $studentId): array
    {
        $fileReviewCommentsDTOs = [];
        $rawResults = $this->reviewCommentRepository->getReviewCommentsForCharon($charonId, $studentId);
        $fileId = null;
        $fileReviewCommentsDTO = null;
        foreach($rawResults as $rawResult)
        {
            if($rawResult->file_id === $fileId)
            {
                $reviewComment = new ReviewCommentDTO();
                $reviewComment->commentedById = $rawResult->commented_by_id;
                $reviewComment->commentedByFirstName = $rawResult->commented_by_firstname;
                $reviewComment->commentedByLastName = $rawResult->commented_by_lastname;
                $reviewComment->id = $rawResult->commented_by_id;
                $reviewComment->commentedById = $rawResult->commented_by_id;
                $reviewComment->commentedById = $rawResult->commented_by_id;
                array_push($fileReviewCommentsDTO->reviewComments, $reviewComment);
            }
            else {
                array_push($fileReviewCommentsDTOs, $fileReviewCommentsDTO);
                $fileReviewCommentsDTO = new FileReviewCommentsDTO();
                $fileReviewCommentsDTO->fileId = $rawResult->file_id;
                $fileReviewCommentsDTO->submissionId = $rawResult->submission_id;
                $fileReviewCommentsDTO->studentId = $rawResult->student_id;
                $fileReviewCommentsDTO->path = $rawResult->path;
            }
        }
        return $fileReviewCommentsDTOs;
    }
}
