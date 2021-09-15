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
    public function save($teacherId, $submissionFileId, $reviewComment)
    {
        DB::table('charon_code_review_comment')->insert([
            'teacher_id' => $teacherId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'comment' => $reviewComment,
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
        return DB::table('charon_code_review_comment')
            ->where('id', $reviewCommentId)
            ->delete();
    }
}