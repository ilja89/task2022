<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\CharonCodeReviewComment;

/**
 * Class CodeReviewCommentsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class CodeReviewCommentRepository
{
    /**
     * Save a submission file comment.
     *
     * @param $teacherId
     * @param $submissionFileId
     * @param $comment
     * @param $notify
     */
    public function save($teacherId, $submissionFileId, $comment, $notify)
    {
        DB::table('charon_code_review_comment')->insert([
            'teacher_id' => $teacherId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'comment' => $comment,
            'created_at' => Carbon::now(),
            'notify' => $notify
        ]);
    }

    /**
     * Find a comment by id.
     *
     * @param $commentId
     * @return CharonCodeReviewComment|null
     */
    public function get($commentId): ?CharonCodeReviewComment
    {
        return CharonCodeReviewComment::where('id', $commentId)
            ->first();
    }

    /**
     * Remove a comment by id.
     *
     * @param $commentId
     * @return boolean
     */
    public function delete($commentId): bool
    {
        return DB::table('charon_code_review_comment')
            ->where('id', $commentId)
            ->delete();
    }
}