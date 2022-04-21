<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use TTU\Charon\Dto\FileReviewCommentsDTO;
use TTU\Charon\Dto\ReviewCommentDTO;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\ReviewComment;

/**
 * Class ReviewCommentRepository.
 *
 * @package TTU\Charon\Repositories
 */
class ReviewCommentRepository
{
    /**
     * Save a submission file comment.
     *
     * @param $userId
     * @param $submissionFileId
     * @param $reviewComment
     * @param $notify
     * @return bool
     */
    public function add($userId, $submissionFileId, $reviewComment, $notify): bool
    {
        return DB::table('charon_review_comment')->insert([
            'user_id' => $userId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'review_comment' => $reviewComment,
            'created_at' => Carbon::now(),
            'notify' => $notify,
        ]);
    }

    /**
     * Find a comment by id.
     *
     * @param $reviewCommentId
     * @return ReviewComment|null
     */
    public function get($reviewCommentId): ?ReviewComment
    {
        return ReviewComment::where('id', $reviewCommentId)
            ->first();
    }

    /**
     * Remove a review comment by id.
     *
     * @param $reviewCommentId
     * @return boolean
     */
    public function delete($reviewCommentId): bool
    {
        return DB::table('charon_review_comment')
            ->where('id', $reviewCommentId)
            ->delete();
    }

    /**
     * Remove notification setting from a given review comment.
     *
     * @param $reviewCommentIds
     * @return bool
     */
    public function clearNotification($reviewCommentIds): bool
    {
        return DB::table('charon_review_comment')
            ->whereIn('id', $reviewCommentIds)->update(['notify' => 0]);
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
        $rawResults = DB::table('charon_submission')
            ->where('charon_submission.charon_id', $charonId)
            ->join('charon_submission_file','charon_submission.id', '=', 'charon_submission_file.submission_id' )
            ->join('charon_review_comment', 'charon_submission_file.id', '=', 'charon_review_comment.submission_file_id')
            ->join('charon_submission_user', 'charon_submission_user.submission_id', '=', 'charon_submission.id')
            ->join('user as student', 'charon_submission_user.user_id', '=', 'student.id')
            ->where('student.id', '=', $studentId)
            ->join('user', 'charon_review_comment.user_id', '=', 'user.id')
            ->select(
                'charon_submission.user_id as student_id',
                'charon_submission.charon_id as charon_id',
                'charon_submission.created_at',
                'charon_submission_file.submission_id',
                'charon_submission_file.id as file_id',
                'charon_submission_file.path',
                'charon_review_comment.id as review_comment_id',
                'charon_review_comment.created_at as comment_creation',
                'charon_review_comment.notify',
                'charon_review_comment.review_comment',
                'charon_review_comment.code_row_no_start',
                'charon_review_comment.code_row_no_end',
                'charon_review_comment.user_id as commented_by_id',
                'user.firstname as commented_by_firstname',
                'user.lastname as commented_by_lastname')
            ->get()
            ->all();
        return $this->convertToDTOs($rawResults);
    }

    /**
     * Get all reviewComments for the specific charon and for the specific student.
     *
     * @param $rawResults
     * @return array
     */
    private function convertToDTOs($rawResults): array
    {
        $fileReviewCommentsDTOs = [];
        foreach ($rawResults as $rawResult) {

            if (!array_key_exists($rawResult->file_id, $fileReviewCommentsDTOs)) {
                $fileReviewCommentsDTO = new FileReviewCommentsDTO(
                    $rawResult->file_id,
                    $rawResult->charon_id,
                    $rawResult->submission_id,
                    $rawResult->created_at,
                    $rawResult->student_id,
                    $rawResult->path
                );

                $fileReviewCommentsDTOs[$rawResult->file_id] = $fileReviewCommentsDTO;
            }

            $reviewCommentDTO = new ReviewCommentDTO(
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
            array_unshift($fileReviewCommentsDTOs[$rawResult->file_id]->reviewComments, $reviewCommentDTO);
        }
        return array_values($fileReviewCommentsDTOs);
    }
}
