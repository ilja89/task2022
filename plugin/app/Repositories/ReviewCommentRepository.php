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
     */
    public function save($userId, $submissionFileId, $reviewComment, $notify)
    {
        DB::table('charon_review_comment')->insert([
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
     * Find multiple comments with given array of identifiers.
     *
     * @param $reviewCommentIds
     * @return ReviewComment[]|Collection
     */
    public function getMany($reviewCommentIds): array
    {
        return ReviewComment::whereIn('id', $reviewCommentIds)
            ->get()
            ->all();
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
     * Clear notification of a comment with given identifier/change its 'notify' field to 0.
     *
     * @param ReviewComment $reviewComment
     * @return bool
     */
    public function clearNotification(ReviewComment $reviewComment): bool
    {
        $reviewComment->notify = 0;
        return $reviewComment->update();
    }
}
