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
     */
    public function save($teacherId, $submissionFileId, $comment)
    {
        DB::table('charon_code_review_comment')->insert([
            'teacher_id' => $teacherId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'comment' => $comment,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Find a comment by id.
     *
     * @param $commentId
     * @return CharonCodeReviewComment
     */
    public function get($commentId): CharonCodeReviewComment
    {
        $res = DB::table('charon_code_review_comment')
            ->where('id', $commentId)
            ->get();
        echo $res;
        $commentData = json_decode($res, true);
        return new CharonCodeReviewComment($commentData);
    }

    /**
     * Remove a comment by id.
     *
     * @param $commentId
     * @return boolean
     */
    public function delete($commentId): bool
    {
        //$comment = CharonCodeReviewComment::find($commentId);
        $comment = $this->get($commentId);
        return $comment->delete();
    }
}