<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Charon review comment model class.
 *
 * @property integer id
 * @property integer user_id
 * @property integer submission_file_id
 * @property integer|null code_row_no_start
 * @property integer|null code_row_no_end
 * @property string review_comment
 * @property integer notify
 * @property Carbon created_at
 *
 * @package TTU\Charon\Model
 */
class ReviewComment extends Model
{
    public $timestamps = false;

    protected $table = 'charon_review_comment';
    protected $fillable = [
        'user_id', 'submission_file_id',
        'code_row_no_start', 'code_row_no_end', 'review_comment', 'notify', 'created_at'
    ];
}
