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
    public function saveComment($teacherId, $submissionFileId, $comment)
    {
        /*$comment = new CharonCodeReviewComment([
            'teacher_id' => $teacherId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'comment' => $comment,
            'created_at' => Carbon::now(),
        ]);*/

        DB::table('charon_code_review_comment')->insert([
            'teacher_id' => $teacherId,
            'submission_file_id' => $submissionFileId,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'comment' => $comment,
            'created_at' => Carbon::now(),
        ]);
    }
}