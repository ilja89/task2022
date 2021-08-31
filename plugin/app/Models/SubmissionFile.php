<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class SubmissionFile.
 *
 * @property integer id
 * @property integer submission_id
 * @property integer is_test
 * @property string path
 * @property string contents
 * @property Submission submission
 *
 * @property CharonCodeReviewComment[]|Collection comments
 *
 * @package TTU\Charon\Models
 */
class SubmissionFile extends Model
{
    public $timestamps = false;
    protected $table = 'charon_submission_file';
    protected $fillable = [
        'submission_id', 'path', 'contents', 'is_test'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(CharonCodeReviewComment::class)->orderBy('id');
    }
}
