<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
}
