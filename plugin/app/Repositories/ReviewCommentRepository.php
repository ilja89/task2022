<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\CharonReviewComment;

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
     * @param $teacherId
     * @param $submissionFileId
     * @param $reviewComment
     */
    public function save($userId, $submissionFileId, $reviewComment)
    {
        DB::table('charon_review_comment')->insert([
            'user_id' => $userId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'review_comment' => $reviewComment,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Find a comment by id.
     *
     * @param $reviewCommentId
     * @return CharonReviewComment|null
     */
    public function get($reviewCommentId): ?CharonReviewComment
    {
        return CharonReviewComment::where('id', $reviewCommentId)
            ->first();
    }

    /**
     * Remove a comment by id.
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
}