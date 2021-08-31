<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Charon code review comment model class.
 *
 * @property integer id
 * @property integer teacher_id
 * @property integer charon_submission_file_id
 * @property integer|null code_row_no_start
 * @property integer|null code_row_no_end
 * @property string comment
 * @property Carbon timestamp
 *
 * @package TTU\Charon\Model
 */
class CharonCodeReviewComment extends Model
{
    public $timestamps = false;

    protected $table = 'charon_code_review_comment';
    protected $fillable = [
        'teacher_id', 'charon_submission_file_id',
        'code_row_no_start', 'code_row_no_end', 'comment'
    ];
}
